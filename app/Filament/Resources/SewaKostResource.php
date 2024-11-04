<?php

namespace App\Filament\Resources;

use Carbon\Carbon;
use Filament\Forms;
use Filament\Tables;
use App\Models\SewaKost;
use Filament\Forms\Form;
use App\Models\BiayaKost;
use Filament\Tables\Table;
use Filament\Support\RawJs;
use Filament\Resources\Resource;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Columns\Summarizers\Sum;
use App\Filament\Resources\SewaKostResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\SewaKostResource\RelationManagers;
use App\Filament\Resources\SewaKostResource\Widgets\SewaKostOverview;

class SewaKostResource extends Resource
{
    protected static ?string $model = SewaKost::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office-2';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make([
                    Forms\Components\TextInput::make('nama_penyewa')
                        ->required()
                        ->maxLength(255),

                    Forms\Components\Select::make('cabang_kost_id')
                        ->relationship('cabangKost', 'nama_kost')
                        ->required(),

                    Forms\Components\DatePicker::make('tgl_sewa')
                        ->label('Check In')
                        ->required()
                        ->reactive(),

                    Forms\Components\DatePicker::make('tgl_sewa_akhir')
                        ->label('Check Out')
                        ->required()
                        ->after('tgl_sewa')
                        ->reactive()
                        ->afterStateUpdated(function (callable $set, $get) {
                            $tglSewa = $get('tgl_sewa');
                            $tglSewaAkhir = $get('tgl_sewa_akhir');
                            $biayaKostId = $get('biaya_kost_id');

                            if ($tglSewa && $tglSewaAkhir && $biayaKostId) {
                                $biayaKost = BiayaKost::find($biayaKostId);
                                $lamaIzin = static::hitungLamaIzin($tglSewa, $tglSewaAkhir, $biayaKost->tipe);
                                $set('lama_sewa', $lamaIzin);
                                $totalBiaya = static::hitungTotalBiaya($lamaIzin, $biayaKost->harga_sewa, $biayaKost->tipe);
                                $set('total_biaya', $totalBiaya);
                            }
                        }),

                    Forms\Components\Select::make('biaya_kost_id')
                        ->label('Tipe Sewa')
                        ->relationship('biayaKost', 'tipe')
                        ->required()
                        ->reactive()
                        ->afterStateUpdated(function (callable $set, $get) {
                            $tglSewa = $get('tgl_sewa');
                            $tglSewaAkhir = $get('tgl_sewa_akhir');
                            $biayaKostId = $get('biaya_kost_id');

                            if ($tglSewa && $tglSewaAkhir && $biayaKostId) {
                                $biayaKost = BiayaKost::find($biayaKostId);
                                $lamaIzin = static::hitungLamaIzin($tglSewa, $tglSewaAkhir, $biayaKost->tipe);
                                $set('lama_sewa', $lamaIzin);
                                $totalBiaya = static::hitungTotalBiaya($lamaIzin, $biayaKost->harga_sewa, $biayaKost->tipe);
                                $set('total_biaya', $totalBiaya);
                            }
                        }),

                    Forms\Components\TextInput::make('lama_sewa')
                        ->label('Lama Sewa')
                        ->readOnly()
                        ->hint('Lama izin akan dihitung otomatis berdasarkan tipe sewa dan tanggal')
                        ->default(''),

                    Forms\Components\TextInput::make('total_biaya')
                        ->label('Total Biaya')
                        ->mask(RawJs::make('$money($input)'))
                        ->stripCharacters(',')
                        ->numeric()
                        ->readOnly(),
                ])
                    ->columns(2),
            ]);
    }

    /**
     * Fungsi untuk menghitung lama izin berdasarkan tipe sewa.
     */
    protected static function hitungLamaIzin($tglSewa, $tglSewaAkhir, $tipe)
    {
        $startDate = Carbon::parse($tglSewa);
        $endDate = Carbon::parse($tglSewaAkhir);

        $diffInDays = $startDate->diffInDays($endDate);

        return match ($tipe) {
            'harian' => "{$diffInDays} hari",
            'mingguan' => ceil($diffInDays / 7) . " minggu",
            'bulanan' => ceil($diffInDays / 30) . " bulan",
            default => 'Tidak diketahui',
        };
    }

    /**
     * Fungsi untuk menghitung total biaya berdasarkan lama izin dan harga sewa.
     */
    protected static function hitungTotalBiaya($lamaIzin, $hargaSewa, $tipe)
    {
        // Ambil nilai numerik dari lama izin
        preg_match('/\d+/', $lamaIzin, $matches);
        $lama = $matches[0] ?? 0;

        return match ($tipe) {
            'harian' => $lama * $hargaSewa,
            'mingguan' => $lama * $hargaSewa,
            'bulanan' => $lama * $hargaSewa,
            default => 0,
        };
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('cabangKost.nama_kost')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('biayaKost.tipe')
                    ->label('Tipe Sewa')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('nama_penyewa')
                    ->searchable(),
                Tables\Columns\TextColumn::make('tgl_sewa')
                    ->label('Check In')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('tgl_sewa_akhir')
                    ->label('Check Out')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('lama_sewa')
                    ->searchable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'Lunas' => 'success',
                        'Belum Lunas' => 'danger',
                    })
                    ->searchable(),
                Tables\Columns\TextColumn::make('total_biaya')
                    ->money('IDR', locale: 'id')
                    ->sortable()
                    ->summarize(
                        Sum::make()
                            ->money('IDR', locale: 'id')
                    ),
                Tables\Columns\TextColumn::make('pembayaran.total_bayar')
                    ->label('Total Pembayaran')
                    ->getStateUsing(
                        fn($record): float =>
                        $record->pembayaran->sum(
                            'total_bayar'
                        )
                    )
                    ->sortable()
                    ->summarize([
                        Tables\Columns\Summarizers\Sum::make()
                            ->numeric()
                            ->money(
                                'IDR',
                                locale: 'id'
                            )
                    ])
                    ->money(
                        'IDR',
                        locale: 'id',
                    ),
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
                Tables\Filters\Filter::make('tgl_sewa')
                    ->form([
                        Forms\Components\DatePicker::make('sewa_dari')
                            ->placeholder(fn($state): string => 'Dec 18, ' . now()->subYear()->format('Y')),
                        Forms\Components\DatePicker::make('sampai_sewa')
                            ->placeholder(fn($state): string => now()->format('M d, Y')),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['sewa_dari'] ?? null,
                                fn(Builder $query, $date): Builder => $query->whereDate('tgl_sewa', '>=', $date),
                            )
                            ->when(
                                $data['sampai_sewa'] ?? null,
                                fn(Builder $query, $date): Builder => $query->whereDate('tgl_sewa', '<=', $date),
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if ($data['sewa_dari'] ?? null) {
                            $indicators['sewa_dari'] = 'Tanggal Mulai: ' . Carbon::parse($data['sewa_dari'])->toFormattedDateString();
                        }
                        if ($data['sampai_sewa'] ?? null) {
                            $indicators['sampai_sewa'] = 'Tanggal Akhir: ' . Carbon::parse($data['sampai_sewa'])->toFormattedDateString();
                        }

                        return $indicators;
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
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
            RelationManagers\PembayaranRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSewaKosts::route('/'),
            'create' => Pages\CreateSewaKost::route('/create'),
            'edit' => Pages\EditSewaKost::route('/{record}/edit'),
        ];
    }

    public static function getWidgets(): array
    {
        return [
            SewaKostOverview::class,
        ];
    }
}
