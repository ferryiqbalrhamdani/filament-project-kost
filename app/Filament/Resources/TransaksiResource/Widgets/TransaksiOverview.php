<?php

namespace App\Filament\Resources\TransaksiResource\Widgets;

use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\Concerns\InteractsWithPageTable;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use App\Filament\Resources\TransaksiResource\Pages\ListTransaksis;
use App\Models\Transaksi;

class TransaksiOverview extends BaseWidget
{
    use InteractsWithPageTable;

    protected function getTablePage(): string
    {
        return ListTransaksis::class;
    }
    protected function getStats(): array
    {
        return [
            Stat::make('Saldo Akhir', 'Rp ' . number_format(Transaksi::where('jenis_transaksi', 'Pemasukan')->sum('saldo') - Transaksi::where('jenis_transaksi', 'Pengeluaran')->sum('saldo') - Transaksi::where('jenis_transaksi', 'Pendapatan')->sum('saldo'), 2, ',', '.')),
            Stat::make('Total Pemasukan', 'Rp ' . number_format($this->getPageTableQuery()->where('jenis_transaksi', 'Pemasukan')->sum('saldo'), 2, ',', '.')),
            Stat::make('Total Pengeluaran', 'Rp ' . number_format($this->getPageTableQuery()->where('jenis_transaksi', 'Pengeluaran')->sum('saldo'), 2, ',', '.')),
            Stat::make('Total Pendapatan', 'Rp ' . number_format($this->getPageTableQuery()->where('jenis_transaksi', 'Pendapatan')->where('status_kamar', 'Kamar Atas')->sum('saldo') + $this->getPageTableQuery()->where('jenis_transaksi', 'Pendapatan')->where('status_kamar', 'Kamar Bawah')->sum('saldo'), 2, ',', '.')),
            Stat::make('Pendapatan Kamar Atas', 'Rp ' . number_format($this->getPageTableQuery()->where('jenis_transaksi', 'Pendapatan')->where('status_kamar', 'Kamar Atas')->sum('saldo'), 2, ',', '.')),
            Stat::make('Pendapatan Kamar Bawah', 'Rp ' . number_format($this->getPageTableQuery()->where('jenis_transaksi', 'Pendapatan')->where('status_kamar', 'Kamar Bawah')->sum('saldo'), 2, ',', '.')),
        ];
    }
}
