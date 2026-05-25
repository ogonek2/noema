<?php

namespace App\Filament\Resources\FormSubmissions;

use App\Filament\Resources\FormSubmissions\Pages\ListFormSubmissions;
use App\Filament\Resources\FormSubmissions\Pages\ViewFormSubmission;
use App\Models\FormSubmission;
use App\Support\FormSubmissionPayload;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class FormSubmissionResource extends Resource
{
    protected static ?string $model = FormSubmission::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-inbox';

    protected static string|\UnitEnum|null $navigationGroup = 'Форми';

    protected static ?int $navigationSort = 10;

    protected static ?string $modelLabel = 'Заявка';

    protected static ?string $pluralModelLabel = 'Заявки';

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit($record): bool
    {
        return false;
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Заявка')->schema([
                TextEntry::make('id')->label('ID'),
                TextEntry::make('form_title')->label('Форма'),
                TextEntry::make('form_key')->label('Ключ')->copyable(),
                TextEntry::make('landing_page_slug')
                    ->label('Сторінка')
                    ->placeholder('Глобальна форма')
                    ->url(fn (FormSubmission $record): ?string => filled($record->landing_page_slug)
                        ? route('landing.show', $record->landing_page_slug)
                        : null)
                    ->openUrlInNewTab(),
                TextEntry::make('created_at')->label('Надіслано')->dateTime('d.m.Y H:i'),
                IconEntry::make('telegram_sent')
                    ->label('Telegram')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle'),
                IconEntry::make('read_at')
                    ->label('Прочитано')
                    ->boolean()
                    ->getStateUsing(fn (FormSubmission $record): bool => $record->read_at !== null),
            ])->columns(3),
            Section::make('Дані форми')->schema([
                TextEntry::make('payload_display')
                    ->hiddenLabel()
                    ->state(fn (FormSubmission $record): string => FormSubmissionPayload::toHtml($record))
                    ->html()
                    ->columnSpanFull(),
            ]),
            Section::make('Telegram')
                ->schema([
                    TextEntry::make('telegram_error')
                        ->label('Помилка')
                        ->placeholder('—')
                        ->color('danger')
                        ->columnSpanFull(),
                ])
                ->visible(fn (FormSubmission $record): bool => ! $record->telegram_sent && filled($record->telegram_error))
                ->collapsed(false),
            Section::make('Технічні дані')->schema([
                TextEntry::make('ip_address')->label('IP')->placeholder('—'),
                TextEntry::make('referer')->label('Referer')->placeholder('—')->columnSpanFull(),
                TextEntry::make('user_agent')->label('User-Agent')->placeholder('—')->columnSpanFull(),
            ])->columns(2)->collapsed(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                IconColumn::make('read_at')
                    ->label('')
                    ->boolean()
                    ->getStateUsing(fn (FormSubmission $record): bool => $record->read_at !== null)
                    ->trueIcon('heroicon-o-envelope-open')
                    ->falseIcon('heroicon-o-envelope')
                    ->trueColor('gray')
                    ->falseColor('primary'),
                TextColumn::make('form_title')->label('Форма')->searchable()->limit(28),
                TextColumn::make('landing_page_slug')->label('Сторінка')->searchable()->toggleable(),
                TextColumn::make('payload_preview')
                    ->label('Дані')
                    ->state(function (FormSubmission $record): string {
                        $rows = FormSubmissionPayload::rows($record);

                        if ($rows === []) {
                            return '—';
                        }

                        $first = $rows[0];

                        return $first['value'] !== '—'
                            ? $first['value']
                            : $first['label'];
                    })
                    ->limit(40),
                IconColumn::make('telegram_sent')->label('TG')->boolean()->toggleable(),
                TextColumn::make('created_at')->label('Дата')->dateTime('d.m.Y H:i')->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Filter::make('unread')
                    ->label('Непрочитані')
                    ->query(fn (Builder $query): Builder => $query->whereNull('read_at')),
                SelectFilter::make('form_key')
                    ->label('Ключ форми')
                    ->options(fn (): array => FormSubmission::query()
                        ->whereNotNull('form_key')
                        ->distinct()
                        ->orderBy('form_key')
                        ->pluck('form_key', 'form_key')
                        ->all()),
            ])
            ->recordUrl(fn (FormSubmission $record): string => static::getUrl('view', ['record' => $record]));
    }

    public static function getPages(): array
    {
        return [
            'index' => ListFormSubmissions::route('/'),
            'view' => ViewFormSubmission::route('/{record}'),
        ];
    }
}
