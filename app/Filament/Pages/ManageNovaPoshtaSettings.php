<?php

namespace App\Filament\Pages;

use App\Models\NovaPoshtaSettings;
use App\Services\NovaPoshtaService;
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

class ManageNovaPoshtaSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-truck';

    protected static string|\UnitEnum|null $navigationGroup = 'CRM';

    protected static ?string $navigationLabel = 'Нова Пошта (API)';

    protected static ?int $navigationSort = 10;

    protected static ?string $title = 'Нова Пошта — API та відправник';

    protected string $view = 'filament.pages.manage-settings';

    public ?array $data = [];

    public function mount(): void
    {
        $data = NovaPoshtaSettings::current()->toArray();
        unset($data['api_key']);
        $this->form->fill($data);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('API')->schema([
                    Toggle::make('is_active')->label('Інтеграція увімкнена')->default(true),
                    TextInput::make('api_key')
                        ->label('API-ключ')
                        ->password()
                        ->revealable()
                        ->helperText('Залиште порожнім, щоб не змінювати. Fallback: NOVA_POSHTA_API_KEY у .env'),
                    TextInput::make('api_url')->label('URL API')->default('https://api.novaposhta.ua/v2.0/json/'),
                    Toggle::make('verify_ssl')->label('Перевірка SSL')->default(true),
                    TextInput::make('timeout')->label('Таймаут (сек)')->numeric()->default(20),
                ])->columns(2),
                Section::make('Відправник (для ТТН)')->schema([
                    TextInput::make('sender_ref')->label('Ref відправника'),
                    TextInput::make('contact_sender_ref')->label('Ref контакту'),
                    TextInput::make('city_sender_ref')->label('Ref міста відправника'),
                    TextInput::make('sender_address_ref')->label('Ref відділення / адреси'),
                    TextInput::make('sender_phone')->label('Телефон відправника')->tel(),
                    TextInput::make('sender_name')->label('Назва / ПІБ відправника'),
                    TextInput::make('sender_warehouse_name')->label('Відділення (для довідки)'),
                ])->columns(2)
                    ->description('Ref-и з кабінету my.novaposhta.ua або через API Counterparty. Без них ТТН не створиться.'),
                Section::make('За замовчуванням для ТТН')->schema([
                    TextInput::make('default_weight')->label('Вага, кг')->numeric()->default(1),
                    TextInput::make('default_seats')->label('Місць')->numeric()->default(1),
                    TextInput::make('default_description')->label('Опис')->default('Товар NOEMA'),
                    TextInput::make('cargo_type')->label('Тип вантажу')->default('Cargo'),
                    TextInput::make('payment_method')->label('Оплата доставки')->default('NonCash'),
                    TextInput::make('payer_type')->label('Платник')->default('Recipient'),
                ])->columns(2),
                Section::make('Примітки')->schema([
                    Textarea::make('notes')->label('Внутрішні нотатки')->rows(3)->columnSpanFull(),
                ]),
            ])
            ->statePath('data');
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('testApi')
                ->label('Перевірити API')
                ->action(function (): void {
                    $settings = NovaPoshtaSettings::current();
                    $data = $this->data;

                    if (blank($data['api_key'] ?? null)) {
                        unset($data['api_key']);
                    }

                    $settings->fill($data);
                    $settings->save();

                    $result = app(NovaPoshtaService::class)->testConnection();

                    Notification::make()
                        ->title($result['success'] ? 'API працює' : 'Помилка API')
                        ->body($result['message'])
                        ->{$result['success'] ? 'success' : 'danger'}()
                        ->send();
                }),
            Action::make('save')
                ->label('Зберегти')
                ->action(function (): void {
                    $settings = NovaPoshtaSettings::current();
                    $data = $this->data;

                    if (blank($data['api_key'] ?? null)) {
                        unset($data['api_key']);
                    }

                    $settings->fill($data);
                    $settings->save();

                    Notification::make()->title('Налаштування збережено')->success()->send();
                }),
        ];
    }
}
