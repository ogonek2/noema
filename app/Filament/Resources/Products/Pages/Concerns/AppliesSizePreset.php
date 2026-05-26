<?php

namespace App\Filament\Resources\Products\Pages\Concerns;

use App\Models\SizePreset;
use App\Services\SizePresetService;
use Filament\Actions\Action;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;

trait AppliesSizePreset
{
    protected function getApplySizePresetAction(): Action
    {
        return Action::make('applySizePreset')
            ->label('Застосувати пресет')
            ->icon('heroicon-o-table-cells')
            ->modalHeading('Застосувати пресет розмірів')
            ->modalDescription('Скопіює розмірну сітку та варіанти з обраного пресета до цього товару. Можна замінити існуючі дані або додати лише відсутні частини.')
            ->form([
                Select::make('size_preset_id')
                    ->label('Пресет')
                    ->options(fn (): array => SizePreset::query()->active()->orderBy('sort_order')->orderBy('name')->pluck('name', 'id')->all())
                    ->searchable()
                    ->required()
                    ->default(fn () => $this->getRecord()->size_preset_id),
                Checkbox::make('replace_chart')
                    ->label('Замінити розмірну сітку (таблицю вимірів)')
                    ->default(true),
                Checkbox::make('replace_variants')
                    ->label('Замінити розміри товару (SKU / залишки)')
                    ->default(true),
                Checkbox::make('apply_intro')
                    ->label('Скопіювати вступ до таблиці')
                    ->default(true),
                Checkbox::make('apply_length_guide')
                    ->label('Скопіювати рекомендації по довжині')
                    ->default(true),
            ])
            ->action(function (array $data, SizePresetService $service): void {
                $preset = SizePreset::query()->findOrFail($data['size_preset_id']);

                $service->applyToProduct(
                    $preset,
                    $this->getRecord(),
                    replaceChart: (bool) ($data['replace_chart'] ?? true),
                    replaceVariants: (bool) ($data['replace_variants'] ?? true),
                    applyIntro: (bool) ($data['apply_intro'] ?? true),
                    applyLengthGuide: (bool) ($data['apply_length_guide'] ?? true),
                );

                Notification::make()
                    ->title('Пресет застосовано')
                    ->body('Розмірна сітка та варіанти оновлені з пресета «'.$preset->name.'».')
                    ->success()
                    ->send();

                $this->redirect(static::getResource()::getUrl('edit', ['record' => $this->getRecord()]));
            });
    }
}
