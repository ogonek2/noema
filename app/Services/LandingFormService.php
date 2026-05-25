<?php

namespace App\Services;

use App\Enums\LandingFormFieldType;
use App\Enums\LandingSectionType;
use App\Models\FormSettings;
use App\Models\FormSubmission;
use App\Models\LandingPage;
use App\Models\LandingPageSection;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class LandingFormService
{
    public function __construct(
        private readonly TelegramFormNotifier $telegram,
    ) {}

    /** @return array<string, mixed> */
    public function normalizeFormContent(array $content): array
    {
        $content['form_key'] = filled($content['form_key'] ?? null)
            ? (string) $content['form_key']
            : (string) Str::uuid();

        $content['fields'] = collect($content['fields'] ?? [])
            ->map(function (mixed $field, int $index): array {
                if (! is_array($field)) {
                    return [];
                }

                $key = Str::slug((string) ($field['key'] ?? $field['label'] ?? 'field-'.$index), '_');

                return [
                    'key' => $key !== '' ? $key : 'field_'.$index,
                    'label' => (string) ($field['label'] ?? ''),
                    'type' => LandingFormFieldType::tryFrom((string) ($field['type'] ?? ''))?->value
                        ?? LandingFormFieldType::Text->value,
                    'required' => (bool) ($field['required'] ?? false),
                    'placeholder' => (string) ($field['placeholder'] ?? ''),
                    'help_text' => (string) ($field['help_text'] ?? ''),
                    'mask' => (string) ($field['mask'] ?? ''),
                    'width' => in_array($field['width'] ?? 'full', ['full', 'half'], true) ? $field['width'] : 'full',
                    'options' => collect($field['options'] ?? [])
                        ->map(fn (mixed $opt): string => is_array($opt) ? (string) ($opt['value'] ?? $opt['label'] ?? '') : (string) $opt)
                        ->filter()
                        ->values()
                        ->all(),
                    'sort_order' => (int) ($field['sort_order'] ?? $index),
                ];
            })
            ->filter(fn (array $field): bool => filled($field['label']) && filled($field['key']))
            ->sortBy('sort_order')
            ->values()
            ->all();

        return $content;
    }

    /** @return array{section: ?LandingPageSection, page: ?LandingPage, schema: array<string, mixed>}|null */
    public function findFormByKey(string $formKey): ?array
    {
        $landing = $this->findLandingFormByKey($formKey);

        if ($landing !== null) {
            return $landing;
        }

        return $this->findGlobalFormByKey($formKey);
    }

    /** @return array{section: LandingPageSection, page: LandingPage, schema: array<string, mixed>}|null */
    private function findLandingFormByKey(string $formKey): ?array
    {
        $section = LandingPageSection::query()
            ->where('type', LandingSectionType::Form)
            ->where('is_active', true)
            ->get()
            ->first(function (LandingPageSection $section) use ($formKey): bool {
                $content = is_array($section->content) ? $section->content : [];

                return ($content['form_key'] ?? null) === $formKey;
            });

        if (! $section) {
            return null;
        }

        $page = $section->landingPage;

        if (! $page || ! $page->is_published) {
            return null;
        }

        $schema = $this->normalizeFormContent($section->content ?? []);

        return [
            'section' => $section,
            'page' => $page,
            'schema' => $schema,
        ];
    }

    /** @return array{section: null, page: null, schema: array<string, mixed>}|null */
    private function findGlobalFormByKey(string $formKey): ?array
    {
        $settings = FormSettings::current();

        if (! $settings->consultation_enabled) {
            return null;
        }

        $key = $settings->consultation_form_key ?: 'consultation';

        if ($formKey !== $key) {
            return null;
        }

        return [
            'section' => null,
            'page' => null,
            'schema' => $settings->consultationSchema(),
        ];
    }

    /**
     * @param  array<string, mixed>  $schema
     * @param  array<string, mixed>  $input
     * @return array{validated: array<string, mixed>, labels: array<string, string>}
     */
    public function validateSubmission(array $schema, array $input): array
    {
        $rules = [];
        $attributes = [];
        $labels = [];

        foreach ($schema['fields'] as $field) {
            $key = $field['key'];
            $labels[$key] = $field['label'];
            $attributes[$key] = $field['label'];

            $fieldRules = [];

            if ($field['required']) {
                $fieldRules[] = 'required';
            } else {
                $fieldRules[] = 'nullable';
            }

            $type = LandingFormFieldType::tryFrom($field['type']) ?? LandingFormFieldType::Text;

            $fieldRules = array_merge($fieldRules, match ($type) {
                LandingFormFieldType::Email => ['email', 'max:255'],
                LandingFormFieldType::Tel => ['string', 'max:32'],
                LandingFormFieldType::Textarea => ['string', 'max:5000'],
                LandingFormFieldType::Number => ['numeric'],
                LandingFormFieldType::Select => ['string', Rule::in($field['options'])],
                LandingFormFieldType::Checkbox => $field['required']
                    ? ['accepted']
                    : ['nullable', 'boolean'],
                LandingFormFieldType::Date => ['date'],
                LandingFormFieldType::Url => ['url', 'max:500'],
                default => ['string', 'max:500'],
            });

            $rules[$key] = $fieldRules;
        }

        $validator = Validator::make($input, $rules, [], $attributes);

        return [
            'validated' => $validator->validate(),
            'labels' => $labels,
        ];
    }

    /**
     * @param  array<string, mixed>  $schema
     * @param  array<string, mixed>  $validated
     */
    public function storeSubmission(
        array $schema,
        array $validated,
        ?LandingPage $page,
        ?LandingPageSection $section,
        ?string $ip,
        ?string $userAgent,
        ?string $referer,
    ): FormSubmission {
        $submission = FormSubmission::query()->create([
            'form_key' => $schema['form_key'],
            'landing_page_id' => $page?->id,
            'landing_page_section_id' => $section?->id,
            'landing_page_slug' => $page?->slug,
            'form_title' => $schema['title'] ?? $page?->title ?? 'Консультація',
            'payload' => $validated,
            'ip_address' => $ip,
            'user_agent' => $userAgent,
            'referer' => $referer,
        ]);

        if ($this->telegram->notify($submission, $schema, $validated)) {
            $submission->forceFill(['telegram_sent' => true])->save();
        }

        return $submission;
    }
}
