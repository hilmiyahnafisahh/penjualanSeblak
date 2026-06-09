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
    protected static ?string $model = Penggajian::class; // Model yang digunakan resource ini
    protected static ?string $navigationGroup = 'Transaksi';

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';
    protected static ?string $navigationLabel = 'Penggajian'; // Label untuk menu di sidebar
    protected static ?string $modelLabel = 'Penggajian'; // Label untuk model di halaman ui
    protected static function hitungGaji($jam, $upah, $hari): array // Fungsi untuk menghitung gaji berdasarkan jam kerja, upah per jam, dan jumlah hari kehadiran
{
    $gajiPerHari = $jam * $upah; //parameter yang diinputkan user untuk menghitung gaji per hari
    $nominal = $gajiPerHari * $hari; //hasil gaji per hari dikalikan dengan jumlah hari kehadiran untuk mendapatkan nominal total gaji yang akan dibayarkan

    return [ // Mengembalikan hasil perhitungan dalam bentuk array
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
                            TextInput::make('id_penggajian') //namanya id_penggajian karena harus konsisten dengan nama field di database, meskipun nanti akan diisi otomatis oleh fungsi getIDPenggajian() di model Penggajian
                                ->label('ID Penggajian')
                                ->disabled() //agar field ini tidak bisa diedit oleh user karena akan otomatis di-generate
                                ->dehydrated() //agar field ini tetap disimpan ke database meskipun disabled
                                ->default(fn () => Penggajian::getIDPenggajian()) //otomatis generate ID Penggajian berdasarkan fungsi getIDPenggajian() di model Penggajian
                                ->columnSpanFull(), // agar field ini mengambil seluruh lebar kolom
 
                            Select::make('id_karyawan')
                                ->label('Karyawan')
                                ->options( //mengambil data karyawan yang memiliki nama tidak null dan tidak kosong untuk ditampilkan di dropdown
                                    \App\Models\Karyawan::query() //
                                        ->whereNotNull('nama') // memastikan nama tidak null
                                        ->where('nama', '!=', '')// memastikan nama tidak kosong
                                        ->pluck('nama', 'id_karyawan')// mengambil nama sebagai label dan id_karyawan sebagai value untuk dropdown
                                        ->toArray()// mengubah hasil pluck menjadi array biasa
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
                                    ->numeric() // memastikan input hanya angka
                                    ->required()
                                    ->suffix('jam') //menambahkan keterangan "jam" di belakang input
                                    ->minValue(4) // memastikan jam kerja minimal 4 jam per hari
                                    ->live(onBlur: true) // menghitung gaji per hari dan nominal total gaji setiap kali input jam kerja diubah (setelah input kehilangan fokus)
                                    ->afterStateUpdated(function ($state, Forms\Set $set, Forms\Get $get) { //Menjalankan function setelah nilai field berubah
                                    $hasil = self::hitungGaji( //memanggi; fungsi hitungGaji dari array yang sudah dibuat sebelumnya untuk menghitung gaji per hari dan nominal total gaji berdasarkan nilai jam kerja, upah per jam, dan kehadiran yang diinputkan user
                                        floatval($state), //mengambil nilai jam kerja yang baru diinputkan, mengubahnya menjadi float, dan memasukkannya ke fungsi hitungGaji untuk menghitung gaji per hari dan nominal total gaji
                                        floatval($get('upah_per_jam')), //mengambil nilai upah per jam yang sudah diinputkan sebelumnya, mengubahnya menjadi float, dan memasukkannya ke fungsi hitungGaji untuk menghitung gaji per hari dan nominal total gaji
                                        floatval($get('kehadiran')) //mengambil nilai kehadiran yang sudah diinputkan sebelumnya, mengubahnya menjadi float, dan memasukkannya ke fungsi hitungGaji untuk menghitung gaji per hari dan nominal total gaji
                                    );

                                    $set('gaji_per_hari', $hasil['gaji_per_hari']); //mengambil hasil gaji per hari dari fungsi hitungGaji dan memasukkannya ke field gaji_per_hari agar otomatis terisi setelah jam kerja diubah
                                    $set('nominal', $hasil['nominal']);
                                }),

                                TextInput::make('upah_per_jam')
                                    ->label('Upah Per Jam')
                                    ->numeric()
                                    ->required()
                                    ->formatStateUsing(fn (string|int|null $state): string => rupiah($state)) //menggunakan fungsi rupiah dari helper untuk menampilkan format mata uang rupiah saat user menginputkan upah per jam
                                    ->minValue(13000) // memastikan upah per jam minimal sesuai dengan UMK 2024 (13.000 IDR)
                                    ->live(onBlur: true) // menghitung gaji per hari dan nominal total gaji setiap kali input upah per jam diubah (setelah input kehilangan fokus)
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
                                    ->afterStateUpdated(function ($state, Forms\Set $set, Forms\Get $get) { //karena field bisa berubah masing-masing, maka perlu dibuat function afterStateUpdated untuk masing-masing field agar ketika salah satu field diubah, maka gaji per hari dan nominal total gaji akan otomatis dihitung ulang berdasarkan nilai terbaru dari ketiga field (jam kerja, upah per jam, dan kehadiran)

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
                Tables\Actions\EditAction::make(),
                Action::make('bayarGaji')
                    ->label('Bayar Gaji')
                    ->icon('heroicon-o-credit-card')
                    ->color('success')
                    ->url(fn (Penggajian $record): string => route('penggajian.midtrans', ['id' => $record->id])) //mengarahkan ke route penggajian.midtrans dengan parameter id penggajian untuk memproses pembayaran melalui Midtrans
                    ->visible(fn (?Penggajian $record): bool => $record !== null && in_array($record->status, ['Ditangguhkan', 'Pending'])), //tombol bayar gaji hanya akan muncul jika status penggajian adalah Ditangguhkan atau Pending, dan tidak muncul jika status sudah Dibayarkan atau record null
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
                    $penggajian = Penggajian::all(); //mengambil semua data penggajian untuk dimasukkan ke dalam PDF. Bisa disesuaikan jika ingin mengambil data tertentu saja (misal berdasarkan filter atau pagination)

                    $pdf = Pdf::loadView('pdf.penggajian', ['penggajian' => $penggajian]); //menggunakan view resources/views/pdf/penggajian.blade.php untuk membuat tampilan PDF, dan mengirimkan data penggajian ke view tersebut untuk ditampilkan di PDF. Pastikan kamu sudah membuat view ini dengan format yang sesuai untuk laporan penggajian.

                    return response()->streamDownload( //mengembalikan response untuk mengunduh file PDF yang sudah dibuat
                        fn () => print($pdf->output()), //mencetak output PDF yang sudah dibuat ke dalam response stream untuk diunduh
                        'penggajian-list.pdf' //nama file PDF yang akan diunduh. Bisa disesuaikan sesuai kebutuhan (misal menambahkan timestamp agar nama file unik)
                    );
                })
            ])

            ->bulkActions([ // untuk menambahkan tombol aksi massal (bulk action) di tabel, seperti hapus banyak data sekaligus
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
        ];
    }
}
 
