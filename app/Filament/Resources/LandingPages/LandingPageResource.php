<?php

namespace App\Filament\Resources\LandingPages;

use App\Filament\Resources\LandingPages\Pages\CreateLandingPage;
use App\Filament\Resources\LandingPages\Pages\EditLandingPage;
use App\Filament\Resources\LandingPages\Pages\ListLandingPages;
use App\Filament\Resources\LandingPages\RelationManagers\SectionsRelationManager;
use App\Models\LandingPage;
use App\Services\LandingPageService;
use Filament\Actions\Action;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class LandingPageResource extends Resource
{
    protected static ?string $model = LandingPage::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-document-duplicate';

    protected static string|\UnitEnum|null $navigationGroup = 'Контент';

    protected static ?int $navigationSort = 2;

    protected static ?string $modelLabel = 'Лендінг';

    protected static ?string $pluralModelLabel = 'Лендінги';

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Основне')
                ->schema([
                    TextInput::make('title')
                        ->label('Назва сторінки')
                        ->required()
                        ->live(onBlur: true)
                        ->afterStateUpdated(fn (?string $state, callable $set) => $set('slug', Str::slug((string) $state))),
                    TextInput::make('slug')
                        ->label('URL slug')
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->rules(fn (?LandingPage $record): array => LandingPageService::slugRules($record?->id))
                        ->helperText('Адреса: /p/{slug}'),
                    Toggle::make('is_published')
                        ->label('Опубліковано')
                        ->default(false)
                        ->live(),
                    DateTimePicker::make('published_at')
                        ->label('Дата публікації')
                        ->seconds(false),
                    TextInput::make('sort_order')
                        ->label('Сортування в адмінці')
                        ->numeric()
                        ->default(0),
                ])
                ->columns(2),

            Section::make('Відображення на сайті')
                ->schema([
                    Toggle::make('show_navigator')
                        ->label('Показувати шапку сайту')
                        ->default(true),
                    Toggle::make('show_footer')
                        ->label('Показувати футер')
                        ->default(true),
                ])
                ->columns(2),

            Section::make('SEO')
                ->schema([
                    TextInput::make('meta_title')
                        ->label('Meta title')
                        ->columnSpanFull(),
                    Textarea::make('meta_description')
                        ->label('Meta description')
                        ->rows(3)
                        ->columnSpanFull(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')->label('Назва')->searchable()->sortable(),
                TextColumn::make('slug')
                    ->label('URL')
                    ->formatStateUsing(fn (string $state): string => '/p/'.$state)
                    ->copyable(),
                TextColumn::make('sections_count')
                    ->counts('sections')
                    ->label('Секцій'),
                IconColumn::make('is_published')->label('Опубл.')->boolean(),
                TextColumn::make('updated_at')->label('Оновлено')->dateTime('d.m.Y H:i')->sortable(),
            ])
            ->filters([
                TernaryFilter::make('is_published')->label('Опубліковано'),
            ])
            ->recordActions([
                Action::make('preview')
                    ->label('На сайті')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->url(fn (LandingPage $record): string => $record->publicUrl())
                    ->openUrlInNewTab()
                    ->visible(fn (LandingPage $record): bool => $record->is_published),
            ])
            ->defaultSort('sort_order');
    }

    public static function getRelations(): array
    {
        return [
            SectionsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListLandingPages::route('/'),
            'create' => CreateLandingPage::route('/create'),
            'edit' => EditLandingPage::route('/{record}/edit'),
        ];
    }
}
