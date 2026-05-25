<?php

namespace App\Http\Controllers\Admin;

use App\Enums\HomepageBlockSlug;
use App\Http\Controllers\Controller;
use App\Models\HomepageAudienceCard;
use App\Models\HomepageBenefit;
use App\Models\HomepageReview;
use App\Models\HomepageRibbonImage;
use App\Services\BunnyStorageService;
use App\Services\HomepageContentService;
use App\Support\MediaUrl;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
class HomepageContentController extends Controller
{
    public function __construct(private readonly HomepageContentService $homepage) {}

    public function show(): JsonResponse
    {
        return response()->json($this->homepage->adminPayload());
    }

    public function updateBlock(Request $request, string $slug): JsonResponse
    {
        $blockSlug = HomepageBlockSlug::from($slug);

        $validated = $request->validate([
            'content' => ['required', 'array'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $block = $this->homepage->updateBlock(
            $blockSlug,
            $validated['content'],
            $validated['is_active'] ?? null,
        );

        return response()->json([
            'message' => 'Блок збережено',
            'block' => [
                'slug' => $block->slug,
                'label' => $block->label,
                'content' => $block->content,
                'is_active' => $block->is_active,
            ],
        ]);
    }

    public function updateGlobals(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'spotlight_product_id' => ['nullable', 'integer', 'exists:products,id'],
            'featured_product_ids' => ['nullable', 'array'],
            'featured_product_ids.*' => ['integer', 'exists:products,id'],
            'use_catalog_audience' => ['sometimes', 'boolean'],
        ]);

        $globals = $this->homepage->updateGlobals($validated);

        return response()->json([
            'message' => 'Глобальні налаштування збережено',
            'globals' => $this->homepage->globalsPayload(),
        ]);
    }

    public function upload(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'file' => ['required', 'file', 'image', 'max:10240'],
            'directory' => ['nullable', 'string', 'max:120'],
        ]);

        $directory = trim($validated['directory'] ?? 'homepage', '/');
        $file = $validated['file'];
        $filename = uniqid('img_', true).'.'.$file->getClientOriginalExtension();
        $path = $directory.'/'.$filename;

        $bunny = app(BunnyStorageService::class);
        $bunny->upload($path, file_get_contents($file->getRealPath()), $file->getMimeType() ?? 'image/jpeg');

        return response()->json([
            'path' => $path,
            'url' => MediaUrl::resolve($path),
        ]);
    }

    public function storeReview(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'quote' => ['required', 'string'],
            'author_name' => ['required', 'string', 'max:120'],
            'author_role' => ['nullable', 'string', 'max:120'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $review = HomepageReview::query()->create($validated);

        return response()->json(['message' => 'Відгук додано', 'item' => $review], 201);
    }

    public function updateReview(Request $request, HomepageReview $review): JsonResponse
    {
        $validated = $request->validate([
            'quote' => ['sometimes', 'string'],
            'author_name' => ['sometimes', 'string', 'max:120'],
            'author_role' => ['nullable', 'string', 'max:120'],
            'sort_order' => ['sometimes', 'integer', 'min:0'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $review->update($validated);

        return response()->json(['message' => 'Відгук оновлено', 'item' => $review->fresh()]);
    }

    public function destroyReview(HomepageReview $review): JsonResponse
    {
        $review->delete();

        return response()->json(['message' => 'Відгук видалено']);
    }

    public function storeAudienceCard(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'image_path' => ['nullable', 'string', 'max:500'],
            'href' => ['nullable', 'string', 'max:500'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $card = HomepageAudienceCard::query()->create($validated);

        return response()->json(['message' => 'Картку додано', 'item' => $card], 201);
    }

    public function updateAudienceCard(Request $request, HomepageAudienceCard $audienceCard): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:120'],
            'image_path' => ['nullable', 'string', 'max:500'],
            'href' => ['nullable', 'string', 'max:500'],
            'sort_order' => ['sometimes', 'integer', 'min:0'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $audienceCard->update($validated);

        return response()->json(['message' => 'Картку оновлено', 'item' => $audienceCard->fresh()]);
    }

    public function destroyAudienceCard(HomepageAudienceCard $audienceCard): JsonResponse
    {
        $audienceCard->delete();

        return response()->json(['message' => 'Картку видалено']);
    }

    public function storeBenefit(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'number_label' => ['required', 'string', 'max:16'],
            'title' => ['required', 'string', 'max:120'],
            'text' => ['nullable', 'string', 'max:255'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $benefit = HomepageBenefit::query()->create($validated);

        return response()->json(['message' => 'Перевагу додано', 'item' => $benefit], 201);
    }

    public function updateBenefit(Request $request, HomepageBenefit $benefit): JsonResponse
    {
        $validated = $request->validate([
            'number_label' => ['sometimes', 'string', 'max:16'],
            'title' => ['sometimes', 'string', 'max:120'],
            'text' => ['nullable', 'string', 'max:255'],
            'sort_order' => ['sometimes', 'integer', 'min:0'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $benefit->update($validated);

        return response()->json(['message' => 'Перевагу оновлено', 'item' => $benefit->fresh()]);
    }

    public function destroyBenefit(HomepageBenefit $benefit): JsonResponse
    {
        $benefit->delete();

        return response()->json(['message' => 'Перевагу видалено']);
    }

    public function storeRibbonImage(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'path' => ['required', 'string', 'max:500'],
            'alt_text' => ['nullable', 'string', 'max:255'],
            'width' => ['nullable', 'integer', 'min:1'],
            'height' => ['nullable', 'integer', 'min:1'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $image = HomepageRibbonImage::query()->create($validated);

        return response()->json([
            'message' => 'Зображення додано',
            'item' => array_merge($image->toArray(), ['url' => MediaUrl::resolve($image->path)]),
        ], 201);
    }

    public function updateRibbonImage(Request $request, HomepageRibbonImage $ribbonImage): JsonResponse
    {
        $validated = $request->validate([
            'path' => ['sometimes', 'string', 'max:500'],
            'alt_text' => ['nullable', 'string', 'max:255'],
            'width' => ['sometimes', 'integer', 'min:1'],
            'height' => ['sometimes', 'integer', 'min:1'],
            'sort_order' => ['sometimes', 'integer', 'min:0'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $ribbonImage->update($validated);

        return response()->json([
            'message' => 'Зображення оновлено',
            'item' => array_merge($ribbonImage->fresh()->toArray(), ['url' => MediaUrl::resolve($ribbonImage->path)]),
        ]);
    }

    public function destroyRibbonImage(HomepageRibbonImage $ribbonImage): JsonResponse
    {
        $ribbonImage->delete();

        return response()->json(['message' => 'Зображення видалено']);
    }
}
