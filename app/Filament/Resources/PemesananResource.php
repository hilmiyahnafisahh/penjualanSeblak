<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PemesananResource\Pages;
use App\Models\Pemesanan;
use App\Models\DetailPesanan;
use App\Models\Menu;
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

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Wizard::make([
                    // STEP 1: DATA PESANAN
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
                                        ->default(now())
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

                                    // Hidden fields
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

                    // STEP 2: DETAIL MENU
                    Wizard\Step::make('Detail Menu')
                        ->icon('heroicon-m-shopping-bag')
                        ->schema([
                            Forms\Components\Section::make('Daftar Menu')
                                ->schema([
                                    // Ganti bagian Repeater yang ada
                                        Repeater::make('DetailPesanan')
                                            ->relationship('DetailPesanan')
                                            ->reactive()
                                            ->afterStateUpdated(function ($state, Set $set) {
                                                $total = 0;
                                                foreach ($state as $item) {
                                                    $total += $item['subtotal'] ?? 0;
                                                }
                                                $set('subtotal', $total); // ✅ ini sudah benar
                                            })
                                                                                ->schema([

                                            Select::make('id_menu')
                                                ->label('Menu')
                                                ->options(Menu::pluck('nama_menu', 'id'))
                                                ->required()
                                                ->searchable()
                                                ->disableOptionsWhenSelectedInSiblingRepeaterItems()
                                                ->reactive()
                                                ->afterStateUpdated(function ($state, Set $set, Get $get) {

                                                    $menu = Menu::find($state);

                                                    if ($menu) {

                                                        $qty = $get('jumlah') ?? 1;

                                                        $harga = $menu->harga_menu;

                                                        $set('subtotal', $harga * $qty);
                                                    }
                                                })
                                                ->columnSpan(2),

                                            TextInput::make('jumlah')
                                                ->label('Qty')
                                                ->numeric()
                                                ->default(1)
                                                ->required()
                                                ->live()
                                                ->afterStateUpdated(function ($state, Set $set, Get $get) {

                                                    $menuId = $get('id_menu');
                                                    $menu = Menu::find($menuId);
                                                    $harga = $menu?->harga_menu ?? 0;

                                                    $set('subtotal', $harga * $state);
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
                                                ->columnSpan(1),

                                            TextInput::make('catatan')
                                                ->label('Catatan')
                                                ->placeholder('Opsional')
                                                ->columnSpan(2),

                                        ])
                                        ->columns(4)
                                        ->defaultItems(1)
                                        ->minItems(1)
                                        ->createItemButtonLabel('Tambah Menu')
                                        ->required(),
                                ]),
                        ]),

                    // STEP 3: KONFRIMASI
                    Wizard\Step::make('Konfirmasi')
                        ->icon('heroicon-m-check-circle')
                        ->schema([
                            Forms\Components\Section::make('Ringkasan Pesanan')
                                ->schema([
                                    Placeholder::make('summary')
                                        ->label('')
                                        ->reactive()
                                        ->content(function (Get $get) {
                                            $items = $get('DetailPesanan') ?? [];
                                            if (empty($items)) {
                                                return 'Belum ada menu yang dipilih.';
                                            }

                                            $html = '<div class="space-y-4">';
                                            $html .= '<table class="w-full text-sm border-collapse border border-gray-300">';
                                            $html .= '<thead><tr class="bg-gray-100"><th class="border border-gray-300 px-4 py-2 text-left">Menu</th><th class="border border-gray-300 px-4 py-2 text-center">Qty</th><th class="border border-gray-300 px-4 py-2 text-right">Harga</th><th class="border border-gray-300 px-4 py-2 text-right">Subtotal</th></tr></thead>';
                                            $html .= '<tbody>';

                                            $total = 0;
                                            foreach ($items as $item) {
                                                $menu = Menu::find($item['id_menu'] ?? null);
                                                $nama = $menu?->nama_menu ?? 'Menu Tidak Ditemukan';
                                                $qty = $item['jumlah'] ?? 0;
                                                $harga = $menu?->harga_menu ?? 0;
                                                $subtotal = $harga * $qty;
                                                $total += $subtotal;

                                                $html .= "<tr>";
                                                $html .= "<td class='border border-gray-300 px-4 py-2'>{$nama}</td>";
                                                $html .= "<td class='border border-gray-300 px-4 py-2 text-center'>{$qty}</td>";
                                                $html .= "<td class='border border-gray-300 px-4 py-2 text-right'>Rp " . number_format($harga, 0, ',', '.') . "</td>";
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
                ->submitAction(new HtmlString('<button type="submit" class="fi-btn fi-btn-size-md fi-btn-color-primary fi-ac-btn-action">Simpan Pesanan</button>'))
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

                TextColumn::make('subtotal')
                    ->label('Total Dibayar')
                    ->money('IDR', divideBy: 1)
                    ->alignment('end')
                    ->sortable(),

                TextColumn::make('status_pemesanan')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'diproses' => 'warning',
                        'selesai' => 'success',
                        'batal' => 'danger',
                        default => 'gray',
                    })
                    ->sortable(),

                TextColumn::make('tanggal_pemesanan')
                    ->label('Tanggal')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status_pemesanan')
                    ->label('Status')
                    ->options([
                        'diproses' => 'Diproses',
                        'selesai' => 'Selesai',
                        'batal' => 'Batal',
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
            'index' => Pages\ListPemesanans::route('/'),
            'create' => Pages\CreatePemesanan::route('/create'),
            'edit' => Pages\EditPemesanan::route('/{record}/edit'),
        ];
    }
}