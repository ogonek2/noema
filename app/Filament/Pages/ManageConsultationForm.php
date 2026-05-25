<?php

namespace App\Filament\Pages;

use App\Enums\LandingFormFieldType;
use App\Models\FormSettings;
use Filament\Actions\Action;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ManageConsultationForm extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-chat-bubble-bottom-center-text';

    protected static string|\UnitEnum|null $navigationGroup = 'Форми';

    protected static ?string $navigationLabel = 'Консультація (віджет)';

    protected static ?int $navigationSort = 15;

    protected static ?string $title = 'Плаваюча форма консультації';

    protected string $view = 'filament.pages.manage-settings';

    public ?array $data = [];

    public function mount(): void
    {
        $settings = FormSettings::current();

        $this->form->fill([
            'consultation_enabled' => $settings->consultation_enabled,
            'consultation_form_key' => $settings->consultation_form_key ?: 'consultation',
            'consultation_title' => $settings->consultation_title,
            'consultation_subtitle' => $settings->consultation_subtitle,
            'consultation_success_message' => $settings->consultation_success_message,
            'consultation_fields' => $settings->consultation_fields ?: FormSettings::defaultConsultationFields(),
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Віджет на сайті')
                    ->description('Кнопка «трубка» справа внизу. Заявки зберігаються в розділі «Заявки» з ключем форми.')
                    ->schema([
                        Toggle::make('consultation_enabled')
                            ->label('Увімкнути плаваючу кнопку та форму')
                            ->default(true)
                            ->columnSpanFull(),
                        TextInput::make('consultation_form_key')
                            ->label('Ключ форми')
                            ->default('consultation')
                            ->helperText('Endpoint: POST /forms/{ключ}. За замовчуванням: consultation'),
                        TextInput::make('consultation_title')
                            ->label('Заголовок вікна')
                            ->columnSpanFull(),
                        Textarea::make('consultation_subtitle')
                            ->label('Підзаголовок')
                            ->rows(2)
                            ->columnSpanFull(),
                        Textarea::make('consultation_success_message')
                            ->label('Повідомлення після відправки')
                            ->rows(2)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Section::make('Поля форми')
                    ->schema([
                        Repeater::make('consultation_fields')
                            ->label('Поля')
                            ->schema([
                                TextInput::make('label')
                                    ->label('Назва')
                                    ->required()
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function (?string $state, callable $set, $get): void {
                                        if (filled($get('key'))) {
                                            return;
                                        }

                                        $set('key', \Illuminate\Support\Str::slug((string) $state, '_'));
                                    }),
                                TextInput::make('key')
                                    ->label('Ключ')
                                    ->required()
                                    ->alphaDash(),
                                Select::make('type')
                                    ->label('Тип')
                                    ->options(LandingFormFieldType::options())
                                    ->required()
                                    ->native(false),
                                Toggle::make('required')->label('Обовʼязкове'),
                                Select::make('width')
                                    ->label('Ширина')
                                    ->options(['full' => 'Повна', 'half' => 'Половина'])
                                    ->default('full')
                                    ->native(false),
                                TextInput::make('placeholder')->label('Placeholder'),
                                TextInput::make('mask')
                                    ->label('Маска')
                                    ->placeholder('+380 (99) 999-99-99'),
                                Hidden::make('sort_order')->default(0),
                            ])
                            ->columns(2)
                            ->collapsible()
                            ->reorderable()
                            ->orderColumn('sort_order')
                            ->itemLabel(fn (array $state): string => $state['label'] ?? 'Поле')
                            ->columnSpanFull(),
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
                    $data = $this->form->getState();

                    $data['consultation_fields'] = collect($data['consultation_fields'] ?? [])
                        ->values()
                        ->map(fn (array $field, int $index): array => array_merge($field, [
                            'sort_order' => $index,
                        ]))
                        ->all();

                    FormSettings::current()->fill($data)->save();

                    Notification::make()->title('Форму консультації збережено')->success()->send();
                }),
        ];
    }
}
