<?php

namespace App\Filament\Widgets;

use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\Concerns\InteractsWithPageTable;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use App\Filament\Resources\TransaksiResource\Pages\ListTransaksis;

class SaldoOverview extends BaseWidget
{
    use InteractsWithPageTable;

    protected function getTablePage(): string
    {
        return ListTransaksis::class;
    }
    protected function getStats(): array
    {
        // Set start to the 9th of the current month
        $startOfToday = Carbon::now()->startOfMonth()->addDays(8); // 9th day of the current month

        // Set end to the 9th of the next month
        $endOfToday = Carbon::now()->addMonth()->startOfMonth()->addDays(8)->endOfDay(); // 9th day of the next month

        return [
            Stat::make('Saldo Akhir', 'Rp ' . number_format(
                $this->getPageTableQuery()->where('jenis_transaksi', 'Pemasukan')->sum('saldo')
                    - $this->getPageTableQuery()->where('jenis_transaksi', 'Pengeluaran')->sum('saldo'),
                2,
                ',',
                '.'
            )),

            Stat::make('Total Pemasukan', 'Rp ' . number_format(
                $this->getPageTableQuery()->where('jenis_transaksi', 'Pemasukan')
                    ->whereBetween('tgl_transaksi', [$startOfToday, $endOfToday])
                    ->sum('saldo'),
                2,
                ',',
                '.'
            )),

            Stat::make('Total Pengeluaran', 'Rp ' . number_format(
                $this->getPageTableQuery()->where('jenis_transaksi', 'Pengeluaran')
                    ->whereBetween('tgl_transaksi', [$startOfToday, $endOfToday])
                    ->sum('saldo'),
                2,
                ',',
                '.'
            )),
        ];
    }
}
