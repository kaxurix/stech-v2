<?php

namespace App\Filament\Resources\Registrations\Tables;

use App\Filament\Resources\Registrations\RegistrationResource;
use App\Models\Registration;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class RegistrationsTable
{
    public const CATEGORY_LABELS = [
        'sma'       => 'SMA/SMK',
        'mahasiswa' => 'Mahasiswa',
        'umum'      => 'Umum',
    ];

    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with(['user', 'payment', 'submission']))
            ->columns([
                TextColumn::make('team_name')
                    ->label('Tim / Peserta')
                    ->description(fn (Registration $record) => $record->user->name.' · '.$record->user->email)
                    ->searchable(['team_name'])
                    ->sortable()
                    ->weight('semibold'),

                TextColumn::make('institution')
                    ->label('Institusi')
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('category')
                    ->label('Kategori')
                    ->badge()
                    ->color('gray')
                    ->formatStateUsing(fn (string $state) => self::CATEGORY_LABELS[$state] ?? $state),

                TextColumn::make('created_at')
                    ->label('Daftar')
                    ->date('d M Y')
                    ->sortable(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (Registration $record) => $record->statusLabel())
                    ->color(fn (Registration $record) => match ($record->statusColor()) {
                        'amber' => 'warning',
                        'violet' => 'info',
                        'green' => 'success',
                        'red' => 'danger',
                        default => 'gray',
                    }),

                IconColumn::make('is_finalist')
                    ->label('Finalis')
                    ->boolean()
                    ->toggleable(),

                TextColumn::make('payment.status')
                    ->label('Bukti')
                    ->badge()
                    ->formatStateUsing(fn (?string $state) => match ($state) {
                        'pending' => 'Menunggu',
                        'approved' => 'Disetujui',
                        'rejected' => 'Ditolak',
                        default => 'Belum ada',
                    })
                    ->color(fn (?string $state) => match ($state) {
                        'pending' => 'warning',
                        'approved' => 'success',
                        'rejected' => 'danger',
                        default => 'gray',
                    }),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'pending_payment'      => 'Menunggu Pembayaran',
                        'pending_verification' => 'Menunggu Verifikasi',
                        'verified'             => 'Terverifikasi',
                        'rejected'             => 'Ditolak',
                    ]),
                SelectFilter::make('category')
                    ->label('Kategori')
                    ->options(self::CATEGORY_LABELS),
            ])
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make()
                        ->url(fn (Registration $record) => RegistrationResource::getUrl('view', ['record' => $record])),
                    EditAction::make()
                        ->url(fn (Registration $record) => RegistrationResource::getUrl('edit', ['record' => $record])),

                    Action::make('viewProof')
                        ->label('Lihat Bukti Bayar')
                        ->icon(Heroicon::OutlinedPhoto)
                        ->visible(fn (Registration $record) => $record->payment !== null)
                        ->url(fn (Registration $record) => $record->payment
                            ? route('admin.payment.view', $record->payment->id)
                            : null)
                        ->openUrlInNewTab(),

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
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
