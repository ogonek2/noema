<?php

namespace App\Filament\Resources\Products\RelationManagers;

use App\Filament\Concerns\UsesBunnyUpload;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ImagesRelationManager extends RelationManager
{
    use UsesBunnyUpload;

    protected static string $relationship = 'images';

    protected static ?string $title = 'Галерея';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            static::bunnyUpload('path', 'products/gallery')
                ->label('Зображення')
                ->required()
                ->columnSpanFull(),
            TextInput::make('alt_text')->label('Alt текст'),
            Toggle::make('is_primary')->label('Головне фото в каталозі'),
            TextInput::make('sort_order')->label('Сортування')->numeric()->default(0),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('path')->label('Фото')->disk('bunny')->height(72),
                TextColumn::make('alt_text')->label('Alt'),
                IconColumn::make('is_primary')->label('Головне')->boolean(),
                TextColumn::make('sort_order')->label('Сорт.'),
            ])
            ->headerActions([
                CreateAction::make()->label('Додати фото'),
                Action::make('bulkUpload')
                    ->label('Завантажити кілька')
                    ->icon('heroicon-o-photo')
                    ->form([
                        static::bunnyUpload('paths', 'products/gallery')
                            ->label('Зображення')
                            ->multiple()
                            ->required()
                            ->minFiles(1)
                            ->maxFiles(20)
                            ->reorderable()
                            ->columnSpanFull(),
                        Toggle::make('first_primary')->label('Перше фото — головне')->default(true),
                    ])
                    ->action(function (array $data): void {
                        $product = $this->getOwnerRecord();
                        $paths = array_values($data['paths'] ?? []);
                        $maxSort = (int) $product->images()->max('sort_order');
                        $isFirst = true;

                        $markFirstPrimary = (bool) ($data['first_primary'] ?? false);

                        foreach ($paths as $path) {
                            if (! is_string($path) || $path === '') {
                                continue;
                            }
                            $maxSort += 10;
                            $makePrimary = $markFirstPrimary && $isFirst;

                            if ($makePrimary) {
                                $product->images()->update(['is_primary' => false]);
                            }

                            $product->images()->create([
                                'path' => $path,
                                'sort_order' => $maxSort,
                                'is_primary' => $makePrimary,
                            ]);

                            $isFirst = false;
                        }

                        $product->refresh();

                        Notification::make()
                            ->title('Завантажено '.count($paths).' фото')
                            ->success()
                            ->send();
                    }),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->reorderable('sort_order')
            ->defaultSort('sort_order');
    }
}
