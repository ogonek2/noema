<?php

namespace App\Filament\Resources\FormSubmissions\Pages;

use App\Filament\Resources\FormSubmissions\FormSubmissionResource;
use App\Models\FormSubmission;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\ViewRecord;

class ViewFormSubmission extends ViewRecord
{
    protected static string $resource = FormSubmissionResource::class;

    public function mount(int|string $record): void
    {
        parent::mount($record);

        $this->record->markAsRead();
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('markUnread')
                ->label('Позначити непрочитаним')
                ->icon('heroicon-o-envelope')
                ->color('gray')
                ->visible(fn (FormSubmission $record): bool => $record->read_at !== null)
                ->action(function (FormSubmission $record): void {
                    $record->forceFill(['read_at' => null])->save();
                    $this->refreshFormData(['read_at']);
                }),
            DeleteAction::make(),
        ];
    }
}
