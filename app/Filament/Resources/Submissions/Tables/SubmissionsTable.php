<?php

namespace App\Filament\Resources\Submissions\Tables;

use App\Models\Submission;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SubmissionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with(['registration.user']))
            ->columns([
                TextColumn::make('registration.team_name')
                    ->label('Tim')
                    ->description(fn (Submission $record) => $record->registration?->user->name.' · '.$record->registration?->user->email)
                    ->searchable()
                    ->sortable()
                    ->weight('semibold'),

                TextColumn::make('project_title')
                    ->label('Judul Proyek')
                    ->searchable()
                    ->limit(40)
                    ->placeholder('-'),

                TextColumn::make('github_url')
                    ->label('GitHub')
                    ->url(fn (Submission $record) => $record->github_url ?: null)
                    ->openUrlInNewTab()
                    ->limit(30)
                    ->placeholder('-'),

                TextColumn::make('drive_url')
                    ->label('Drive')
                    ->url(fn (Submission $record) => $record->drive_url ?: null)
                    ->openUrlInNewTab()
                    ->limit(30)
                    ->placeholder('-'),

                TextColumn::make('created_at')
                    ->label('Dikumpulkan')
                    ->dateTime('d M Y, H:i')
                    ->sortable(),
            ])
            ->recordActions([
                ViewAction::make(),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
