<?php

namespace App\Filament\Resources\Submissions\Schemas;

use App\Filament\Resources\Registrations\Tables\RegistrationsTable;
use App\Models\Submission;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class SubmissionInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Tim')
                    ->columns(3)
                    ->components([
                        TextEntry::make('registration.team_name')->label('Nama Tim'),
                        TextEntry::make('registration.institution')->label('Institusi'),
                        TextEntry::make('registration.category')
                            ->label('Kategori')
                            ->formatStateUsing(fn (?string $state) => RegistrationsTable::CATEGORY_LABELS[$state] ?? $state),
                        TextEntry::make('registration.user.name')->label('Ketua Tim'),
                        TextEntry::make('registration.user.email')->label('Email'),
                    ]),

                Section::make('Submission Karya')
                    ->columns(2)
                    ->components([
                        TextEntry::make('project_title')->label('Judul Proyek')->placeholder('-')->columnSpanFull(),
                        TextEntry::make('github_url')
                            ->label('GitHub')
                            ->url(fn (Submission $record) => $record->github_url ?: null)
                            ->openUrlInNewTab()
                            ->placeholder('-'),
                        TextEntry::make('drive_url')
                            ->label('Drive')
                            ->url(fn (Submission $record) => $record->drive_url ?: null)
                            ->openUrlInNewTab()
                            ->placeholder('-'),
                        TextEntry::make('description')->label('Deskripsi')->placeholder('-')->columnSpanFull(),
                        TextEntry::make('created_at')->label('Dikumpulkan')->dateTime('d M Y, H:i'),
                        TextEntry::make('updated_at')->label('Terakhir Diubah')->dateTime('d M Y, H:i'),
                    ]),
            ]);
    }
}
