<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Registrations\Tables\RegistrationsTable;
use App\Models\Registration;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class RegistrationsTableWidget extends TableWidget
{
    protected static bool $isLazy = false;

    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return RegistrationsTable::configure($table->query(Registration::query()))
            ->heading('Peserta');
    }
}
