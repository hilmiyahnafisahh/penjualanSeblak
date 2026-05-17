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
use Filament\Forms\Components\Placeholder;
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

                    // =====================================================
                    // STEP 1 : PILIH PESANAN
                    // =====================================================

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
                                        ->required(),

                                    TextInput::make('id_pemesanan')
                                        ->label('ID Pesanan')
                                        ->default(fn () => request()->query('id_pemesanan'))
                                        ->hidden()
                                        ->dehydrated(),

                                    Placeholder::make('rincian_pesanan')
                                        ->label('Rincian Pesanan')
                                        ->content(function (Get $get) {
                                            $idPemesanan = $get('id_pemesanan') ?: request()->query('id_pemesanan');

                                            $pesanan = \App\Models\Pemesanan::with('DetailPesanan.menu', 'pelanggan')
                                                ->find($idPemesanan);

                                            if (!$pesanan) return 'Pesanan tidak ditemukan';

                                            $html = "<div class='space-y-2'>";
                                            $html .= "<div><strong>Nama Pelanggan:</strong> {$pesanan->pelanggan->nama_pelanggan}</div>";
                                            $html .= "<div><strong>No Pesanan:</strong> {$pesanan->id_pesanan}</div>";
                                            $html .= "<div><strong>Menu:</strong></div><ul class='list-disc list-inside ml-4'>";

                                            foreach ($pesanan->DetailPesanan as $item) {
                                                $namaMenu = $item->menu ? $item->menu->nama_menu : 'Menu tidak ditemukan';
                                                $html .= "<li>{$namaMenu} x {$item->jumlah} - Rp " . number_format($item->subtotal, 0, ',', '.') . "</li>";
                                            }

                                            $html .= "</ul>";
                                            $html .= "<div><strong>Total:</strong> Rp " . number_format($pesanan->subtotal, 0, ',', '.') . "</div>";
                                            $html .= "</div>";

                                            return new \Illuminate\Support\HtmlString($html);
                                        }),

                                    TextInput::make('total_pembayaran')
                                        ->label('Total Pembayaran')
                                        ->numeric()
                                        ->prefix('Rp')
                                        ->readonly()
                                        ->required()
                                        ->default(fn () => request()->query('id_pemesanan') ? \App\Models\Pemesanan::find(request()->query('id_pemesanan'))?->subtotal : 0),

                                    Select::make('metode_pembayaran')
                                        ->label('Metode Pembayaran')
                                        ->options([
                                            'cash'     => 'Cash',
                                            'qris'     => 'QRIS',
                                        ])
                                        ->required()
                                        ->reactive()
                                        ->afterStateUpdated(function ($state, Set $set) {
                                            if (in_array($state, ['qris'])) {
                                                $set('status_pembayaran', 'pending');
                                            } else {
                                                $set('status_pembayaran', 'lunas');
                                            }
                                        }),

                                    TextInput::make('status_pembayaran')
                                        ->default('lunas')
                                        ->hidden()
                                        ->dehydrated(),

                                ]),
                        ]),

                    // =====================================================
                    // STEP 2 : DETAIL PESANAN
                    // =====================================================

                    Wizard\Step::make('Detail Pesanan')
                        ->icon('heroicon-m-shopping-bag')
                        ->schema([

                            Placeholder::make('detail_pesanan')
                                ->label('')
                                ->reactive()

                                ->content(function (Get $get) {

                                    $idPemesanan = $get('id_pemesanan');

                                    if (!$idPemesanan) {
                                        return 'Pilih pesanan terlebih dahulu.';
                                    }

                                    $pemesanan = Pemesanan::with('DetailPesanan.menu')
                                        ->find($idPemesanan);

                                    if (!$pemesanan) {
                                        return 'Pesanan tidak ditemukan.';
                                    }

                                    $html  = '<div class="space-y-4">';

                                    $html .= '<table class="w-full text-sm border-collapse border border-gray-300">';

                                    $html .= '
                                        <thead>
                                            <tr class="bg-gray-100">
                                                <th class="border px-4 py-2 text-left">
                                                    Menu
                                                </th>

                                                <th class="border px-4 py-2 text-center">
                                                    Qty
                                                </th>

                                                <th class="border px-4 py-2 text-right">
                                                    Subtotal
                                                </th>
                                            </tr>
                                        </thead>
                                    ';

                                    $html .= '<tbody>';

                                    foreach ($pemesanan->DetailPesanan as $detail) {

                                        $namaMenu = $detail->menu?->nama_menu ?? '-';

                                        $html .= '
                                            <tr>

                                                <td class="border px-4 py-2">
                                                    ' . $namaMenu . '
                                                </td>

                                                <td class="border px-4 py-2 text-center">
                                                    ' . $detail->jumlah . '
                                                </td>

                                                <td class="border px-4 py-2 text-right">
                                                    Rp ' . number_format($detail->subtotal, 0, ',', '.') . '
                                                </td>

                                            </tr>
                                        ';
                                    }

                                    $html .= '</tbody>';
                                    $html .= '</table>';

                                    $html .= '
                                        <div class="mt-4 p-4 bg-green-100 rounded-lg">

                                            <div class="text-right text-lg font-bold text-green-700">

                                                TOTAL :
                                                Rp ' . number_format($pemesanan->subtotal, 0, ',', '.') . '

                                            </div>

                                        </div>
                                    ';

                                    $html .= '</div>';

                                    return new HtmlString($html);
                                }),

                        ]),

                    // =====================================================
                    // STEP 3 : KONFIRMASI
                    // =====================================================

                    Wizard\Step::make('Konfirmasi')
                        ->icon('heroicon-m-check-circle')
                        ->schema([

                            Placeholder::make('konfirmasi')
                                ->label('')

                                ->content(function (Get $get) {

                                    return new HtmlString('

                                        <div class="space-y-2">

                                            <div>
                                                <strong>No Pembayaran:</strong>
                                                ' . $get('id_pembayaran') . '
                                            </div>

                                            <div>
                                                <strong>Total:</strong>
                                                Rp ' . number_format((float) $get('total_pembayaran'), 0, ',', '.') . '
                                            </div>

                                            <div>
                                                <strong>Metode:</strong>
                                                ' . strtoupper($get('metode_pembayaran')) . '
                                            </div>

                                        </div>

                                    ');
                                }),

                        ]),

                ])

                ->submitAction(
                    new HtmlString('
                        <button
                            type="submit"
                            class="fi-btn fi-btn-size-md fi-btn-color-primary fi-ac-btn-action">

                            Simpan Pembayaran

                        </button>
                    ')
                )

                ->columnSpanFull(),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([

                TextColumn::make('id_pembayaran')
                    ->label('No Pembayaran')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('pemesanan.id_pesanan')
                    ->label('No Pesanan')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('pemesanan.pelanggan.nama_pelanggan')
                    ->label('Pelanggan')
                    ->searchable(),

                TextColumn::make('metode_pembayaran')
                    ->badge()
                    ->color('success'),

                TextColumn::make('total_pembayaran')
                    ->label('Total')
                    ->formatStateUsing(
                        fn ($state) =>
                        'Rp ' . number_format((float) $state, 0, ',', '.')
                    ),

                TextColumn::make('status_pembayaran')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {

                        'lunas'   => 'success',
                        'pending' => 'warning',
                        'batal'   => 'danger',

                        default   => 'gray',
                    }),

                TextColumn::make('tanggal_pembayaran')
                    ->dateTime('d/m/Y H:i')
                    ->timezone('Asia/Jakarta'),

            ])

            ->filters([

                SelectFilter::make('status_pembayaran')
                    ->options([

                        'lunas'   => 'Lunas',
                        'pending' => 'Pending',
                        'batal'   => 'Batal',

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

            'index'  => Pages\ListPembayarans::route('/'),
            'create' => Pages\CreatePembayaran::route('/create'),
            'edit'   => Pages\EditPembayaran::route('/{record}/edit'),

        ];
    }
}