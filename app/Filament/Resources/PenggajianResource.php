<?php
 
namespace App\Filament\Resources;
 
use App\Filament\Resources\PenggajianResource\Pages;
use App\Filament\Resources\PenggajianResource\RelationManagers;
use App\Models\Penggajian;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Components\Wizard;
use Filament\Resources\Resource;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\TextColumn;
// BadgeColumn removed (deprecated in Filament v3, use TextColumn->badge() instead)
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\DB;
use App\Models\Karyawan;

// tambahan untuk tombol unduh pdf
use Filament\Tables\Actions\Action; //untuk dapat menggunakan action
use Barryvdh\DomPDF\Facade\Pdf; // Kalau kamu pakai DomPDF
use Illuminate\Support\Facades\Storage; //untuk menyimpan file PDF ke storage
 
class PenggajianResource extends Resource
{
    protected static ?string $model = Penggajian::class;
    protected static ?string $navigationGroup = '💵 Transaksi';

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';
    protected static ?string $navigationLabel = 'Penggajian'; 
    protected static ?string $modelLabel = 'Penggajian'; 
    protected static function hitungGaji($jam, $upah, $hari): array 
    {
    $gajiPerHari = $jam * $upah; 
    $nominal = $gajiPerHari * $hari; 

    return [ 
        'gaji_per_hari' => $gajiPerHari,
        'nominal' => $nominal,
    ];
}
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Wizard::make([
 
                    // ─── STEP 1: Data Karyawan ───────────────────────────//
                    Wizard\Step::make('Data Karyawan')
                        ->description('Pilih karyawan yang akan digaji')
                        ->icon('heroicon-o-user')
                        ->schema([
                            TextInput::make('id_penggajian') 
                                ->label('ID Penggajian')
                                ->disabled() 
                                ->dehydrated() 
                                ->default(fn () => Penggajian::getIDPenggajian()) 
                                ->columnSpanFull(), 
 
                            Select::make('id_karyawan')
                                ->label('Karyawan')
                                ->options( 
                                    \App\Models\Karyawan::query() 
                                        ->whereNotNull('nama') 
                                        ->where('nama', '!=', '')
                                        ->pluck('nama', 'id_karyawan')
                                        ->toArray()
                                )
                                ->searchable()
                                ->required()
                                ->columnSpanFull(),
                            DatePicker::make('tanggal_penggajian')
                                ->label('Tanggal Penggajian')
                                ->required()
                                ->default(now())
                                ->columnSpanFull(),
                        ]),
 
                    // ─── STEP 2: Detail Jam & Upah ───────────────────────
                        Wizard\Step::make('Jam Kerja & Upah')
                            ->description('Masukkan detail jam kerja dan upah per jam')
                            ->icon('heroicon-o-clock')
                            ->schema([

                                TextInput::make('jam_kerja')
                                    ->label('Jam Kerja Per Hari')
                                    ->numeric() 
                                    ->required()
                                    ->suffix('jam') 
                                    ->minValue(4) 
                                    ->live(onBlur: true) 
                                    ->afterStateUpdated(function ($state, Forms\Set $set, Forms\Get $get) { 
                                    $hasil = self::hitungGaji( 
                                        floatval($state), 
                                        floatval($get('upah_per_jam')), 
                                        floatval($get('kehadiran')) 
                                    );

                                    $set('gaji_per_hari', $hasil['gaji_per_hari']); //mengambil hasil gaji per hari dari fungsi hitungGaji dan memasukkannya ke field gaji_per_hari agar otomatis terisi setelah jam kerja diubah
                                    $set('nominal', $hasil['nominal']);
                                }),

                                TextInput::make('upah_per_jam')
                                    ->label('Upah Per Jam')
                                    ->numeric()
                                    ->required()
                                    ->formatStateUsing(fn (string|int|null $state): string => rupiah($state)) 
                                    ->minValue(13000) 
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function ($state, Forms\Set $set, Forms\Get $get) {

                                    $hasil = self::hitungGaji(
                                        floatval($get('jam_kerja')),
                                        floatval($state), 
                                        floatval($get('kehadiran'))
                                    );

                                    $set('gaji_per_hari', $hasil['gaji_per_hari']);
                                    $set('nominal', $hasil['nominal']);
                                }),

                                TextInput::make('kehadiran')
                                    ->label('Jumlah Kehadiran')
                                    ->numeric()
                                    ->default(24)
                                    ->required()
                                    ->suffix('hari')
                                    ->minValue(20)   
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function ($state, Forms\Set $set, Forms\Get $get) { 
                                    $hasil = self::hitungGaji(
                                        floatval($get('jam_kerja')),
                                        floatval($get('upah_per_jam')),
                                        floatval($state)
                                    );

                                    $set('gaji_per_hari', $hasil['gaji_per_hari']);
                                    $set('nominal', $hasil['nominal']);
                                }),

                                TextInput::make('gaji_per_hari')
                                    ->label('Gaji Per Hari')
                                    ->numeric()
                                    ->formatStateUsing(fn (string|int|null $state): string => rupiah($state))
                                    ->readOnly()
                                    ->dehydrated(),

                                Select::make('periode')
                                    ->label('Periode Gaji')
                                    ->options([
                                        'Januari' => 'Januari',
                                        'Februari' => 'Februari',
                                        'Maret' => 'Maret',
                                        'April' => 'April',
                                        'Mei' => 'Mei',
                                        'Juni' => 'Juni',
                                        'Juli' => 'Juli',
                                        'Agustus' => 'Agustus',
                                        'September' => 'September',
                                        'Oktober' => 'Oktober',
                                        'November' => 'November',
                                        'Desember' => 'Desember',
                                    ])
                                    ->required(),

                            ])
                            ->columns(2),
 
                    // ─── STEP 3: Konfirmasi & Status ─────────────────────
                    Wizard\Step::make('Konfirmasi')
                        ->description('Cek nominal dan tentukan status pembayaran')
                        ->icon('heroicon-o-check-circle')
                        ->schema([
                            TextInput::make('nominal')
                                ->label('Total Nominal Gaji')
                                ->numeric()
                                ->required()
                                ->formatStateUsing(fn (string|int|null $state): string => rupiah($state))
                                ->readOnly(),
 
                            Select::make('status')
                                ->label('Status Pembayaran')
                                ->options([
                                    'Ditangguhkan' => 'Ditangguhkan',
                                    'Dibayarkan'   => 'Dibayarkan',
                                ])
                                ->required()
                                ->default('Ditangguhkan'),
                        ])
                        ->columns(2),
 
                ])
                ->columnSpanFull()
                ->skippable(false), // tidak bisa skip step
            ]);
    }
 
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id_penggajian')
                    ->label('ID Penggajian')
                    ->sortable(),
                TextColumn::make('karyawan.nama')
                    ->label('Karyawan')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('tanggal_penggajian')
                    ->label('Tanggal')
                    ->date('d M Y')
                    ->sortable(),
                TextColumn::make('periode')
                    ->label('Periode'),
                TextColumn::make('jam_kerja')
                    ->label('Jam Kerja')
                    ->suffix(' jam'),
                TextColumn::make('upah_per_jam')
                    ->label('Upah/Jam')
                    ->money('IDR'),
                TextColumn::make('gaji_per_hari')
                    ->label('Gaji/Hari')
                    ->money('IDR'),
                TextColumn::make('kehadiran')
                    ->label('Kehadiran')
                    ->suffix(' hari'),
                TextColumn::make('nominal')
                    ->label('Total Gaji')
                    ->money('IDR')
                    ->sortable(),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Dibayarkan'   => 'success',
                        'Ditangguhkan' => 'warning',
                        default        => 'gray',
                    }),
                
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->label('Struk')
                    ->icon('heroicon-o-document-text')
                    ->color('info'),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])

            // tombol tambahan
            ->headerActions([
                // tombol tambahan export pdf
                // ✅ Tombol Unduh PDF
                Action::make('downloadPdf')
                ->label('Unduh PDF')
                ->icon('heroicon-o-document-arrow-down')
                ->color('success')
                ->action(function () {
                    $penggajian = Penggajian::all(); 
                    $pdf = Pdf::loadView('pdf.penggajian', ['penggajian' => $penggajian]); 
                    return response()->streamDownload( 
                        fn () => print($pdf->output()), 
                        'penggajian-list.pdf' 
                    );
                })
            ])

            ->bulkActions([ 
                Tables\Actions\BulkActionGroup::make([
                Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
 
    public static function getRelations(): array
    {
        return [];
    }
 
    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListPenggajians::route('/'),
            'create' => Pages\CreatePenggajian::route('/create'),
            'edit'   => Pages\EditPenggajian::route('/{record}/edit'),
            'view'   => Pages\ViewPenggajian::route('/{record}'),
        ];
    }
}
 
