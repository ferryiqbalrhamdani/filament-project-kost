<?php

namespace App\Filament\Resources\SewaKostResource\Widgets;

use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\Concerns\InteractsWithPageTable;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use App\Filament\Resources\SewaKostResource\Pages\ListSewaKosts;

class SewaKostOverview extends BaseWidget
{
    use InteractsWithPageTable;

    protected function getTablePage(): string
    {
        return ListSewaKosts::class;
    }
    protected function getStats(): array
    {
        return [
            Stat::make('Jumlah Sewa',  number_format($this->getPageTableQuery()->count(), 0, ',', '.')),
            Stat::make('Jumlah Lunas',  number_format($this->getPageTableQuery()->where('status', 'Lunas')->count(), 0, ',', '.')),
            Stat::make('Jumlah Belum Lunas',  number_format($this->getPageTableQuery()->where('status', 'Belum Lunas')->count(), 0, ',', '.')),
        ];
    }
}
