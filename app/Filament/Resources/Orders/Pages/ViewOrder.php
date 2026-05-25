<?php

namespace App\Filament\Resources\Orders\Pages;

use App\Enums\OrderStatus;
use App\Filament\Resources\Orders\OrderResource;
use App\Services\NovaPoshtaService;
use App\Services\OrderManagementService;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use RuntimeException;

class ViewOrder extends ViewRecord
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('createTtn')
                ->label('Створити ТТН')
                ->icon('heroicon-o-truck')
                ->color('success')
                ->visible(fn () => $this->record->canCreateTtn())
                ->form([
                    TextInput::make('weight')
                        ->label('Вага (кг)')
                        ->numeric()
                        ->default(fn () => $this->record->shipment_weight ?? 1),
                    TextInput::make('seats')
                        ->label('Місць')
                        ->numeric()
                        ->default(fn () => $this->record->shipment_seats ?? 1),
                    TextInput::make('description')
                        ->label('Опис відправлення')
                        ->default('Товар NOEMA'),
                ])
                ->action(function (array $data, NovaPoshtaService $novaPoshta, OrderManagementService $orders): void {
                    try {
                        $result = $novaPoshta->createTtnForOrder($this->record, [
                            'weight' => $data['weight'] ?? null,
                            'seats' => $data['seats'] ?? null,
                            'description' => $data['description'] ?? null,
                        ]);

                        $this->record->shipment_weight = $data['weight'] ?? $this->record->shipment_weight;
                        $this->record->shipment_seats = $data['seats'] ?? $this->record->shipment_seats;
                        $this->record->save();

                        $orders->recordTtnCreated(
                            $this->record,
                            $result['ref'],
                            $result['number'],
                            auth()->user(),
                        );

                        Notification::make()
                            ->title('ТТН створено: '.$result['number'])
                            ->success()
                            ->send();

                        $this->refreshFormData(['ttn_number', 'ttn_status', 'status']);
                    } catch (RuntimeException $exception) {
                        app(OrderManagementService::class)->recordTtnFailed(
                            $this->record,
                            $exception->getMessage(),
                            auth()->user(),
                        );

                        Notification::make()
                            ->title('Не вдалося створити ТТН')
                            ->body($exception->getMessage())
                            ->danger()
                            ->send();
                    }
                }),
            Action::make('printTtn')
                ->label('Друк ТТН')
                ->icon('heroicon-o-printer')
                ->visible(fn () => filled($this->record->ttn_ref))
                ->url(fn (NovaPoshtaService $novaPoshta) => $novaPoshta->printTtnLink((string) $this->record->ttn_ref))
                ->openUrlInNewTab(),
            Action::make('markPaid')
                ->label('Підтвердити оплату')
                ->icon('heroicon-o-banknotes')
                ->color('warning')
                ->requiresConfirmation()
                ->visible(fn () => $this->record->payment_status->value !== 'paid')
                ->action(function (OrderManagementService $orders): void {
                    $orders->markPaymentPaid($this->record, auth()->user());
                    Notification::make()->title('Оплату підтверджено')->success()->send();
                    $this->refreshFormData(['payment_status', 'status', 'paid_at']);
                }),
            Action::make('markProcessing')
                ->label('В обробку')
                ->action(fn (OrderManagementService $orders) => $this->updateOrderStatus($orders, OrderStatus::Processing)),
            Action::make('markShipped')
                ->label('Відправлено')
                ->action(fn (OrderManagementService $orders) => $this->updateOrderStatus($orders, OrderStatus::Shipped)),
            Action::make('markCompleted')
                ->label('Завершено')
                ->action(fn (OrderManagementService $orders) => $this->updateOrderStatus($orders, OrderStatus::Completed)),
            Action::make('addNote')
                ->label('Нотатка')
                ->icon('heroicon-o-chat-bubble-left-ellipsis')
                ->form([
                    Textarea::make('note')->label('Коментар')->required()->rows(3),
                ])
                ->action(function (array $data, OrderManagementService $orders): void {
                    $orders->addNote($this->record, $data['note'], auth()->user());
                    Notification::make()->title('Нотатку додано')->success()->send();
                }),
            EditAction::make(),
        ];
    }

    private function updateOrderStatus(OrderManagementService $orders, OrderStatus $status): void
    {
        $orders->transitionStatus($this->record, $status, auth()->user());
        Notification::make()->title('Статус оновлено')->success()->send();
        $this->refreshFormData(['status', 'shipped_at', 'completed_at']);
    }
}
