<?php

namespace App\Filament\Widgets;

use App\Models\Registration;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class RegistrationStatsOverview extends StatsOverviewWidget
{
    protected static bool $isLazy = false;

    protected int|array|null $columns = 5;

    protected function getStats(): array
    {
        return [
            Stat::make('Total Tim Terdaftar', Registration::count())
                ->color('gray'),

            Stat::make('Belum Bayar', Registration::where('status', 'pending_payment')->count())
                ->color('warning'),

            Stat::make('Menunggu Verifikasi', Registration::where('status', 'pending_verification')->count())
                ->color('info'),

            Stat::make('Terverifikasi', Registration::where('status', 'verified')->count())
                ->color('success'),

            Stat::make('Ditolak', Registration::where('status', 'rejected')->count())
                ->color('danger'),
        ];
    }
}
