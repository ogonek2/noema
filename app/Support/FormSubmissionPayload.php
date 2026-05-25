<?php

namespace App\Support;

use App\Models\FormSettings;
use App\Models\FormSubmission;
use App\Models\LandingPageSection;
use App\Services\LandingFormService;

class FormSubmissionPayload
{
    /**
     * @return array<string, string>
     */
    public static function fieldLabels(FormSubmission $submission): array
    {
        $labels = [];

        if ($submission->landing_page_section_id) {
            $section = LandingPageSection::query()->find($submission->landing_page_section_id);

            if ($section) {
                $schema = app(LandingFormService::class)->normalizeFormContent($section->content ?? []);

                foreach ($schema['fields'] as $field) {
                    $labels[$field['key']] = $field['label'];
                }
            }
        }

        if ($labels === [] && filled($submission->form_key)) {
            $found = app(LandingFormService::class)->findFormByKey($submission->form_key);

            if ($found) {
                foreach ($found['schema']['fields'] as $field) {
                    $labels[$field['key']] = $field['label'];
                }
            }
        }

        if ($labels === [] && filled($submission->form_key)) {
            $settings = FormSettings::current();
            $consultationKey = $settings->consultation_form_key ?: 'consultation';

            if ($submission->form_key === $consultationKey) {
                foreach ($settings->consultationSchema()['fields'] as $field) {
                    $labels[$field['key']] = $field['label'];
                }
            }
        }

        return $labels;
    }

    /**
     * @return list<array{key: string, label: string, value: string}>
     */
    public static function rows(FormSubmission $submission): array
    {
        $payload = $submission->payloadArray();
        $labels = self::fieldLabels($submission);

        if ($payload === []) {
            return [];
        }

        $rows = [];
        $used = [];

        foreach (array_keys($labels) as $key) {
            if (! array_key_exists($key, $payload)) {
                continue;
            }

            $rows[] = [
                'key' => $key,
                'label' => $labels[$key],
                'value' => self::formatValue($payload[$key]),
            ];
            $used[$key] = true;
        }

        foreach ($payload as $key => $value) {
            if (isset($used[$key])) {
                continue;
            }

            $rows[] = [
                'key' => (string) $key,
                'label' => $labels[$key] ?? (string) $key,
                'value' => self::formatValue($value),
            ];
        }

        return $rows;
    }

    public static function toHtml(FormSubmission $submission): string
    {
        $rows = self::rows($submission);

        if ($rows === []) {
            return '<p style="margin:0;font-size:0.875rem;color:rgb(107 114 128);">—</p>';
        }

        $html = '<dl style="margin:0;display:grid;gap:0;">';

        foreach ($rows as $row) {
            $html .= '<div style="display:grid;gap:0.35rem;padding:0.85rem 0;border-bottom:1px solid rgba(0,0,0,.06);">';
            $html .= '<dt style="margin:0;font-size:0.68rem;font-weight:600;letter-spacing:0.12em;text-transform:uppercase;color:rgb(107 114 128);">'.e($row['label']).'</dt>';
            $html .= '<dd style="margin:0;font-size:0.9rem;line-height:1.5;white-space:pre-wrap;word-break:break-word;color:rgb(17 24 39);">'.e($row['value']).'</dd>';
            $html .= '</div>';
        }

        $html .= '</dl>';

        return $html;
    }

    public static function formatValue(mixed $value): string
    {
        if (is_bool($value)) {
            return $value ? 'Так' : 'Ні';
        }

        if (is_array($value)) {
            return json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '—';
        }

        $string = trim((string) ($value ?? ''));

        return $string !== '' ? $string : '—';
    }
}
