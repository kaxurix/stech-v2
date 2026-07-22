<?php

namespace App\Filament\Resources\Registrations\Pages;

use App\Filament\Resources\Registrations\RegistrationResource;
use App\Models\Registration;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Auth;

class ViewRegistration extends ViewRecord
{
    protected static string $resource = RegistrationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('approve')
                ->label('Verifikasi Pembayaran')
                ->icon(Heroicon::OutlinedCheckCircle)
                ->color('success')
                ->visible(fn (Registration $record) => $record->status === 'pending_verification')
                ->requiresConfirmation()
                ->schema([
                    Textarea::make('notes')->label('Catatan (opsional)')->maxLength(500),
                ])
                ->action(function (Registration $record, array $data): void {
                    $record->payment?->update([
                        'status'      => 'approved',
                        'verified_at' => now(),
                        'verified_by' => Auth::id(),
                        'admin_notes' => $data['notes'] ?? null,
                    ]);
                    $record->update(['status' => 'verified']);

                    Notification::make()
                        ->title("Pembayaran {$record->team_name} berhasil diverifikasi")
                        ->success()
                        ->send();
                }),

            Action::make('reject')
                ->label('Tolak Pembayaran')
                ->icon(Heroicon::OutlinedXCircle)
                ->color('danger')
                ->visible(fn (Registration $record) => $record->status === 'pending_verification')
                ->requiresConfirmation()
                ->schema([
                    Textarea::make('notes')->label('Alasan penolakan')->required()->maxLength(500),
                ])
                ->action(function (Registration $record, array $data): void {
                    $record->payment?->update([
                        'status'      => 'rejected',
                        'verified_at' => now(),
                        'verified_by' => Auth::id(),
                        'admin_notes' => $data['notes'],
                    ]);
                    $record->update(['status' => 'rejected']);

                    Notification::make()
                        ->title("Pembayaran {$record->team_name} ditolak")
                        ->danger()
                        ->send();
                }),

            Action::make('toggleFinalist')
                ->label(fn (Registration $record) => $record->is_finalist ? 'Batalkan dari Final' : 'Loloskan ke Final')
                ->icon(Heroicon::OutlinedTrophy)
                ->color('warning')
                ->visible(fn (Registration $record) => $record->status === 'verified')
                ->requiresConfirmation()
                ->action(function (Registration $record): void {
                    $record->update(['is_finalist' => ! $record->is_finalist]);

                    Notification::make()
                        ->title($record->is_finalist
                            ? "{$record->team_name} diloloskan ke final"
                            : "{$record->team_name} dibatalkan dari final")
                        ->success()
                        ->send();
                }),

            EditAction::make(),
        ];
    }
}
