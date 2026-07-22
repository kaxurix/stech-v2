<?php

namespace App\Filament\Resources\Registrations\Pages;

use App\Filament\Resources\Registrations\RegistrationResource;
use App\Models\Registration;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;

class ListRegistrations extends ListRecords
{
    protected static string $resource = RegistrationResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('Semua')
                ->badge(Registration::count()),

            'pending_payment' => Tab::make('Belum Bayar')
                ->modifyQueryUsing(fn ($query) => $query->where('status', 'pending_payment'))
                ->badge(Registration::where('status', 'pending_payment')->count()),

            'pending_verification' => Tab::make('Menunggu Verifikasi')
                ->modifyQueryUsing(fn ($query) => $query->where('status', 'pending_verification'))
                ->badge(Registration::where('status', 'pending_verification')->count()),

            'verified' => Tab::make('Terverifikasi')
                ->modifyQueryUsing(fn ($query) => $query->where('status', 'verified'))
                ->badge(Registration::where('status', 'verified')->count()),

            'rejected' => Tab::make('Ditolak')
                ->modifyQueryUsing(fn ($query) => $query->where('status', 'rejected'))
                ->badge(Registration::where('status', 'rejected')->count()),
        ];
    }
}
