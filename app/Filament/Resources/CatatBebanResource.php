<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CatatBebanResource\Pages;
use App\Models\CatatBeban;

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

use Filament\Tables\Columns\ImageColumn;

use Barryvdh\DomPDF\Facade\Pdf;

class CatatBebanResource extends Resource
{
    protected static ?string $model = CatatBeban::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';
    protected static ?string $navigationGroup = '💵 Transaksi';
    protected static ?string $maxContentWidth = 'full';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([

                Wizard::make([

                    Wizard\Step::make('Data Beban')
                        ->icon('heroicon-o-document-text')
                        ->schema([

                            Section::make('Informasi Beban')
                                ->schema([

                                    Select::make('kode_akun')
                                        ->label('Kategori Beban')
                                        ->relationship(
                                            'akun',
                                            'nama_akun'
                                        )
                                        ->searchable()
                                        ->required(),

                                    DatePicker::make('tanggal')
                                        ->required(),

                                    TextInput::make('jenis_beban')
                                        ->required(),

                                ])
                                ->columns(3),

                        ]),

                    Wizard\Step::make('Detail')
                        ->icon('heroicon-o-document')
                        ->schema([

                            Section::make('Detail Beban')
                                ->schema([

                                    Textarea::make('keterangan'),

                                    FileUpload::make('gambar')
                                        ->label('Gambar Bukti Tagihan')
                                        ->image()
                                        ->imageEditor()
                                        ->directory('beban')
                                        ->disk('public')
                                        ->visibility('public')
                                        ->maxSize(2048)
                                        ->nullable(),

                                    TextInput::make('total')
                                        ->numeric()
                                        ->required(),

                                ])
                                ->columns(3),

                        ]),

                    Wizard\Step::make('Status')
                        ->icon('heroicon-o-check-circle')
                        ->schema([

                            Section::make('Status Beban')
                                ->schema([

                                    Select::make('status')
                                        ->options([
                                            'lunas' => 'Lunas',
                                            'belum lunas' => 'Belum Lunas',
                                        ])
                                        ->default('lunas')
                                        ->required(),

                                ])
                                ->columns(1),

                        ]),

                ])
                    ->skippable(false)
                    ->persistStepInQueryString()
                    ->columnSpanFull(),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table

            ->columns([

                Tables\Columns\TextColumn::make('akun.nama_akun')
                    ->label('Kategori Beban')
                    ->searchable(),

                ImageColumn::make('gambar')
                    ->label('Gambar')
                    ->disk('public')
                    ->square()
                    ->defaultImageUrl(
                        url('/images/placeholder.png')
                    ),

                Tables\Columns\TextColumn::make('tanggal')
                    ->date(),

                Tables\Columns\TextColumn::make('jenis_beban')
                    ->searchable(),

                Tables\Columns\TextColumn::make('total')
                    ->formatStateUsing(
                        fn($state) =>
                        'Rp ' . number_format(
                            $state,
                            0,
                            ',',
                            '.'
                        )
                    ),

                // STATUS BAGUS (BADGE)
                Tables\Columns\TextColumn::make('status')

                    ->badge()

                    ->formatStateUsing(
                        fn($state) =>
                        $state === 'lunas'
                        ? 'Lunas'
                        : 'Belum Lunas'
                    )

                    ->color(
                        fn($state) =>
                        match ($state) {
                            'lunas' => 'success',
                            'belum lunas' => 'danger',
                            default => 'gray',
                        }
                    ),

            ])

            ->actions([

                Tables\Actions\ViewAction::make(),

                Tables\Actions\EditAction::make(),

                // BUTTON UBAH STATUS
                Tables\Actions\Action::make('ubahStatus')

                    ->label('Ubah Status')

                    ->icon('heroicon-o-pencil-square')

                    ->color('warning')

                    ->fillForm(fn($record) => [
                        'status' => $record->status,
                    ])

                    ->form([

                        Select::make('status')
                            ->label('Pilih Status')

                            ->options([
                                'lunas' => 'Lunas',
                                'belum lunas' => 'Belum Lunas',
                            ])

                            ->required(),

                    ])

                    ->action(function (
                        CatatBeban $record,
                        array $data
                    ) {

                        $record->update([
                            'status' => $data['status'],
                        ]);

                    }),

                Tables\Actions\DeleteAction::make(),

            ])

            ->bulkActions([

                Tables\Actions\BulkAction::make(
                    'download_pdf'
                )

                    ->label('Download PDF')

                    ->icon('heroicon-o-document')

                    ->color('success')

                    ->action(function ($records) {

                        $pdf = Pdf::loadView(
                            'pdf.beban',
                            [
                                'data' => $records
                            ]
                        );

                        return response()
                            ->streamDownload(

                                fn() =>
                                print(
                                    $pdf->output()
                                ),

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

            'index' =>
            Pages\ListCatatBebans::route('/'),

            'create' =>
            Pages\CreateCatatBeban::route('/create'),

            'edit' =>
            Pages\EditCatatBeban::route('/{record}/edit'),

            'view' =>
            Pages\ViewCatatBeban::route('/{record}'),

        ];
    }
}