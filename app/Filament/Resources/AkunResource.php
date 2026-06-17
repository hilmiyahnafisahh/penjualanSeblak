<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AkunResource\Pages;
use App\Filament\Resources\AkunResource\RelationManagers;
use App\Models\Akun; // Pastikan menggunakan huruf kapital 'A'
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\DB; 

// tambahan
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\FileUpload; 

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;

class AkunResource extends Resource
{
    protected static ?string $model = Akun::class;

    protected static ?string $modelLabel = 'Akun';
    protected static ?string $pluralModelLabel = 'Data Akun';

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';

    protected static ?string $navigationGroup = '📦 MASTER DATA';
    
    protected static ?int $navigationSort = 5;
    // ------------------------------------

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('jenis_akun')
                    ->label('Jenis Akun')
                    ->options([
                        '1' => 'Harta',
                        '2' => 'Kewajiban',
                        '3' => 'Modal',
                        '4' => 'Pendapatan',
                        '5' => 'Beban',
                    ])
                    ->live()
                    ->required()
                    ->afterStateUpdated(function ($state, callable $set) {
                        $kode = Akun::getKodeAkun($state);
                        $set('kode_akun', $kode);
                    }),

                TextInput::make('kode_akun')
                    ->label('Kode Akun')
                    ->readonly()
                    ->required(),
                TextInput::make('nama_akun')
                    ->label('Nama Akun')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('kode_akun', 'asc')
            ->columns([
                // Teks bersusun: Nama Akun tebal, Kode Akun di bawahnya
                TextColumn::make('nama_akun')
                    ->label('INFORMASI AKUN')
                    ->weight('bold')
                    ->searchable()
                    ->description(fn ($record): string => 'Kode Akun: ' . $record->kode_akun),

                // Mengubah angka klasifikasi menjadi Badge berwarna
                TextColumn::make('jenis_akun')
                    ->label('KLASIFIKASI')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        '1' => 'Harta',
                        '2' => 'Kewajiban',
                        '3' => 'Modal',
                        '4' => 'Pendapatan',
                        '5' => 'Beban',
                        default => 'Tidak Diketahui',
                    })
                    ->color(fn (string $state): string => match ($state) {
                        '1' => 'info',
                        '2' => 'warning',
                        '3' => 'success',
                        '4' => 'primary', // Oranye sesuai tema
                        '5' => 'danger',
                        default => 'gray',
                    })
                    ->searchable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                // Tombol aksi diubah menjadi kotak solid berwarna
                Tables\Actions\EditAction::make()
                    ->button()
                    ->color('warning'),
                Tables\Actions\DeleteAction::make()
                    ->button()
                    ->color('danger'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAkuns::route('/'),
            'create' => Pages\CreateAkun::route('/create'),
            'edit' => Pages\EditAkun::route('/{record}/edit'),
        ];
    }
}