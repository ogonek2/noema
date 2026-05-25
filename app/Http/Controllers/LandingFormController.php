<?php

namespace App\Http\Controllers;

use App\Services\LandingFormService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LandingFormController extends Controller
{
    public function __construct(
        private readonly LandingFormService $forms,
    ) {}

    public function store(Request $request, string $formKey): JsonResponse
    {
        $context = $this->forms->findFormByKey($formKey);

        if ($context === null) {
            return response()->json(['message' => 'Форму не знайдено.'], 404);
        }

        $input = $request->input('fields', []);

        if (! is_array($input)) {
            return response()->json(['message' => 'Невірний формат даних.'], 422);
        }

        try {
            $result = $this->forms->validateSubmission($context['schema'], $input);
        } catch (\Illuminate\Validation\ValidationException $exception) {
            return response()->json([
                'message' => 'Перевірте правильність заповнення полів.',
                'errors' => $exception->errors(),
            ], 422);
        }

        $submission = $this->forms->storeSubmission(
            $context['schema'],
            $result['validated'],
            $context['page'],
            $context['section'],
            $request->ip(),
            $request->userAgent(),
            $request->headers->get('referer'),
        );

        return response()->json([
            'message' => $context['schema']['success_message'] ?? 'Дякуємо! Ми звʼяжемося з вами найближчим часом.',
            'submission_id' => $submission->id,
        ]);
    }
}
