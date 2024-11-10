<?php

namespace App\Filament\Resources\SewaKostResource\RelationManagers;

use Filament\Forms;
use Filament\Tables;
use App\Models\AkunBank;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Support\RawJs;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Columns\Summarizers\Sum;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Resources\RelationManagers\RelationManager;

class PembayaranRelationManager extends RelationManager
{
    protected static string $relationship = 'pembayaran';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('metode_pembayaran_id')
                    ->relationship('metodePembayaran', 'nama')
                    ->reactive()
                    ->required(),


                Forms\Components\Select::make('akun_bank_id')
                    ->relationship('akunBank', 'nama_bank')
                    ->reactive()
                    ->required()
                    ->visible(fn(callable $get) => $get('metode_pembayaran_id') == 2)
                    ->afterStateUpdated(function (callable $set, $state) {
                        // Fetch the selected bank account's `nomor_rekening` and set it in the placeholder
                        $nomorRekening = AkunBank::find($state)?->nomor_rekening;
                        $set('nomor_rekening_placeholder', $nomorRekening);
                    }),

                Forms\Components\Placeholder::make('nomor_rekening')
                    ->label('Nomor Rekening')
                    ->content(fn(callable $get) => $get('nomor_rekening_placeholder') ?? 'No rekening tidak ada')
                    ->visible(fn(callable $get) => $get('metode_pembayaran_id') == 2),

                Forms\Components\TextInput::make('total_bayar')
                    ->required()
                    ->mask(RawJs::make('$money($input)'))
                    ->stripCharacters(',')
                    ->numeric(),

                Forms\Components\Select::make('status')
                    ->options([
                        'Lunas' => 'Lunas',
                        'Belum Lunas' => 'Belum Lunas',
                    ])
                    ->default($this->getOwnerRecord()->status),
                Forms\Components\DatePicker::make('tgl_tansaksi')
                    ->required(),

                Forms\Components\Textarea::make('catatan')
                    ->rows(7)
                    ->cols(7)
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('total_bayar')
            ->columns([
                Tables\Columns\TextColumn::make('metodePembayaran.nama'),
                Tables\Columns\TextColumn::make('akunBank.nama_bank'),
                Tables\Columns\TextColumn::make('akunBank.nomor_rekening'),
                Tables\Columns\TextColumn::make('catatan'),
                Tables\Columns\TextColumn::make('total_bayar')
                    ->money('IDR', locale: 'id')
                    ->sortable()
                    ->summarize(
                        Sum::make()
                            ->money('IDR', locale: 'id')
                    ),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Buat Pembayaran')
                    ->after(function ($data, $record) {
                        $status = $data['status'];

                        $record->transaksi()->create([
                            'pembayaran_id' => $record->id,
                            'jenis_transaksi' => 'Pemasukan',
                            'saldo' => $record->total_bayar,
                            'catatan' => 'Sewa kost',
                        ]);

                        $this->getOwnerRecord()->update([
                            'status' => $status
                        ]);
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
}
