<?php

namespace App\Filament\Pages;

use App\Models\CheckoutSettings;
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

class ManageCheckoutSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-credit-card';

    protected static string|\UnitEnum|null $navigationGroup = 'CRM';

    protected static ?string $navigationLabel = 'Оплата та доставка';

    protected static ?int $navigationSort = 11;

    protected static ?string $title = 'Налаштування оформлення замовлення';

    protected string $view = 'filament.pages.manage-settings';

    public ?array $data = [];

    public function mount(): void
    {
        $settings = CheckoutSettings::current();
        $data = $settings->toArray();
        unset($data['liqpay_private_key']);
        $this->form->fill($data);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('LiqPay')->schema([
                    Toggle::make('liqpay_enabled')->label('Увімкнено'),
                    TextInput::make('liqpay_public_key')->label('Public key'),
                    TextInput::make('liqpay_private_key')
                        ->label('Private key')
                        ->password()
                        ->revealable()
                        ->helperText('Порожнє = не змінювати'),
                    Toggle::make('liqpay_sandbox')->label('Sandbox'),
                    TextInput::make('liqpay_currency')->label('Валюта')->default('UAH'),
                ])->columns(2),
                Section::make('Інші способи оплати')->schema([
                    Toggle::make('cod_enabled')->label('Накладений платіж'),
                    Toggle::make('iban_enabled')->label('Оплата на IBAN'),
                    TextInput::make('iban_holder')->label('Отримувач'),
                    TextInput::make('iban_number')->label('IBAN'),
                    TextInput::make('iban_bank')->label('Банк'),
                    TextInput::make('iban_purpose')->label('Призначення платежу')->columnSpanFull(),
                ])->columns(2),
                Section::make('Доставка')->schema([
                    TextInput::make('default_shipping_cost')
                        ->label('Вартість доставки за замовчуванням')
                        ->numeric()
                        ->prefix('$'),
                ]),
                Section::make('Примітки')->schema([
                    Textarea::make('notes')->rows(3)->columnSpanFull(),
                ]),
            ])
            ->statePath('data');
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('save')
                ->label('Зберегти')
                ->action(function (): void {
                    $settings = CheckoutSettings::current();
                    $data = $this->data;

                    if (blank($data['liqpay_private_key'] ?? null)) {
                        unset($data['liqpay_private_key']);
                    }

                    $settings->fill($data);
                    $settings->save();

                    Notification::make()->title('Налаштування збережено')->success()->send();
                }),
        ];
    }
}
