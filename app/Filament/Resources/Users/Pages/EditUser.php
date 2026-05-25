<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use App\Models\User;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Exceptions\Halt;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->label('Видалити')
                ->before(function (DeleteAction $action, User $record): void {
                    if ($record->id === auth()->id()) {
                        Notification::make()
                            ->title('Не можна видалити свій обліковий запис')
                            ->danger()
                            ->send();

                        throw new Halt;
                    }

                    if (User::query()->count() <= 1) {
                        Notification::make()
                            ->title('Не можна видалити останнього адміністратора')
                            ->danger()
                            ->send();

                        throw new Halt;
                    }
                }),
        ];
    }
}
