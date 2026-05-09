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

class CatatBebanResource extends Resource
{
    protected static ?string $model = CatatBeban::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';

    // biar full lebar seperti pembelian
    protected static ?string $maxContentWidth = 'full';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Wizard::make([

                    // ✅ STEP 1
                    Wizard\Step::make('Data Beban')
                        ->schema([
                            Section::make('Informasi Beban')
                                ->schema([
                                    Select::make('kode_akun')
                                        ->label('Kategori Beban')
                                        ->relationship('akun', 'nama_akun')
                                        ->searchable()
                                        ->required(),

                                    DatePicker::make('tanggal')
                                        ->label('Tanggal')
                                        ->required(),

                                    TextInput::make('jenis_beban')
                                        ->label('Jenis Beban')
                                        ->required(),
                                ])
                                ->columns(3),
                        ]),

                    // ✅ STEP 2
                    Wizard\Step::make('Detail')
                        ->schema([
                            Section::make('Detail Beban')
                                ->schema([
                                    Textarea::make('keterangan')
                                        ->label('Keterangan')
                                        ->nullable(),

                                    FileUpload::make('gambar')
                                        ->label('Bukti Tagihan')
                                        ->image()
                                        ->directory('beban')
                                        ->nullable(),

                                    TextInput::make('total')
                                        ->label('Total')
                                        ->numeric()
                                        ->required(),
                                ])
                                ->columns(3),
                        ]),

                    // ✅ STEP 3
                    Wizard\Step::make('Status')
                        ->schema([
                            Section::make('Status Pembayaran')
                                ->schema([
                                    Select::make('status')
                                        ->label('Status')
                                        ->options([
                                            'lunas' => 'Lunas',
                                            'belum lunas' => 'Belum Lunas',
                                        ])
                                        ->default('lunas')
                                        ->required(),
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

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
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
        ];
    }
}