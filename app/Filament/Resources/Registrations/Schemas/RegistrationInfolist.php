<?php

namespace App\Filament\Resources\Registrations\Schemas;

use App\Filament\Resources\Registrations\Tables\RegistrationsTable;
use App\Models\Registration;
use App\Models\TeamMember;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class RegistrationInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Data Tim')
                    ->columns(3)
                    ->components([
                        TextEntry::make('team_name')->label('Nama Tim'),
                        TextEntry::make('category')
                            ->label('Kategori')
                            ->formatStateUsing(fn (string $state) => RegistrationsTable::CATEGORY_LABELS[$state] ?? $state),
                        TextEntry::make('institution')->label('Institusi'),
                        TextEntry::make('phone')->label('No. HP Ketua Tim'),
                        TextEntry::make('member_count')->label('Jumlah Anggota'),
                        TextEntry::make('created_at')->label('Tanggal Daftar')->dateTime('d M Y, H:i'),
                        TextEntry::make('status')
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
                        TextEntry::make('is_finalist')
                            ->label('Finalis')
                            ->badge()
                            ->formatStateUsing(fn (bool $state) => $state ? 'Ya' : 'Tidak')
                            ->color(fn (bool $state) => $state ? 'success' : 'gray'),
                    ]),

                Section::make('Akun Peserta')
                    ->columns(2)
                    ->components([
                        TextEntry::make('user.name')->label('Nama'),
                        TextEntry::make('user.email')->label('Email'),
                    ]),

                Section::make('Anggota Tim')
                    ->components([
                        RepeatableEntry::make('teamMembers')
                            ->label('')
                            ->schema([
                                Grid::make(3)
                                    ->schema([
                                        TextEntry::make('full_name')
                                            ->label('Nama')
                                            ->formatStateUsing(fn (string $state, TeamMember $record) => $state.($record->is_leader ? ' (Ketua)' : '')),
                                        TextEntry::make('identity_number')->label('NIM/NISN'),
                                        TextEntry::make('institution')->label('Institusi'),
                                        TextEntry::make('major')->label('Jurusan')->placeholder('-'),
                                        TextEntry::make('batch')->label('Angkatan')->placeholder('-'),
                                        TextEntry::make('phone')->label('No. HP'),
                                    ]),
                            ]),
                    ])
                    ->visible(fn (Registration $record) => $record->teamMembers->isNotEmpty()),

                Section::make('Bukti Pembayaran')
                    ->columns(2)
                    ->components([
                        TextEntry::make('payment.original_filename')->label('Nama File'),
                        TextEntry::make('payment.status')
                            ->label('Status Bukti')
                            ->badge()
                            ->formatStateUsing(fn (?string $state) => match ($state) {
                                'pending' => 'Menunggu',
                                'approved' => 'Disetujui',
                                'rejected' => 'Ditolak',
                                default => '-',
                            })
                            ->color(fn (?string $state) => match ($state) {
                                'pending' => 'warning',
                                'approved' => 'success',
                                'rejected' => 'danger',
                                default => 'gray',
                            }),
                        TextEntry::make('payment.uploaded_at')->label('Diunggah')->dateTime('d M Y, H:i')->placeholder('-'),
                        TextEntry::make('payment.verified_at')->label('Diverifikasi')->dateTime('d M Y, H:i')->placeholder('-'),
                        TextEntry::make('payment.verifier.name')->label('Diverifikasi Oleh')->placeholder('-'),
                        TextEntry::make('payment.admin_notes')->label('Catatan Admin')->placeholder('-')->columnSpanFull(),
                        ImageEntry::make('payment_preview')
                            ->label('Preview')
                            ->state(fn (Registration $record) => $record->payment && $record->payment->isImage()
                                ? route('admin.payment.view', $record->payment->id)
                                : null)
                            ->height(240)
                            ->visible(fn (Registration $record) => $record->payment?->isImage())
                            ->columnSpanFull(),
                        TextEntry::make('payment_link')
                            ->label('File Bukti')
                            ->state('Buka file bukti pembayaran →')
                            ->url(fn (Registration $record) => $record->payment ? route('admin.payment.view', $record->payment->id) : null)
                            ->openUrlInNewTab()
                            ->visible(fn (Registration $record) => $record->payment && ! $record->payment->isImage())
                            ->columnSpanFull(),
                    ])
                    ->visible(fn (Registration $record) => $record->payment !== null),

                Section::make('Submission Karya')
                    ->columns(2)
                    ->components([
                        TextEntry::make('submission.project_title')->label('Judul Proyek'),
                        TextEntry::make('submission.submitted_at')->label('Dikumpulkan')->dateTime('d M Y, H:i')->placeholder('-'),
                        TextEntry::make('submission.github_url')->label('GitHub')->url(fn (Registration $record) => $record->submission?->github_url)->openUrlInNewTab(),
                        TextEntry::make('submission.drive_url')->label('Drive')->url(fn (Registration $record) => $record->submission?->drive_url)->openUrlInNewTab(),
                        TextEntry::make('submission.description')->label('Deskripsi')->columnSpanFull(),
                    ])
                    ->visible(fn (Registration $record) => $record->submission !== null),
            ]);
    }
}
