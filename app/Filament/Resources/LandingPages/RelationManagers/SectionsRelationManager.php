<?php

namespace App\Filament\Resources\LandingPages\RelationManagers;

use App\Enums\LandingSectionType;
use App\Filament\Resources\LandingPages\Schemas\LandingSectionForm;
use App\Services\LandingPageService;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Width;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SectionsRelationManager extends RelationManager
{
    protected static string $relationship = 'sections';

    protected static ?string $title = 'Секції сторінки';

    public function form(Schema $schema): Schema
    {
        return $schema->components(LandingSectionForm::schema());
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('sort_order')->label('#')->sortable(),
                TextColumn::make('displayLabel')
                    ->label('Секція')
                    ->state(fn ($record) => $record->displayLabel()),
                TextColumn::make('type')
                    ->label('Тип')
                    ->badge()
                    ->formatStateUsing(fn (LandingSectionType $state): string => $state->label()),
                IconColumn::make('is_active')->label('Активна')->boolean(),
            ])
            ->headerActions([
                $this->makeSectionAction(CreateAction::make()),
            ])
            ->recordActions([
                $this->makeSectionAction(EditAction::make()),
                DeleteAction::make(),
            ])
            ->reorderable('sort_order')
            ->defaultSort('sort_order');
    }

    /** @template T of CreateAction|EditAction */
    private function makeSectionAction(CreateAction|EditAction $action): CreateAction|EditAction
    {
        return $action
            ->slideOver()
            ->modalWidth(Width::ScreenExtraLarge)
            ->extraModalWindowAttributes(['class' => 'fi-landing-section-modal'])
            ->mutateFormDataUsing(fn (array $data): array => $this->prepareSectionData($data));
    }

    /** @param  array<string, mixed>  $data */
    private function prepareSectionData(array $data): array
    {
        $type = LandingSectionType::from($data['type']);
        $data['content'] = app(LandingPageService::class)->normalizeContent(
            $type,
            is_array($data['content'] ?? null) ? $data['content'] : [],
        );

        return $data;
    }
}
