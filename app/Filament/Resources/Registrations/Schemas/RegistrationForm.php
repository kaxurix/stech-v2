<?php

namespace App\Filament\Resources\Registrations\Schemas;

use App\Filament\Resources\Registrations\Tables\RegistrationsTable;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class RegistrationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Data Tim')
                    ->columns(2)
                    ->components([
                        TextInput::make('team_name')
                            ->label('Nama Tim')
                            ->required()
                            ->maxLength(255),

                        Select::make('category')
                            ->label('Kategori')
                            ->options(RegistrationsTable::CATEGORY_LABELS)
                            ->required(),

                        TextInput::make('institution')
                            ->label('Institusi')
                            ->required()
                            ->maxLength(255),

                        TextInput::make('phone')
                            ->label('No. HP Ketua Tim')
                            ->tel()
                            ->required()
                            ->maxLength(255),

                        TextInput::make('member_count')
                            ->label('Jumlah Anggota')
                            ->numeric()
                            ->required(),

                        Toggle::make('is_finalist')
                            ->label('Finalis')
                            ->helperText('Hanya berlaku untuk tim yang sudah terverifikasi.'),
                    ]),
            ]);
    }
}
