<?php

namespace App\Filament\Pages;

use App\Models\FormSettings;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use App\Support\TelegramHttp;

class ManageFormSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-paper-airplane';

    protected static string|\UnitEnum|null $navigationGroup = 'Форми';

    protected static ?string $navigationLabel = 'Telegram';

    protected static ?int $navigationSort = 20;

    protected static ?string $title = 'Налаштування Telegram для форм';

    protected string $view = 'filament.pages.manage-settings';

    public ?array $data = [];

    public function mount(): void
    {
        $settings = FormSettings::current();
        $data = $settings->only([
            'telegram_enabled',
            'telegram_chat_id',
            'notes',
        ]);
        $data['telegram_bot_token'] = '';
        $this->form->fill($data);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Telegram-бот')
                    ->description('Опційно. Нові заявки з лендінг-форм надсилатимуться в групу або чат.')
                    ->schema([
                        Toggle::make('telegram_enabled')
                            ->label('Увімкнути сповіщення в Telegram')
                            ->live(),
                        TextInput::make('telegram_bot_token')
                            ->label('Токен бота')
                            ->password()
                            ->revealable()
                            ->helperText('Отримайте у @BotFather. Порожнє поле при збереженні = токен не змінюється. На хостингу додайте в .env: TELEGRAM_VERIFY_SSL=false (якщо тест не проходить через SSL).')
                            ->visible(fn ($get): bool => (bool) $get('telegram_enabled')),
                        TextInput::make('telegram_chat_id')
                            ->label('Chat ID')
                            ->placeholder('-1001234567890')
                            ->helperText('ID групи або каналу (бот має бути учасником).')
                            ->visible(fn ($get): bool => (bool) $get('telegram_enabled')),
                    ])
                    ->columns(2),
                Section::make('Примітки')->schema([
                    Textarea::make('notes')->rows(3)->columnSpanFull(),
                ]),
            ])
            ->statePath('data');
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('testTelegram')
                ->label('Тестове повідомлення')
                ->icon('heroicon-o-bolt')
                ->color('gray')
                ->visible(fn (): bool => (bool) FormSettings::current()->telegram_enabled)
                ->action(function (): void {
                    $settings = FormSettings::current();

                    if (! $settings->hasValidTelegramBotToken()) {
                        Notification::make()
                            ->title('Токен бота не налаштований')
                            ->body('Збережіть дійсний токен з @BotFather. Якщо після переносу на сервер токен «зник» — введіть його заново (APP_KEY на сервері має збігатися з тим, під яким його шифрували).')
                            ->danger()
                            ->send();

                        return;
                    }

                    if (blank($settings->telegram_chat_id)) {
                        Notification::make()
                            ->title('Не вказано Chat ID')
                            ->danger()
                            ->send();

                        return;
                    }

                    try {
                        $response = TelegramHttp::client()->post(
                            'https://api.telegram.org/bot'.$settings->telegram_bot_token.'/sendMessage',
                            [
                                'chat_id' => $settings->telegram_chat_id,
                                'text' => '✅ NOEMA: тестове повідомлення з налаштувань форм.',
                                'disable_web_page_preview' => true,
                            ],
                        );

                        if ($response->successful()) {
                            Notification::make()
                                ->title('Повідомлення надіслано')
                                ->success()
                                ->send();

                            return;
                        }

                        Notification::make()
                            ->title('Telegram повернув помилку')
                            ->body($response->json('description') ?? $response->body())
                            ->danger()
                            ->send();
                    } catch (\Throwable $exception) {
                        Notification::make()
                            ->title('Не вдалося надіслати')
                            ->body($exception->getMessage())
                            ->danger()
                            ->send();
                    }
                }),
            Action::make('save')
                ->label('Зберегти')
                ->action(function (): void {
                    $settings = FormSettings::current();
                    $data = $this->data;

                    if (blank($data['telegram_bot_token'] ?? null)) {
                        unset($data['telegram_bot_token']);
                    }

                    $settings->fill($data);
                    $settings->save();

                    Notification::make()->title('Налаштування збережено')->success()->send();
                }),
        ];
    }
}
