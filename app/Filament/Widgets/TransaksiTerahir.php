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
                Tables\Filters\Filter::make('tgl_tansaksi')
                    ->form([
                        Forms\Components\DatePicker::make('tgl_transaksi_mulai')
                            ->placeholder(fn($state): string => 'Dec 18, ' . now()->subYear()->format('Y'))
                            ->default(function (): string {
                                return Carbon::create(Carbon::now()->year, Carbon::now()->month, 9);
                            }),
                        Forms\Components\DatePicker::make('tgl_transaksi_selesai')
                            ->placeholder(fn($state): string => now()->format('M d, Y'))
                            ->default(function (): string {
                                return Carbon::create(Carbon::now()->year, Carbon::now()->month + 1, 9);
                            }),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['tgl_transaksi_mulai'] ?? null,
                                fn(Builder $query, $date): Builder => $query->whereDate('tgl_transaksi', '>=', $date),
                            )
                            ->when(
                                $data['tgl_transaksi_selesai'] ?? null,
                                fn(Builder $query, $date): Builder => $query->whereDate('tgl_transaksi', '<=', $date),
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if ($data['tgl_transaksi_mulai'] ?? null) {
                            $indicators['tgl_transaksi_mulai'] = 'Tgl. transaksi mulai ' . Carbon::parse($data['tgl_transaksi_mulai'])->toFormattedDateString();
                        }
                        if ($data['tgl_transaksi_selesai'] ?? null) {
                            $indicators['tgl_transaksi_selesai'] = 'Tgl. transaksi selesai ' . Carbon::parse($data['tgl_transaksi_selesai'])->toFormattedDateString();
                        }

                        return $indicators;
                    }),
            ])
            ->groups([
                Tables\Grouping\Group::make('tgl_transaksi')
                    ->label('Tgl. Transaksi')
                    ->date()
                    ->collapsible(),
            ]);
    }
}
