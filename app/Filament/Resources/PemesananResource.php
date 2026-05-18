<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PemesananResource\Pages;
use App\Models\Pemesanan;
use App\Models\Menu;
use App\Models\Barang;
use App\Models\Pelanggan;
use App\Models\Layanan;
use Barryvdh\DomPDF\Facade\Pdf;
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
use Filament\Forms\Components\Hidden;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Support\HtmlString;

class PemesananResource extends Resource
{
    protected static ?string $model            = Pemesanan::class;
    protected static ?string $navigationIcon   = 'heroicon-o-shopping-cart';
    protected static ?string $navigationGroup  = 'Transaksi';
    protected static ?string $modelLabel       = 'Pemesanan';
    protected static ?string $pluralModelLabel = 'Pemesanan';
    protected static ?string $navigationLabel  = 'Pemesanan';
    protected static ?string $slug             = 'pemesanan';

    // =========================================================
    // FORM
    // =========================================================

    public static function form(Form $form): Form
    {
        return $form->schema([

            Wizard::make([

                // ─────────────────────────────────────────────
                // STEP 1: DATA PESANAN
                // ─────────────────────────────────────────────

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

                                TextInput::make('subtotal')
                                    ->default(0)
                                    ->hidden()
                                    ->dehydrated(),

                                Hidden::make('redirect_to_payment')
                                    ->default(false)
                                    ->dehydrated(),

                            ]),
                    ]),

                // ─────────────────────────────────────────────
                // STEP 2: DETAIL MENU
                // ─────────────────────────────────────────────

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
                                            $total += (float) ($item['subtotal'] ?? 0);

                                            // Tambahkan subtotal topping
                                            if (! empty($item['topping']) && is_array($item['topping'])) {
                                                foreach ($item['topping'] as $topping) {
                                                    $total += (float) ($topping['subtotal'] ?? 0);
                                                }
                                            }
                                        }

                                        $set('subtotal', $total);
                                    })
                                    ->schema([

                                        Select::make('kategori_menu')
                                            ->label('Kategori')
                                            ->options([
                                                'Makanan' => 'Makanan',
                                                'Minuman' => 'Minuman',
                                            ])
                                            ->reactive()
                                            ->dehydrated(false)
                                            ->afterStateUpdated(fn (Set $set) => $set('id_menu', null))
                                            ->columnSpan(1),

                                        Select::make('id_menu')
                                            ->label('Menu')
                                            ->options(function (Get $get) {
                                                return Menu::when(
                                                    $get('kategori_menu'),
                                                    fn ($query, $kategori) => $query->where('kategori_menu', $kategori)
                                                )->pluck('nama_menu', 'id');
                                            })
                                            ->searchable()
                                            ->required()
                                            ->reactive()
                                            ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                                $menu = Menu::find($state);
                                                if ($menu) {
                                                    $qty   = (int) ($get('jumlah') ?? 1);
                                                    $harga = (float) $menu->harga_menu;
                                                    $set('harga_menu', $harga);
                                                    $set('subtotal', $harga * $qty);
                                                }
                                            })
                                            ->columnSpan(1),

                                        TextInput::make('harga_menu')
                                            ->label('Harga Satuan')
                                            ->numeric()
                                            ->prefix('Rp')
                                            ->readonly()
                                            ->dehydrated(false)
                                            ->columnSpan(1),

                                        TextInput::make('jumlah')
                                            ->label('Qty')
                                            ->numeric()
                                            ->default(1)
                                            ->required()
                                            ->reactive()
                                            ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                                $menu  = Menu::find($get('id_menu'));
                                                $harga = (float) ($menu?->harga_menu ?? 0);
                                                $set('subtotal', $harga * (int) $state);
                                            })
                                            ->columnSpan(1),

                                        TextInput::make('subtotal')
                                            ->label('Subtotal')
                                            ->numeric()
                                            ->prefix('Rp')
                                            ->readonly()
                                            ->dehydrated()
                                            ->default(0)
                                            ->columnSpan(1),

                                        TextInput::make('catatan')
                                            ->label('Catatan')
                                            ->placeholder('Opsional')
                                            ->columnSpan(1),

                                        Repeater::make('topping')
                                            ->label('Tambah Topping')
                                            ->visible(fn (Get $get) => $get('kategori_menu') === 'Makanan')
                                            ->reactive()
                                            ->schema([

                                                Select::make('id_barang')
                                                    ->label('Barang Topping')
                                                    ->options(Barang::pluck('nama_barang', 'id_barang'))
                                                    ->searchable()
                                                    ->required()
                                                    ->reactive()
                                                    ->afterStateHydrated(function ($state, Set $set, Get $get) {
                                                        if (! $state) {
                                                            return;
                                                        }

                                                        $barang = Barang::where('id_barang', $state)->first();
                                                        $qty    = (int) ($get('qty') ?? 1);

                                                        if ($barang) {
                                                            // FIX: gunakan harga_jual, bukan harga_barang
                                                            $harga = (float) $barang->harga_jual;
                                                            $set('nama_barang', $barang->nama_barang);
                                                            $set('harga', $harga);
                                                            $set('subtotal', $harga * $qty);
                                                        }
                                                    })
                                                    ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                                        $barang = Barang::where('id_barang', $state)->first();

                                                        if ($barang) {
                                                            // FIX: gunakan harga_jual, bukan harga_barang
                                                            $harga = (float) $barang->harga_jual;
                                                            $qty   = (int) ($get('qty') ?? 1);

                                                            $set('nama_barang', $barang->nama_barang);
                                                            $set('harga', $harga);
                                                            $set('subtotal', $harga * $qty);
                                                        } else {
                                                            $set('nama_barang', null);
                                                            $set('harga', 0);
                                                            $set('subtotal', 0);
                                                        }
                                                    })
                                                    ->columnSpan(2),

                                                Hidden::make('nama_barang')
                                                    ->dehydrated(),

                                                TextInput::make('qty')
                                                    ->label('Qty Topping')
                                                    ->numeric()
                                                    ->default(1)
                                                    ->required()
                                                    ->reactive()
                                                    ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                                        $harga = (float) ($get('harga') ?? 0);
                                                        $set('subtotal', $harga * (int) $state);
                                                    })
                                                    ->columnSpan(1),

                                                TextInput::make('harga')
                                                    ->label('Harga Topping')
                                                    ->prefix('Rp')
                                                    ->readonly()
                                                    ->reactive()
                                                    ->numeric()
                                                    ->dehydrated()
                                                    ->default(0)
                                                    ->placeholder('Pilih barang topping')
                                                    ->columnSpan(1),

                                                TextInput::make('subtotal')
                                                    ->label('Subtotal Topping')
                                                    ->prefix('Rp')
                                                    ->readonly()
                                                    ->reactive()
                                                    ->numeric()
                                                    ->dehydrated()
                                                    ->default(0)
                                                    ->placeholder('0')
                                                    ->columnSpan(1),

                                            ])
                                            ->columns(2)
                                            ->defaultItems(0)
                                            ->createItemButtonLabel('Tambah Topping')
                                            ->columnSpanFull(),

                                    ])
                                    ->columns(2)
                                    ->defaultItems(1)
                                    ->minItems(1)
                                    ->createItemButtonLabel('Tambah Menu'),

                            ]),
                    ]),

                // ─────────────────────────────────────────────
                // STEP 3: KONFIRMASI
                // ─────────────────────────────────────────────

                Wizard\Step::make('Konfirmasi')
                    ->icon('heroicon-m-check-circle')
                    ->schema([

                        Forms\Components\Section::make('Ringkasan Pesanan')
                            ->schema([

                                Placeholder::make('summary')
                                    ->label('')
                                    ->content(function (Get $get) {

                                        $items = $get('DetailPesanan') ?? [];

                                        if (empty($items)) {
                                            return new HtmlString('<p class="text-gray-500">Belum ada menu yang dipilih.</p>');
                                        }

                                        $total = 0;
                                        $rows  = '';

                                        foreach ($items as $item) {
                                            $menu     = Menu::find($item['id_menu'] ?? null);
                                            $nama     = $menu?->nama_menu ?? '-';
                                            $qty      = (int) ($item['jumlah'] ?? 0);
                                            $harga    = (float) ($menu?->harga_menu ?? 0);
                                            $subtotal = (float) ($item['subtotal'] ?? 0);
                                            $total   += $subtotal;
                                            $catatan  = $item['catatan'] ?? '';

                                            $rows .= "
                                                <tr>
                                                    <td style='padding:10px 12px; border-bottom:1px solid #e5e7eb;'>
                                                        <strong>{$nama}</strong>
                                                        " . ($catatan ? "<br><span style='font-size:12px;color:#6b7280;'>Catatan: {$catatan}</span>" : '') . "
                                                    </td>
                                                    <td style='padding:10px 12px; border-bottom:1px solid #e5e7eb; text-align:center;'>{$qty}</td>
                                                    <td style='padding:10px 12px; border-bottom:1px solid #e5e7eb; text-align:right;'>Rp " . number_format($harga, 0, ',', '.') . "</td>
                                                    <td style='padding:10px 12px; border-bottom:1px solid #e5e7eb; text-align:right;'>Rp " . number_format($subtotal, 0, ',', '.') . "</td>
                                                </tr>
                                            ";

                                            if (! empty($item['topping']) && is_array($item['topping'])) {
                                                foreach ($item['topping'] as $topping) {
                                                    $toppingName     = $topping['nama_barang'] ?? 'Topping';
                                                    $toppingQty      = (int) ($topping['qty'] ?? 0);
                                                    $toppingPrice    = (float) ($topping['harga'] ?? 0);
                                                    $toppingSubtotal = (float) ($topping['subtotal'] ?? 0);

                                                    if ($toppingPrice <= 0 && ! empty($topping['id_barang'])) {
                                                        $barang          = Barang::where('id_barang', $topping['id_barang'])->first();
                                                        // FIX: gunakan harga_jual, bukan harga_barang
                                                        $toppingPrice    = (float) ($barang?->harga_jual ?? 0);
                                                        $toppingSubtotal = $toppingPrice * $toppingQty;
                                                    } elseif ($toppingSubtotal <= 0) {
                                                        $toppingSubtotal = $toppingPrice * $toppingQty;
                                                    }

                                                    $total += $toppingSubtotal;

                                                    $rows .= "
                                                        <tr>
                                                            <td style='padding:10px 12px 10px 28px; border-bottom:1px solid #e5e7eb; color:#6b7280;'>
                                                                &ndash; {$toppingName}
                                                            </td>
                                                            <td style='padding:10px 12px; border-bottom:1px solid #e5e7eb; text-align:center;'>{$toppingQty}</td>
                                                            <td style='padding:10px 12px; border-bottom:1px solid #e5e7eb; text-align:right;'>Rp " . number_format($toppingPrice, 0, ',', '.') . "</td>
                                                            <td style='padding:10px 12px; border-bottom:1px solid #e5e7eb; text-align:right;'>Rp " . number_format($toppingSubtotal, 0, ',', '.') . "</td>
                                                        </tr>
                                                    ";
                                                }
                                            }
                                        }

                                        $html = "
                                            <div style='border:1px solid #e5e7eb; border-radius:8px; overflow:hidden;'>
                                                <table style='width:100%; border-collapse:collapse; font-size:14px;'>
                                                    <thead>
                                                        <tr style='background:#f9fafb;'>
                                                            <th style='padding:10px 12px; text-align:left; border-bottom:1px solid #e5e7eb;'>Menu</th>
                                                            <th style='padding:10px 12px; text-align:center; border-bottom:1px solid #e5e7eb;'>Qty</th>
                                                            <th style='padding:10px 12px; text-align:right; border-bottom:1px solid #e5e7eb;'>Harga</th>
                                                            <th style='padding:10px 12px; text-align:right; border-bottom:1px solid #e5e7eb;'>Subtotal</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>{$rows}</tbody>
                                                    <tfoot>
                                                        <tr style='background:#f0fdf4;'>
                                                            <td colspan='3' style='padding:12px; text-align:right; font-weight:bold; color:#15803d;'>TOTAL</td>
                                                            <td style='padding:12px; text-align:right; font-weight:bold; font-size:16px; color:#15803d;'>
                                                                Rp " . number_format($total, 0, ',', '.') . "
                                                            </td>
                                                        </tr>
                                                    </tfoot>
                                                </table>
                                            </div>
                                        ";

                                        return new HtmlString($html);
                                    }),

                            ]),
                    ]),

            ])
            ->submitAction(
                new HtmlString('
                    <button type="submit" class="fi-btn fi-btn-size-md fi-btn-color-success fi-ac-btn-action">
                        Bayar
                    </button>
                ')
            )
            ->columnSpanFull(),

        ]);
    }

    // =========================================================
    // TABLE
    // =========================================================

    public static function table(Table $table): Table
    {
        return $table
            ->columns([

                TextColumn::make('id_pesanan')
                    ->label('No Pesanan')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('pelanggan.nama_pelanggan')
                    ->label('Pelanggan')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('subtotal')
                    ->label('Total')
                    ->formatStateUsing(fn ($state) => 'Rp ' . number_format((float) $state, 0, ',', '.'))
                    ->sortable(),

                TextColumn::make('status_pemesanan')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'belumdibayar' => 'Belum Dibayar',
                        'diproses'     => 'Diproses',
                        'selesai'      => 'Selesai',
                        default        => ucfirst($state),
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'belumdibayar' => 'warning',
                        'diproses'     => 'info',
                        'selesai'      => 'success',
                        default        => 'gray',
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
                        'belumdibayar' => 'Belum Dibayar',
                        'diproses'     => 'Diproses',
                        'selesai'      => 'Selesai',
                    ]),

            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([

                Tables\Actions\BulkActionGroup::make([

                    Tables\Actions\BulkAction::make('downloadPdf')
                        ->label('Unduh PDF')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->color('success')
                        ->deselectRecordsAfterCompletion()
                        ->action(function ($records) {
                            $pdf = Pdf::loadView('pdf.invoice_bulk', [
                                'records' => $records,
                            ]);
                            return response()->streamDownload(
                                fn () => print($pdf->output()),
                                'invoice-pemesanan.pdf'
                            );
                        }),

                    Tables\Actions\DeleteBulkAction::make(),

                ])->label('Aksi'),

            ]);
    }

    // =========================================================
    // PAGES
    // =========================================================

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListPemesanans::route('/'),
            'create' => Pages\CreatePemesanan::route('/create'),
            'edit'   => Pages\EditPemesanan::route('/{record}/edit'),
        ];
    }
}