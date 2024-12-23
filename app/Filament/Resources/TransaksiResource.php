<?php

namespace App\Filament\Resources;

use Carbon\Carbon;
use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use App\Models\Transaksi;
use Filament\Tables\Table;
use Filament\Support\RawJs;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Illuminate\Database\Eloquent\Builder;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Tables\Columns\Summarizers\Sum;
use App\Filament\Resources\TransaksiResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\TransaksiResource\RelationManagers;
use App\Filament\Resources\TransaksiResource\Widgets\TransaksiOverview;

class TransaksiResource extends Resource
{
    protected static ?string $model = Transaksi::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('jenis_transaksi')
                    ->options([
                        'Pemasukan' => 'Pemasukan',
                        'Pengeluaran' => 'Pengeluaran',
                    ])
                    ->default('Pengeluaran')
                    ->required(),
                Forms\Components\TextInput::make('saldo')
                    ->label('Total transaksi')
                    ->mask(RawJs::make('$money($input)'))
                    ->stripCharacters(',')
                    ->numeric(),
                Forms\Components\DatePicker::make('tgl_transaksi')
                    ->required(),
                Forms\Components\Textarea::make('catatan')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('jenis_transaksi')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'Pemasukan' => 'success',
                        'Pengeluaran' => 'danger',
                    })
                    ->searchable(),
                Tables\Columns\TextColumn::make('catatan')
                    ->searchable()
                    ->description(fn(Transaksi $record): string => $record->catatan === 'Sewa kost' ? $record->pembayaran->sewaKost->status_kamar : ''),
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
            ->defaultSort('created_at', 'desc')
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

                Tables\Filters\Filter::make('status_kamar')
                    ->form([
                        Forms\Components\Select::make('status_kamar')
                            ->options([
                                'Kamar Atas' => 'Kamar Atas',
                                'Kamar Bawah' => 'Kamar Bawah',
                            ]),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['status_kamar'] ?? null,
                                fn(Builder $query, $statusKamar): Builder => $query->whereHas('pembayaran', fn(Builder $query) => $query->whereHas('sewaKost', fn(Builder $query) => $query->where('status_kamar', $statusKamar))),
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if ($data['status_kamar'] ?? null) {
                            $indicators['status_kamar'] = 'Status Kamar: ' . $data['status_kamar'];
                        }


                        return $indicators;
                    }),
            ])
            ->recordAction(null)
            ->recordUrl(null)
            ->groups([
                Tables\Grouping\Group::make('tgl_transaksi')
                    ->label('Tgl. Transaksi')
                    ->date()
                    ->collapsible(),
                Tables\Grouping\Group::make('pembayaran.sewaKost.status_kamar')
                    ->collapsible(),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->visible(fn($record) => $record->pembayaran_id === null),

                Tables\Actions\DeleteAction::make()
                    ->visible(fn($record) => $record->pembayaran_id === null),

                Tables\Actions\ViewAction::make()
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTransaksis::route('/'),
            'create' => Pages\CreateTransaksi::route('/create'),
            'edit' => Pages\EditTransaksi::route('/{record}/edit'),
        ];
    }

    public static function getWidgets(): array
    {
        return [
            TransaksiOverview::class,
        ];
    }


    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('')
                    ->schema([
                        TextEntry::make('jenis_transaksi')
                            ->badge()
                            ->color(fn(string $state): string => match ($state) {
                                'Pemasukan' => 'success',
                                'Pengeluaran' => 'danger',
                            }),
                        TextEntry::make('saldo')
                            ->label('Total transaksi')
                            ->money('IDR', locale: 'id'),
                        TextEntry::make('tgl_transaksi')
                            ->label('Tgl. Transaksi')
                            ->date(),
                        TextEntry::make('catatan')
                            ->limit(50)
                            ->tooltip(function (TextEntry $component): ?string {
                                $state = $component->getState();

                                if (strlen($state) <= $component->getCharacterLimit()) {
                                    return null;
                                }

                                // Only render the tooltip if the entry contents exceeds the length limit.
                                return $state;
                            })
                    ])
                    ->columns([
                        'xl' => 3,
                        '2xl' => 3,
                    ]),
                Section::make('Informasi Kost')
                    ->schema([
                        TextEntry::make('pembayaran.sewaKost.nama_penyewa')
                            ->label('Penyewa'),
                        TextEntry::make('pembayaran.sewaKost.cabangKost.nama_kost')
                            ->label('Cabang Kost'),
                        TextEntry::make('pembayaran.sewaKost.status_kamar')
                            ->label('Status Kamar'),
                        TextEntry::make('pembayaran.sewaKost.biayaKost.tipe')
                            ->badge()
                            ->label('Tipe Sewa'),
                        TextEntry::make('pembayaran.sewaKost.tgl_sewa')
                            ->label('Check In')
                            ->date(),
                        TextEntry::make('pembayaran.sewaKost.tgl_sewa_akhir')
                            ->label('Check Out')
                            ->date(),
                        TextEntry::make('pembayaran.sewaKost.lama_sewa')
                            ->label('Lama Sewa'),

                    ])
                    ->columns([
                        'xl' => 3,
                        '2xl' => 3,
                    ])
                    ->visible(fn(Transaksi $record) => $record->pembayaran !== null),


            ]);
    }
}
