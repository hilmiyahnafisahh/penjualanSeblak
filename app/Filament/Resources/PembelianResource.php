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

    protected static ?string $navigationGroup = '💵 Transaksi';

    protected static ?string $maxContentWidth = 'full';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([

                Wizard::make([

                    // STEP 1
                    Step::make('Data Pembelian')
                        ->icon('heroicon-m-document-text')
                        ->schema([

                            Section::make('Informasi Utama')
                                ->schema([

                                    TextInput::make('id_pembelian')
                                        ->default(fn () => Pembelian::getKodeFakturBeli())
                                        ->required()
                                        ->readonly(),

                                    Select::make('karyawan_id')
                                        ->label('Pilih Karyawan')
                                        ->options(
                                            Karyawan::all()->pluck('nama', 'id')
                                        )
                                        ->searchable()
                                        ->preload()
                                        ->required(),

                                    Hidden::make('tgl')->default(now()),
                                    Hidden::make('total_bayar')->default(0),
                                    Hidden::make('tagihan')->default(0),
                                    Hidden::make('status')->default('pending'),

                                ])->columns(2),

                        ]),

                    // STEP 2
                    Step::make('Item Barang')
                        ->icon('heroicon-m-cube')
                        ->schema([

                            Repeater::make('barang')
                                ->relationship()
                                ->minItems(1)
                                ->schema([

                                    Select::make('id_barang')
                                        ->label('Barang')
                                        ->options(fn () => Barang::pluck('nama_barang', 'id_barang'))
                                        ->required()
                                        ->searchable()
                                        ->live()
                                        ->afterStateUpdated(function ($state, Forms\Set $set) {
                                            $barang = Barang::where('id_barang', $state)->first();
                                            if ($barang) {
                                                $set('harga_beli', $barang->harga_beli);
                                            }
                                        })
                                        ->suffixAction(
                                            Forms\Components\Actions\Action::make('tambah_barang_baru')
                                                ->icon('heroicon-m-plus-circle')
                                                ->color('success')
                                                ->label('Barang Baru')
                                                ->form([

                                                    TextInput::make('nama_barang')
                                                        ->label('Nama Barang')
                                                        ->required(),

                                                    TextInput::make('stok')
                                                        ->label('Stok')
                                                        ->numeric()
                                                        ->default(1)
                                                        ->required()
                                                        ->live()
                                                        ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                                            if ($state > 0 && $get('harga_beli') > 0) {
                                                                $set('harga_jual', hargajual($get('harga_beli'), $state));
                                                            }
                                                        }),

                                                    TextInput::make('harga_beli')
                                                        ->label('Harga Beli')
                                                        ->numeric()
                                                        ->prefix('Rp')
                                                        ->required()
                                                        ->live()
                                                        ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                                            if ($state > 0 && $get('stok') > 0) {
                                                                $set('harga_jual', hargajual($state, $get('stok')));
                                                            }
                                                        }),

                                                    TextInput::make('harga_jual')
                                                        ->label('Harga Jual')
                                                        ->numeric()
                                                        ->prefix('Rp')
                                                        ->disabled()
                                                        ->dehydrated()
                                                        ->live()
                                                        ->default(fn ($get) =>
                                                            ($get('harga_beli') > 0 && $get('stok') > 0)
                                                                ? hargajual($get('harga_beli'), $get('stok'))
                                                                : 0
                                                        ),

                                                    TextInput::make('satuan')
                                                        ->label('Satuan')
                                                        ->default('pcs'),

                                                ])
                                                ->action(function (array $data, Forms\Set $set) {
                                                    $barangBaru = Barang::create([
                                                        'id_barang'   => Barang::getKodeBarang(),
                                                        'nama_barang' => $data['nama_barang'],
                                                        'harga_beli'  => $data['harga_beli'],
                                                        'harga_jual'  => $data['harga_jual'],
                                                        'satuan'      => $data['satuan'] ?? 'pcs',
                                                        'stok'        => $data['stok'],
                                                        'gambar'      => null,
                                                    ]);

                                                    $set('id_barang', $barangBaru->id_barang);
                                                    $set('harga_beli', $barangBaru->harga_beli);
                                                })
                                        ),

                                    TextInput::make('harga_beli')
                                        ->numeric()
                                        ->prefix('Rp')
                                        ->required(),

                                    TextInput::make('jumlah')
                                        ->numeric()
                                        ->default(1)
                                        ->required(),

                                    Hidden::make('tgl')
                                        ->default(fn (Forms\Get $get) => $get('../../tgl') ?? now()),

                                ])->columns(3),

                            Forms\Components\Actions::make([

                                Action::make('konfirmasi')
                                    ->label('Konfirmasi Pembayaran')
                                    ->color('success')
                                    ->icon('heroicon-m-check-circle')
                                    ->form([
                                        Select::make('status_bayar')
                                            ->options([
                                                'lunas'  => 'Lunas',
                                                'hutang' => 'Hutang',
                                            ])
                                            ->required(),
                                    ])
                                    ->action(function ($get, $set, $data) {
                                        $items = $get('barang') ?? [];
                                        $total = collect($items)->sum(
                                            fn($i) => floatval($i['harga_beli']) * floatval($i['jumlah'])
                                        );

                                        $set('status', $data['status_bayar']);
                                        $set('total_bayar', $total);
                                        $set('tagihan', $data['status_bayar'] === 'hutang' ? $total : 0);

                                        $set('pembayaran', [[
                                            'tgl_bayar'        => now(),
                                            'jenis_pembayaran' => 'cash',
                                            'nama_vendor'      => '-',
                                            'jumlah_bayar'     => $data['status_bayar'] === 'lunas' ? $total : 0,
                                            'sisa_tagihan'     => $data['status_bayar'] === 'hutang' ? $total : 0,
                                        ]]);
                                    }),

                            ]),

                        ]),

                    // STEP 3
                    Step::make('Pembayaran')
                        ->icon('heroicon-m-credit-card')
                        ->schema([

                            Repeater::make('pembayaran')
                                ->relationship()
                                ->schema([

                                    DatePicker::make('tgl_bayar')
                                        ->required(),

                                    Select::make('jenis_pembayaran')
                                        ->options([
                                            'cash'     => 'Cash',
                                            'transfer' => 'Transfer',
                                        ])
                                        ->required(),

                                    TextInput::make('nama_vendor')
                                        ->default('-')
                                        ->required(),

                                    TextInput::make('jumlah_bayar')
                                        ->label('Jumlah Bayar')
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

                                            $sisa = max(0, $tagihan - $bayar);
                                            $set('sisa_tagihan', $sisa);

                                            if ($sisa <= 0) {
                                                $set('../../status', 'lunas');
                                            } else {
                                                $set('../../status', 'hutang');
                                            }

                                            $set('../../total_bayar', $bayar);
                                        }),

                                    TextInput::make('sisa_tagihan')
                                        ->label('Sisa Tagihan')
                                        ->numeric()
                                        ->prefix('Rp')
                                        ->readOnly(),

                                ])
                                ->columns(3)
                                ->addable(false)
                                ->deletable(false),

                        ]),

                ])
                // ✅ Tombol submit pindah ke sebelah Next
                ->submitAction(
                    new \Illuminate\Support\HtmlString(
                        '<button type="submit"
                            class="fi-btn fi-btn-size-md inline-flex items-center justify-center gap-1 rounded-lg bg-primary-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-primary-500">
                            Buat Pembelian
                        </button>'
                    )
                )
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
                    ->alignment('center')
                    ->placeholder('Tidak ada karyawan')
                    ->searchable(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
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
                    ->color(fn ($record) =>
                        $record->status === 'hutang' ? 'danger' : 'success'
                    ),

                TextColumn::make('pembayaran.nama_vendor')
                    ->label('Vendor')
                    ->placeholder('-')
                    ->searchable(),

                TextColumn::make('tgl')
                    ->label('Tgl')
                    ->dateTime('d M Y'),

            ])

            ->filters([])

            ->headerActions([

                TableAction::make('downloadPdf')
                    ->label('Unduh PDF')
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('success')
                    ->action(function () {
                        $pembelian = Pembelian::with([
                            'barang',
                            'pembayaran',
                            'karyawan',
                        ])->get();

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