<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AkunResource\Pages;
use App\Filament\Resources\AkunResource\RelationManagers;
use App\Models\Akun;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\DB; // Tambahkan ini untuk menggunakan DB facade

// tambahan
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\FileUpload; //untuk tipe file

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;

class AkunResource extends Resource
{
    protected static ?string $model = akun::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Master Data';

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
                TextColumn::make('kode_akun')
                    ->searchable(),
                TextColumn::make('nama_akun')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('jenis_akun')
                    ->sortable()
                    ->searchable(),
            ])
            ->filters([
                //
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
