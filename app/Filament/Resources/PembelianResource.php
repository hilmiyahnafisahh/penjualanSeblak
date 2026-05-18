<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PembelianResource\Pages;
use App\Models\Pembelian;
use App\Models\Barang;
use App\Models\Karyawan;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\Wizard;
use Filament\Forms\Components\Wizard\Step;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Actions\Action as TableAction;
use Barryvdh\DomPDF\Facade\Pdf;

class PembelianResource extends Resource
{
    protected static ?string $model = Pembelian::class;
    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';
    protected static ?string $navigationLabel = 'Pembelian';
    protected static ?string $navigationGroup = 'Transaksi';
    protected static ?string $maxContentWidth = 'full';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Wizard::make([
                    Step::make('Data Pembelian')
                        ->icon('heroicon-m-document-text')
                        ->schema([
                            Section::make('Informasi Utama')
                                ->schema([
                                    TextInput::make('id_pembelian')
                                        ->default(fn () => Pembelian::getKodeFakturBeli())
                                        ->required()
                                        ->readonly(),

                                    DateTimePicker::make('tgl')
                                        ->label('Tanggal Transaksi')
                                        ->default(now())
                                        ->required(),

                                    Select::make('karyawan_id')
                                        ->label('Pilih Karyawan')
                                        ->options(Karyawan::all()->pluck('nama', 'id'))
                                        ->searchable()
                                        ->preload()
                                        ->required(),

                                    Hidden::make('total_bayar')->default(0),
                                    Hidden::make('tagihan')->default(0),
                                    Hidden::make('status')->default('pending'),
                                ])->columns(3),
                        ]),

                    Step::make('Item Barang')
                        ->icon('heroicon-m-cube')
                        ->schema([
                            Repeater::make('barang')
                                ->relationship()
                                ->minItems(1)
                                ->schema([
                                    Select::make('id_barang')
                                        ->label('Barang')
                                        ->options(Barang::pluck('nama_barang', 'id_barang'))
                                        ->required()
                                        ->searchable()
                                        ->live()
                                        ->afterStateUpdated(function ($state, Forms\Set $set) {
                                            $barang = Barang::where('id_barang', $state)->first();
                                            if ($barang) {
                                                $set('harga_beli', $barang->harga_beli);
                                            }
                                        }),
                                    TextInput::make('harga_beli')->numeric()->prefix('Rp')->required(),
                                    TextInput::make('jumlah')->numeric()->default(1)->required(),
                                    Hidden::make('tgl')->default(fn (Forms\Get $get) => $get('../../tgl') ?? now()),
                                ])
                                ->columns(3),

                            Forms\Components\Actions::make([
                                Action::make('konfirmasi')
                                    ->label('Konfirmasi Pembayaran')
                                    ->color('success')
                                    ->icon('heroicon-m-check-circle')
                                    ->form([
                                        Select::make('status_bayar')
                                            ->options(['lunas' => 'Lunas', 'hutang' => 'Hutang'])
                                            ->required(),
                                    ])
                                    ->action(function ($get, $set, $data) {
                                        $items = $get('barang') ?? [];
                                        $total = collect($items)->sum(fn($i) => floatval($i['harga_beli']) * floatval($i['jumlah']));

                                        $set('status', $data['status_bayar']);

                                        // ✅ total_bayar selalu diisi total belanja
                                        $set('total_bayar', $total);

                                        // ✅ tagihan diisi jika hutang
                                        $set('tagihan', $data['status_bayar'] === 'hutang' ? $total : 0);

                                        $set('pembayaran', [[
                                            'tgl_bayar'        => $get('tgl') ?? now(),
                                            'jenis_pembayaran' => 'cash',
                                            'nama_vendor'      => '-',
                                            'jumlah_bayar'     => $data['status_bayar'] === 'lunas' ? $total : 0,
                                            'sisa_tagihan'     => $data['status_bayar'] === 'hutang' ? $total : 0,
                                        ]]);
                                    }),
                            ]),
                        ]),

                    Step::make('Pembayaran')
                        ->icon('heroicon-m-credit-card')
                        ->schema([
                            Repeater::make('pembayaran')
                                ->relationship()
                                ->schema([
                                    DatePicker::make('tgl_bayar')
                                        ->required(),

                                    Select::make('jenis_pembayaran')
                                        ->options(['cash' => 'Cash', 'transfer' => 'Transfer'])
                                        ->required(),

                                    TextInput::make('nama_vendor')
                                        ->default('-')
                                        ->required(),

                                    TextInput::make('jumlah_bayar')
                                        ->numeric()
                                        ->prefix('Rp')
                                        ->live()
                                        ->afterStateUpdated(function ($state, Forms\Set $set, Forms\Get $get) {
                                            $tagihan = floatval($get('../../tagihan') ?? 0);
                                            $bayar   = floatval($state ?? 0);

                                            if ($bayar > $tagihan) {
                                                $bayar = $tagihan;
                                                $set('jumlah_bayar', $bayar);
                                            }

                                            $set('sisa_tagihan', max(0, $tagihan - $bayar));
                                        }),

                                    TextInput::make('sisa_tagihan')
                                        ->numeric()
                                        ->prefix('Rp')
                                        ->readOnly(),
                                ])
                                ->columns(3)
                                ->addable(false)
                                ->deletable(false),
                        ]),

                    Step::make('Selesai')
                        ->icon('heroicon-m-check-circle')
                        ->schema([
                            Forms\Components\Placeholder::make('final')
                                ->content('Klik "Create" untuk simpan.'),
                        ]),
                ])
                ->columnSpanFull()
                ->skippable(false),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id_pembelian')
                    ->label('Faktur')
                    ->searchable(),

                TextColumn::make('karyawan.nama')
                    ->label('Karyawan')
                    ->placeholder('Tidak ada karyawan')
                    ->searchable(),

                BadgeColumn::make('status')
                    ->colors([
                        'success' => 'lunas',
                        'warning' => 'hutang',
                    ]),

                TextColumn::make('total_bayar')
                    ->money('IDR')
                    ->label('Total Bayar'),

                TextColumn::make('pembayaran.sisa_tagihan')
                    ->label('Sisa Tagihan')
                    ->money('IDR')
                    ->placeholder('0')
                    ->color(fn ($record) => $record->status === 'hutang' ? 'danger' : 'success'),

                TextColumn::make('pembayaran.nama_vendor')
                    ->label('Vendor')
                    ->placeholder('-')
                    ->searchable(),

                TextColumn::make('tgl')
                    ->label('Tgl')
                    ->dateTime('M d, Y H:i:s'),
            ])
            ->filters([])
            ->headerActions([
                TableAction::make('downloadPdf')
                    ->label('Unduh PDF')
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('success')
                    ->action(function () {
                        $pembelian = Pembelian::with(['barang', 'pembayaran', 'karyawan'])->get();
                        $pdf = Pdf::loadView('pdf.pembelian', ['pembelian' => $pembelian]);

                        return response()->streamDownload(
                            fn () => print($pdf->output()),
                            'pembelian.pdf'
                        );
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListPembelians::route('/'),
            'create' => Pages\CreatePembelian::route('/create'),
            'edit'   => Pages\EditPembelian::route('/{record}/edit'),
        ];
    }
}