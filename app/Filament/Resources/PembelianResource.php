<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PembelianResource\Pages;
use App\Models\Pembelian;
use App\Models\Barang;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\Wizard;
use Filament\Forms\Components\Wizard\Step;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Actions;
use Filament\Forms\Components\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;


class PembelianResource extends Resource
{
    protected static ?string $model = Pembelian::class;
    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';
    protected static ?string $navigationLabel = 'Pembelian';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Wizard::make([
                    // STEP 1: INFORMASI TRANSAKSI
                    Step::make('Data Pembelian')
                        ->schema([
                            Section::make('Informasi Utama')
                                ->schema([
                                    TextInput::make('id_pembelian')
                                        ->default(fn () => Pembelian::getKodeFakturBeli())
                                        ->required()
                                        ->readonly(),
                                    DateTimePicker::make('tgl')
                                        ->default(now())
                                        ->required(),
                                    Select::make('karyawan_id')
                                        ->relationship('karyawan', 'nama')
                                        ->searchable()
                                        ->preload()
                                        ->required(),
                                    
                                    Hidden::make('total_bayar')->default(0),
                                    Hidden::make('tagihan')->default(0),
                                    Hidden::make('status')->default('pending'),
                                ])->columns(3),
                        ]),

                    // STEP 2: DETAIL ITEM
                    Step::make('Item Barang')
                        ->schema([
                            Repeater::make('barang') 
                                ->relationship()
                                ->minItems(1)
                                ->schema([
                                    Select::make('id_barang')
                                        ->label('Barang')
                                        ->options(Barang::pluck('nama_barang', 'id_barang')->toArray())
                                        ->required()
                                        ->searchable()
                                        ->preload()
                                        ->optionsLimit(5)
                                        ->live()
                                        ->afterStateUpdated(function ($state, Forms\Set $set) {
                                            $barang = Barang::where('id_barang', $state)->first();
                                            if ($barang) {
                                                $set('harga_beli', $barang->harga_beli);
                                            }
                                        })
                                        ->createOptionForm([
                                            TextInput::make('nama_barang')->required(),
                                            TextInput::make('harga_beli')->numeric()->required(),
                                            TextInput::make('harga_jual')->numeric()->required(),
                                            TextInput::make('stok')->numeric()->default(0),
                                            TextInput::make('satuan')->required(),
                                        ])
                                        ->createOptionUsing(function (array $data) {
                                            $last = Barang::orderBy('id_barang', 'desc')->first();
                                            $lastNumber = $last ? (int) preg_replace('/[^0-9]/', '', $last->id_barang) : 0;
                                            $data['id_barang'] = 'BRG' . str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);

                                            $newBarang = Barang::create($data);
                                            return $newBarang->id_barang;
                                        }),

                                    TextInput::make('harga_beli')
                                        ->label('Harga Satuan')
                                        ->numeric()
                                        ->prefix('Rp')
                                        ->required()
                                        ->live()
                                        ->dehydrated(),

                                    TextInput::make('jumlah')
                                        ->numeric()
                                        ->default(1)
                                        ->required()
                                        ->live(),

                                    DatePicker::make('tgl')
                                        ->default(today())
                                        ->required(),
                                ])
                                ->columns(4),

                            Actions::make([
                                Action::make('konfirmasi_pembelian')
                                    ->label('Hitung Total')
                                    ->color('success')
                                    ->icon('heroicon-m-calculator')
                                    ->form([
                                        Select::make('status_bayar')
                                            ->options(['lunas' => 'Lunas', 'hutang' => 'Hutang'])
                                            ->required(),
                                    ])
                                    ->action(function ($get, $set, $data) {
                                        $items = $get('barang') ?? [];
                                        $total = 0;
                                        foreach ($items as $item) {
                                            if (!empty($item['id_barang'])) {
                                                $total += ($item['harga_beli'] * $item['jumlah']);
                                            }
                                        }
                                        
                                        $set('status', $data['status_bayar']);
                                        if ($data['status_bayar'] === 'lunas') {
                                            $set('total_bayar', $total);
                                            $set('tagihan', 0);
                                        } else {
                                            $set('total_bayar', 0);
                                            $set('tagihan', $total);
                                        }
                                    }),
                            ]),
                        ]),

                    Step::make('Selesai')
                        ->schema([
                            Placeholder::make('info')
                                ->content('Klik "Create" untuk simpan data.'),
                        ]),
                ])
                ->columnSpanFull()
                ->skippable(false)
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('id_pembelian')->label('Faktur'),
            TextColumn::make('karyawan.nama')->label('Karyawan'),
            BadgeColumn::make('status')
                ->colors([
                    'success' => 'lunas',
                    'warning' => 'hutang',
                    'gray'    => 'pending',
                ]),
            TextColumn::make('total_bayar')->money('IDR'),
            TextColumn::make('tagihan')->money('IDR'),
        ])
        ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
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
            'index' => Pages\ListPembelians::route('/'),
            'create' => Pages\CreatePembelian::route('/create'),
            'edit' => Pages\EditPembelian::route('/{record}/edit'),
        ];
    }
}