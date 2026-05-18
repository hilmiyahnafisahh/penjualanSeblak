<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CatatBebanResource\Pages;
use App\Models\CatatBeban;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

use Filament\Forms\Components\Wizard;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\FileUpload;

use Barryvdh\DomPDF\Facade\Pdf;

class CatatBebanResource extends Resource
{
    protected static ?string $model = CatatBeban::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';
    protected static ?string $navigationGroup = 'Transaksi';
    protected static ?string $maxContentWidth = 'full';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Wizard::make([

                    // STEP 1
                    Wizard\Step::make('Data Beban')
                        ->schema([
                            Section::make('Informasi Beban')
                                ->schema([
                                    Select::make('kode_akun')
                                        ->label('Kategori Beban')
                                        ->relationship('akun', 'nama_akun') //mengambil nama_akun dari tabel akun
                                        ->searchable()
                                        ->required(),

                                    DatePicker::make('tanggal')
                                        ->required(),

                                    TextInput::make('jenis_beban')
                                        ->required(),
                                ])
                                ->columns(3),
                        ]),

                    // STEP 2
                    Wizard\Step::make('Detail')
                        ->schema([
                            Section::make('Detail Beban')
                                ->schema([
                                    Textarea::make('keterangan'),

                                    FileUpload::make('gambar')
                                        ->image()
                                        ->disk('public') // ← TAMBAHKAN DI SINI
                                        ->directory('beban'),

                                    TextInput::make('total')
                                        ->numeric()
                                        ->required(),
                                ])
                                ->columns(3),
                        ]),

                    // STEP 3
                    Wizard\Step::make('Status')
                        ->schema([
                            Section::make('Status Pembayaran')
                                ->schema([
                                    Select::make('status')
                                        ->options([
                                            'lunas' => 'Lunas',
                                            'belum lunas' => 'Belum Lunas',
                                        ])
                                        ->default('lunas')
                                        ->required(),

                                    // tombol bayar
                                    Forms\Components\Actions::make([
                                        Forms\Components\Actions\Action::make('bayar')
                                            ->label('Bayar Sekarang')
                                            ->color('success')
                                            ->icon('heroicon-o-credit-card')
                                            ->url(fn () => url('/bayar-beban/' . request()->route('record')))
                                            ->openUrlInNewTab()
                                            ->visible(fn () => request()->route('record') !== null),
                                    ]),
                                ])
                                ->columns(1),
                        ]),
                ])->columnSpan(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('akun.nama_akun')
                    ->label('Kategori Beban')
                    ->searchable(),

                Tables\Columns\ImageColumn::make('gambar')
                    ->label('Bukti Tagihan'),

                Tables\Columns\TextColumn::make('tanggal')
                    ->date(),

                Tables\Columns\TextColumn::make('jenis_beban')
                    ->searchable(),

                Tables\Columns\TextColumn::make('total')
                    ->formatStateUsing(fn ($state) => 'Rp ' . number_format($state, 0, ',', '.')),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->colors([
                        'success' => 'lunas',
                        'danger' => 'belum lunas',
                    ]),
            ])

            ->actions([

                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\Action::make('bayar')
                    ->label('Bayar')
                    ->icon('heroicon-o-credit-card')
                    ->color('success')
                    ->url(fn ($record) => url('/bayar-beban/' . $record->id_beban))
                    ->openUrlInNewTab()
                    ->visible(fn ($record) => $record->status !== 'lunas'),
            ])

            // ✅ BULK PDF (CHECKLIST)
            ->bulkActions([
                Tables\Actions\BulkAction::make('download_pdf')
                    ->label('Download PDF')
                    ->icon('heroicon-o-document')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(function ($records) {

                        $pdf = Pdf::loadView('pdf.beban', [
                            'data' => $records
                        ]);

                        return response()->streamDownload(
                            fn () => print($pdf->output()),
                            'laporan-beban.pdf'
                        );
                    }),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCatatBebans::route('/'),
            'create' => Pages\CreateCatatBeban::route('/create'),
            'edit' => Pages\EditCatatBeban::route('/{record}/edit'),
            'view' => Pages\ViewCatatBeban::route('/{record}'),
        ];
    }
}