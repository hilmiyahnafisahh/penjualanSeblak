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
use Filament\Tables\Actions\Action;
use Barryvdh\DomPDF\Facade\Pdf;

use Illuminate\Support\HtmlString;

class PembayaranResource extends Resource
{
    protected static ?string $model = Pembayaran::class;

    protected static ?string $navigationIcon = 'heroicon-o-credit-card';

    protected static ?string $navigationGroup = '💵 Transaksi';

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
                                        ->required()
                                        ->readOnly()
                                        ->dehydrated()
                                        ->afterStateHydrated(function ($state, callable $set) {

                                    if (!$state) {

                                            $set(
                                                    'id_pembayaran',
                                                    Pembayaran::getKodePembayaran()
                                        );
                                        }
                                    }),

                                    DateTimePicker::make('tanggal_pembayaran')
                                        ->label('Tanggal Pembayaran')
                                        ->default(fn () => now('Asia/Jakarta')->format('Y-m-d H:i:s'))
                                        ->timezone('Asia/Jakarta')
                                        ->required()
                                        ->afterStateHydrated(function ($state, callable $set) {
                                            if (!$state) {
                                                $set('tanggal_pembayaran', now('Asia/Jakarta')->format('Y-m-d H:i:s'));
                                            }
                                        }),

                                    Select::make('id_pemesanan')
                                        ->label('Pilih Pesanan')
                                        ->default(fn () => request()->query('id_pemesanan'))
                                        ->options(
                                            Pemesanan::whereDoesntHave('pembayaran')
                                                ->where('status_pemesanan', 'diproses')
                                                ->pluck('id_pesanan', 'id')
                                        )
                                        ->getOptionLabelUsing(fn ($value) => Pemesanan::find($value)?->id_pesanan)
                                        ->searchable()
                                        ->required()
                                        ->reactive()

                                        ->afterStateUpdated(function ($state, Set $set) {

                                            $pesanan = Pemesanan::find($state);

                                            if ($pesanan) {

                                                $set('total_pembayaran', $pesanan->subtotal);
                                            }
                                        }),

                                    TextInput::make('total_pembayaran')
                                        ->label('Total Pembayaran')
                                        ->numeric()
                                        ->prefix('Rp')
                                        ->readonly()
                                        ->required(),

                                    Select::make('metode_pembayaran')
                                        ->label('Metode Pembayaran')
                                        ->options([
                                            'cash'     => 'Cash',
                                            'qris'     => 'QRIS',
                                            'transfer' => 'Transfer',
                                        ])
                                        ->required()
                                        ->reactive()
                                        ->afterStateUpdated(function ($state, Set $set) {
                                            if (in_array($state, ['qris', 'transfer'])) {
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
                                                <strong>No Pesanan:</strong>
                                                ' . optional(Pemesanan::find($get('id_pemesanan')))->id_pesanan . '
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
            ->headerActions([
                Action::make('download_all_invoices')
                    ->label('Unduh PDF')
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('success')
                    ->action(function () {
                        $pembayaran = Pembayaran::with('pemesanan.DetailPesanan.menu', 'pemesanan.pelanggan')->get();

                        $pdf = Pdf::loadView('pdf.invoice_pembayaran_semua', [
                            'pembayarans' => $pembayaran,
                        ])->setPaper('A4', 'portrait');

                        return response()->streamDownload(
                            fn () => print($pdf->output()),
                            'unduh-pdf-pembayaran.pdf'
                        );
                    }),
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