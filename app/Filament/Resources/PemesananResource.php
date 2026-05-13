<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PemesananResource\Pages;
use App\Models\Pemesanan;
use App\Models\DetailPesanan;
use App\Models\Menu;
use App\Models\Barang;
use App\Models\Pelanggan;
use App\Models\Layanan;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\Wizard;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Placeholder;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Illuminate\Support\HtmlString;

class PemesananResource extends Resource
{
    protected static ?string $model = Pemesanan::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';

    protected static ?string $navigationGroup = 'Transaksi';

    protected static ?string $modelLabel        = 'Pemesanan';
    protected static ?string $pluralModelLabel  = 'Pemesanan';
    protected static ?string $navigationLabel   = 'Pemesanan';
    protected static ?string $slug              = 'pemesanan';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Wizard::make([

                    // ─────────────────────────────────────
                    // STEP 1: DATA PESANAN
                    // ─────────────────────────────────────
                    Wizard\Step::make('Data Pesanan')
                        ->icon('heroicon-m-document-text')
                        ->schema([
                            Forms\Components\Section::make('Informasi Pesanan')
                                ->columns(2)
                                ->schema([

                                    TextInput::make('id_pesanan')
                                        ->label('Nomor Pesanan')
                                        ->default(fn () => Pemesanan::getKodeFaktur())
                                        ->required()
                                        ->readonly()
                                        ->columnSpan(1),

                                    DateTimePicker::make('tanggal_pemesanan')
                                        ->label('Tanggal Pesanan')
                                        ->default(now('Asia/Jakarta'))
                                        ->timezone('Asia/Jakarta')
                                        ->required()
                                        ->columnSpan(1),

                                    Select::make('id_pelanggan')
                                        ->label('Pelanggan')
                                        ->options(Pelanggan::pluck('nama_pelanggan', 'id'))
                                        ->searchable()
                                        ->required()
                                        ->columnSpan(1),

                                    Select::make('id_layanan')
                                        ->label('Layanan')
                                        ->options(Layanan::pluck('nama_layanan', 'id'))
                                        ->searchable()
                                        ->required()
                                        ->columnSpan(1),

                                    TextInput::make('status_pemesanan')
                                        ->default('diproses')
                                        ->hidden()
                                        ->dehydrated(),

                                    TextInput::make('subtotal')
                                        ->default(0)
                                        ->hidden()
                                        ->dehydrated(),
                                ]),
                        ]),

                    // ─────────────────────────────────────
                    // STEP 2: DETAIL MENU
                    // ─────────────────────────────────────
                    Wizard\Step::make('Detail Menu')
                        ->icon('heroicon-m-shopping-bag')
                        ->schema([
                            Forms\Components\Section::make('Daftar Menu')
                                ->schema([
                                    Repeater::make('DetailPesanan')
                                        ->relationship('DetailPesanan')
                                        ->reactive()
                                        ->afterStateUpdated(function ($state, Set $set) {
                                            $total = 0;
                                            foreach ($state as $item) {
                                                // ✅ FIX: cast ke int/float agar tidak error string*string
                                                $total += (float) ($item['subtotal'] ?? 0);
                                            }
                                            $set('subtotal', $total);
                                        })
                                        ->schema([

                                            Select::make('kategori_menu')
                                                ->label('Kategori Menu')
                                                ->options([
                                                    'Makanan' => 'Makanan',
                                                    'Minuman' => 'Minuman',
                                                ])
                                                ->reactive()
                                                ->dehydrated(false)
                                                ->afterStateUpdated(function ($state, Set $set) {
                                                    $set('id_menu', null);
                                                })
                                                ->columnSpan(2),

                                            Select::make('id_menu')
                                                ->label('Menu')
                                                ->options(function (Get $get) {
                                                    $kategori = $get('kategori_menu');

                                                    return Menu::when($kategori, function ($query) use ($kategori) {
                                                        return $query->where('kategori_menu', $kategori);
                                                    })->pluck('nama_menu', 'id');
                                                })
                                                ->required()
                                                ->searchable()
                                                ->disableOptionsWhenSelectedInSiblingRepeaterItems()
                                                ->reactive()
                                                ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                                    $menu = Menu::find($state);
                                                    if ($menu) {
                                                        $qty       = (int) ($get('jumlah') ?? 1);
                                                        $hargaMenu = (float) $menu->harga_menu;
                                                        $toppingItems = $get('topping_items') ?? [];
                                                        $toppingTotal = 0;
                                                        foreach ($toppingItems as $item) {
                                                            $toppingTotal += (float) ($item['subtotal'] ?? 0);
                                                        }
                                                        $set('harga_menu', $hargaMenu);
                                                        $set('subtotal', $hargaMenu * $qty + $toppingTotal);
                                                    }
                                                })
                                                ->columnSpan(4),

                                            TextInput::make('harga_menu')
                                                ->label('Harga Menu')
                                                ->numeric()
                                                ->prefix('Rp')
                                                ->readonly()
                                                ->dehydrated(false)
                                                ->columnSpan(2),

                                            Repeater::make('topping_items')
                                                ->label('Topping')
                                                ->dehydrated(false)
                                                ->visible(fn (Get $get): bool => $get('kategori_menu') === 'Makanan')
                                                ->schema([
                                                    Select::make('id_barang')
                                                        ->label('Topping')
                                                        ->options(Barang::pluck('nama_barang', 'id'))
                                                        ->required()
                                                        ->searchable()
                                                        ->reactive()
                                                        ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                                            $barang = Barang::find($state);
                                                            $set('harga', (float) ($barang?->harga_jual ?? 0));
                                                            $qty = (int) ($get('qty') ?? 1);
                                                            $set('subtotal', (float) ($barang?->harga_jual ?? 0) * $qty);
                                                        })
                                                        ->columnSpan(3),

                                                    TextInput::make('qty')
                                                        ->label('Qty')
                                                        ->numeric()
                                                        ->default(1)
                                                        ->required()
                                                        ->reactive()
                                                        ->live()
                                                        ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                                            $harga = (float) ($get('harga') ?? 0);
                                                            $set('subtotal', $harga * (int) $state);
                                                        })
                                                        ->columnSpan(1),

                                                    TextInput::make('harga')
                                                        ->label('Harga Topping')
                                                        ->numeric()
                                                        ->prefix('Rp')
                                                        ->readonly()
                                                        ->dehydrated(false)
                                                        ->columnSpan(1),

                                                    TextInput::make('subtotal')
                                                        ->label('Subtotal Topping')
                                                        ->numeric()
                                                        ->prefix('Rp')
                                                        ->readonly()
                                                        ->dehydrated(false)
                                                        ->columnSpan(1),
                                                ])
                                                ->columns(6)
                                                ->defaultItems(0)
                                                ->minItems(0)
                                                ->createItemButtonLabel('Tambah Topping')
                                                ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                                    $menu = Menu::find($get('id_menu'));
                                                    $qty = (int) ($get('jumlah') ?? 1);
                                                    $hargaMenu = (float) ($menu?->harga_menu ?? 0);
                                                    $toppingTotal = 0;
                                                    foreach ($state as $item) {
                                                        $toppingTotal += (float) ($item['subtotal'] ?? 0);
                                                    }
                                                    $set('subtotal', $hargaMenu * $qty + $toppingTotal);
                                                })
                                                ->columnSpan(6),

                                            TextInput::make('jumlah')
                                                ->label('Qty Menu')
                                                ->numeric()
                                                ->default(1)
                                                ->required()
                                                ->live()
                                                ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                                    $menuId    = $get('id_menu');
                                                    $menu      = Menu::find($menuId);
                                                    $hargaMenu = (float) ($menu?->harga_menu ?? 0);
                                                    $toppingItems = $get('topping_items') ?? [];
                                                    $toppingTotal = 0;
                                                    foreach ($toppingItems as $item) {
                                                        $toppingTotal += (float) ($item['subtotal'] ?? 0);
                                                    }
                                                    $set('harga_menu', $hargaMenu);
                                                    $set('subtotal', $hargaMenu * (int) $state + $toppingTotal);
                                                })
                                                ->columnSpan(1),

                                            TextInput::make('subtotal')
                                                ->label('Subtotal')
                                                ->numeric()
                                                ->prefix('Rp')
                                                ->readonly()
                                                ->dehydrated()
                                                ->default(0)
                                                ->required()
                                                ->columnSpan(2),

                                            TextInput::make('catatan')
                                                ->label('Catatan')
                                                ->placeholder('Opsional')
                                                ->columnSpan(6),

                                        ])
                                        ->columns(6)
                                        ->defaultItems(1)
                                        ->minItems(1)
                                        ->createItemButtonLabel('Tambah Menu')
                                        ->required(),
                                ]),
                        ]),

                    // ─────────────────────────────────────
                    // STEP 3: KONFIRMASI
                    // ─────────────────────────────────────
                    Wizard\Step::make('Konfirmasi')
                        ->icon('heroicon-m-check-circle')
                        ->schema([
                            Forms\Components\Section::make('Ringkasan Pesanan')
                                ->schema([
                                    Placeholder::make('bayar_info')
                                        ->label('')
                                        ->content('Periksa kembali pesanan Anda, lalu tekan tombol Bayar untuk menyelesaikan transaksi.'),

                                    Placeholder::make('summary')
                                        ->label('')
                                        ->reactive()
                                        ->content(function (Get $get) {
                                            $items = $get('DetailPesanan') ?? [];
                                            if (empty($items)) {
                                                return 'Belum ada menu yang dipilih.';
                                            }

                                            $html  = '<div class="space-y-4">';
                                            $html .= '<table class="w-full text-sm border-collapse border border-gray-300">';
                                            $html .= '<thead><tr class="bg-gray-100">'
                                                   . '<th class="border border-gray-300 px-4 py-2 text-left">Menu</th>'
                                                   . '<th class="border border-gray-300 px-4 py-2 text-center">Qty</th>'
                                                   . '<th class="border border-gray-300 px-4 py-2 text-right">Harga</th>'
                                                   . '<th class="border border-gray-300 px-4 py-2 text-right">Subtotal</th>'
                                                   . '</tr></thead>';
                                            $html .= '<tbody>';

                                            $total = 0;
                                            foreach ($items as $item) {
                                                $menu         = Menu::find($item['id_menu'] ?? null);
                                                $nama         = $menu?->nama_menu ?? 'Menu Tidak Ditemukan';
                                                $qty          = (int) ($item['jumlah'] ?? 0);
                                                $hargaMenu    = (float) ($menu?->harga_menu ?? 0);
                                                $menuTotal    = $hargaMenu * $qty;

                                                $toppingItems = $item['topping_items'] ?? [];
                                                $toppingNames = [];
                                                $toppingTotal = 0;
                                                if (!empty($toppingItems)) {
                                                    foreach ($toppingItems as $toppingItem) {
                                                        $toppingTotal += (float) ($toppingItem['subtotal'] ?? 0);
                                                        $barang = Barang::find($toppingItem['id_barang'] ?? null);
                                                        if ($barang) {
                                                            $toppingNames[] = $barang->nama_barang;
                                                        }
                                                    }
                                                }
                                                $subtotal = $menuTotal + $toppingTotal;
                                                $total   += $subtotal;

                                                $namaCell = $nama;
                                                $namaCell .= "<div class='text-xs text-gray-600 mt-1'>";
                                                if ($qty > 1) {
                                                    $namaCell .= "Harga menu: Rp " . number_format($hargaMenu, 0, ',', '.') . " x {$qty} = Rp " . number_format($menuTotal, 0, ',', '.') . "<br>";
                                                } else {
                                                    $namaCell .= "Harga menu: Rp " . number_format($hargaMenu, 0, ',', '.') . "<br>";
                                                }
                                                $namaCell .= "Qty menu: {$qty}";
                                                if (!empty($toppingNames)) {
                                                    $namaCell .= "<br>Topping: " . implode(', ', $toppingNames);
                                                }
                                                $namaCell .= "</div>";

                                                $hargaCell = "<div>Rp " . number_format($hargaMenu, 0, ',', '.') . "";
                                                if ($qty > 1) {
                                                    $hargaCell .= " x {$qty}";
                                                }
                                                $hargaCell .= "</div>";
                                                if (!empty($toppingItems)) {
                                                    foreach ($toppingItems as $toppingItem) {
                                                        $qtyToppingItem = (int) ($toppingItem['qty'] ?? 0);
                                                        $hargaToppingItem = (float) ($toppingItem['harga'] ?? 0);
                                                        $subtotalToppingItem = (float) ($toppingItem['subtotal'] ?? 0);
                                                        $barang = Barang::find($toppingItem['id_barang'] ?? null);
                                                        $toppingName = $barang?->nama_barang ?? 'Topping';
                                                        $hargaCell .= "<div class='text-xs text-gray-600 mt-1'>";
                                                        $hargaCell .= $toppingName . ": Rp " . number_format($hargaToppingItem, 0, ',', '.') . " x {$qtyToppingItem} = Rp " . number_format($subtotalToppingItem, 0, ',', '.') . "";
                                                        $hargaCell .= "</div>";
                                                    }
                                                }

                                                $qtyCell = "<div>{$qty}</div>";
                                                if (!empty($toppingItems)) {
                                                    foreach ($toppingItems as $toppingItem) {
                                                        $qtyToppingItem = (int) ($toppingItem['qty'] ?? 0);
                                                        $barang = Barang::find($toppingItem['id_barang'] ?? null);
                                                        $toppingName = $barang?->nama_barang ?? 'Topping';
                                                        $qtyCell .= "<div class='text-xs text-gray-600 mt-1'>{$toppingName} qty: {$qtyToppingItem}</div>";
                                                    }
                                                }

                                                $html .= "<tr>";
                                                $html .= "<td class='border border-gray-300 px-4 py-2'>{$namaCell}</td>";
                                                $html .= "<td class='border border-gray-300 px-4 py-2 text-center'>{$qtyCell}</td>";
                                                $html .= "<td class='border border-gray-300 px-4 py-2 text-right'>{$hargaCell}</td>";
                                                $html .= "<td class='border border-gray-300 px-4 py-2 text-right'>Rp " . number_format($subtotal, 0, ',', '.') . "</td>";
                                                $html .= "</tr>";
                                            }

                                            $html .= '</tbody></table>';
                                            $html .= '<div class="mt-4 p-4 bg-green-100 border border-green-300 rounded-lg">';
                                            $html .= '<div class="text-lg font-bold text-green-800 text-right">';
                                            $html .= 'TOTAL: Rp ' . number_format($total, 0, ',', '.');
                                            $html .= '</div></div></div>';

                                            return new HtmlString($html);
                                        }),
                                ]),
                        ]),

                ])
                ->submitAction(new HtmlString('<button type="submit" class="fi-btn fi-btn-size-md fi-btn-color-success fi-ac-btn-action">Bayar</button>'))
                ->columnSpanFull(),
            ]);
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()->with('DetailPesanan');
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\CheckboxColumn::make(''),

                TextColumn::make('id_pesanan')
                    ->label('No. Pesanan')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('pelanggan.nama_pelanggan')
                    ->label('Pelanggan')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('layanan.nama_layanan')
                    ->label('Layanan')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('menu_pemesanan')
                    ->label('Menu Pemesanan')
                    ->getStateUsing(function ($record) {
                        return $record->DetailPesanan->map(function ($detail) {
                            $menu = $detail->menu;
                            return $menu
                                ? $menu->nama_menu . ' (x' . $detail->jumlah . ')'
                                : 'Menu tidak ditemukan';
                        })->join(', ');
                    })
                    ->wrap()
                    ->limit(50),

                TextColumn::make('subtotal')
                    ->label('Total Dibayar')
                    ->formatStateUsing(fn ($state) => 'Rp ' . number_format((float) $state, 0, ',', '.'))
                    ->alignment('end')
                    ->sortable(),

                TextColumn::make('status_pemesanan')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending'  => 'Belum Dibayar',
                        'diproses' => 'Diproses',
                        'selesai'  => 'Selesai',
                        'batal'    => 'Batal',
                        default    => ucfirst($state),
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'pending'  => 'warning',
                        'diproses' => 'warning',
                        'selesai'  => 'success',
                        'batal'    => 'danger',
                        default    => 'gray',
                    })
                    ->sortable(),

                TextColumn::make('tanggal_pemesanan')
                    ->label('Tanggal')
                    ->dateTime('d/m/Y H:i')
                    ->timezone('Asia/Jakarta')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status_pemesanan')
                    ->label('Status')
                    ->options([
                        'pending'  => 'Belum Dibayar',
                        'diproses' => 'Diproses',
                        'selesai'  => 'Selesai',
                        'batal'    => 'Batal',
                    ]),
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

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListPemesanans::route('/'),
            'create' => Pages\CreatePemesanan::route('/create'),
            'edit'   => Pages\EditPemesanan::route('/{record}/edit'),
        ];
    }
}   