<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PembayaranResource\Pages;
use App\Models\Pembayaran;
use App\Models\Pemesanan;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\Wizard;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Section;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Support\HtmlString;

class PembayaranResource extends Resource
{
    protected static ?string $model = Pembayaran::class;

    protected static ?string $navigationIcon = 'heroicon-o-credit-card';
    protected static ?string $navigationGroup = 'Transaksi';
    protected static ?string $modelLabel       = 'Pembayaran';
    protected static ?string $pluralModelLabel = 'Pembayaran';
    protected static ?string $navigationLabel  = 'Pembayaran';
    protected static ?string $slug             = 'pembayaran';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Wizard::make([
                    // ─────────────────────────────────────────────
                    // STEP 1 : DATA PEMBAYARAN
                    // ─────────────────────────────────────────────
                    Wizard\Step::make('Data Pembayaran')
                        ->icon('heroicon-m-credit-card')
                        ->schema([
                            Section::make('Informasi Pembayaran')
                                ->columns(2)
                                ->schema([
                                    TextInput::make('id_pembayaran')
                                        ->label('No Pembayaran')
                                        ->default(fn () => Pembayaran::getKodePembayaran())
                                        ->required()
                                        ->readonly(),

                                    DateTimePicker::make('tanggal_pembayaran')
                                        ->label('Tanggal Pembayaran')
                                        ->default(now('Asia/Jakarta'))
                                        ->timezone('Asia/Jakarta')
                                        ->required(),

                                    Select::make('id_pemesanan')
                                        ->label('Pilih Pesanan')
                                        // Mengambil ID dari URL jika diarahkan dari tombol "Bayar" di Pemesanan
                                        ->default(fn () => request()->query('id_pemesanan'))
                                        ->options(function () {
                                            return Pemesanan::query()
                                                ->where('status_pemesanan', 'belumdibayar')
                                                ->whereDoesntHave('pembayaran')
                                                ->get()
                                                ->mapWithKeys(function ($pesanan) {
                                                    return [
                                                        $pesanan->id => "{$pesanan->id_pesanan} - {$pesanan->pelanggan?->nama_pelanggan}"
                                                    ];
                                                });
                                        })
                                        ->searchable()
                                        ->required()
                                        ->reactive()
                                        ->afterStateUpdated(function ($state, Set $set) {
                                            if ($state) {
                                                $pesanan = Pemesanan::find($state);
                                                if ($pesanan) {
                                                    // Otomatis mengisi total bayar dari subtotal pesanan
                                                    $set('total_bayar', $pesanan->subtotal);
                                                }
                                            }
                                        }),

                                    Select::make('metode_pembayaran')
                                        ->label('Metode Pembayaran')
                                        ->options([
                                            'cash' => 'Tunai / Cash',
                                            'transfer' => 'Transfer Bank',
                                            'qris' => 'QRIS',
                                        ])
                                        ->required(),
                                ]),
                        ]),

                    // ─────────────────────────────────────────────
                    // STEP 2 : NOMINAL & KONFIRMASI
                    // ─────────────────────────────────────────────
                    Wizard\Step::make('Nominal')
                        ->icon('heroicon-m-banknotes')
                        ->schema([
                            Section::make('Rincian Nominal')
                                ->columns(2)
                                ->schema([
                                    TextInput::make('total_bayar')
                                        ->label('Total yang Harus Dibayar')
                                        ->numeric()
                                        ->prefix('Rp')
                                        ->readonly()
                                        ->required(),

                                    TextInput::make('nominal_masuk')
                                        ->label('Uang Diterima')
                                        ->numeric()
                                        ->prefix('Rp')
                                        ->required()
                                        ->reactive()
                                        ->afterStateUpdated(function ($state, Get $get, Set $set) {
                                            $total = (float) $get('total_bayar');
                                            $masuk = (float) $state;
                                            $set('kembalian', $masuk - $total);
                                        }),

                                    TextInput::make('kembalian')
                                        ->label('Kembalian')
                                        ->numeric()
                                        ->prefix('Rp')
                                        ->readonly()
                                        ->default(0),
                                ]),
                        ]),
                ])
                ->columnSpanFull()
                // Tombol submit akhir
                ->submitAction(new HtmlString('<button type="submit" class="fi-btn fi-btn-size-md fi-btn-color-primary">Simpan Pembayaran</button>')),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id_pembayaran')
                    ->label('No Bayar')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('pemesanan.id_pesanan')
                    ->label('No Pesanan')
                    ->searchable(),

                TextColumn::make('total_pembayaran')
                    ->label('Total')
                    ->formatStateUsing(fn ($state) => 'Rp ' . number_format($state, 0, ',', '.'))
                    ->sortable(),

                TextColumn::make('metode_pembayaran')
                    ->label('Metode')
                    ->badge()
                    ->color('info'),

                TextColumn::make('tanggal_pembayaran')
                    ->label('Waktu Bayar')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('metode_pembayaran')
                    ->options([
                        'cash' => 'Cash',
                        'transfer' => 'Transfer',
                        'qris' => 'QRIS',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListPembayarans::route('/'),
            'create' => Pages\CreatePembayaran::route('/create'),
            'edit'   => Pages\EditPembayaran::route('/{record}/edit'),
        ];
    }
}