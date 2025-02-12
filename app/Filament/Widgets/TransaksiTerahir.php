<?php

namespace App\Filament\Widgets;

use Carbon\Carbon;
use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\TransaksiResource;
use Filament\Widgets\TableWidget as BaseWidget;
use Malzariey\FilamentDaterangepickerFilter\Filters\DateRangeFilter;

class TransaksiTerahir extends BaseWidget
{
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(TransaksiResource::getEloquentQuery())
            ->defaultPaginationPageOption(5)
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('jenis_transaksi')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'Pemasukan' => 'success',
                        'Pengeluaran' => 'danger',
                        'Pendapatan' => 'info',
                    })
                    ->searchable(),
                Tables\Columns\TextColumn::make('catatan')
                    ->searchable(),
                Tables\Columns\TextColumn::make('tgl_transaksi')
                    ->label('Tanggal Transaksi')
                    ->date()
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('saldo')
                    ->money('IDR', locale: 'id')
                    ->color(fn($record) => $record->jenis_transaksi === 'Pemasukan' ? 'success' : 'danger')
                    ->prefix(fn($record) => $record->jenis_transaksi === 'Pemasukan' ? '' : '-')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                DateRangeFilter::make('tgl_transaksi')
                    ->startDate(Carbon::create(Carbon::now()->year, Carbon::now()->month, 9))
                    ->endDate(Carbon::create(Carbon::now()->year, Carbon::now()->month + 1, 9)),
            ])
            ->groups([
                Tables\Grouping\Group::make('tgl_transaksi')
                    ->label('Tgl. Transaksi')
                    ->date()
                    ->collapsible(),
            ]);
    }
}
