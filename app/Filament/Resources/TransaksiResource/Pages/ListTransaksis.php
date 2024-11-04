<?php

namespace App\Filament\Resources\TransaksiResource\Pages;

use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\TransaksiResource;
use App\Models\Transaksi;
use Filament\Pages\Concerns\ExposesTableToWidgets;

class ListTransaksis extends ListRecords
{
    use ExposesTableToWidgets;

    protected static string $resource = TransaksiResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Buat Transaksi Baru'),
        ];
    }

    public function getTabs(): array
    {
        return [
            'semua data' => Tab::make()
                ->modifyQueryUsing(fn(Builder $query) => $query)
                ->badge(count(Transaksi::get())),
            'pemasukan' => Tab::make()
                ->modifyQueryUsing(fn(Builder $query) => $query->where('jenis_transaksi', 'Pemasukan'))
                ->badge(count(Transaksi::where('jenis_transaksi', 'Pemasukan')->get())),
            'pengeluaran' => Tab::make()
                ->modifyQueryUsing(fn(Builder $query) => $query->where('jenis_transaksi', 'Pengeluaran'))
                ->badge(count(Transaksi::where('jenis_transaksi', 'Pengeluaran')->get())),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return TransaksiResource::getWidgets();
    }
}
