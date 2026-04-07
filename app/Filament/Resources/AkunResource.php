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


// tambahan
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\FileUpload; //untuk tipe file

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;

class AkunResource extends Resource
{
    protected static ?string $model = Akun::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('kode_akun')
                    ->default(fn () => Akun::getKodeAkun()) // Ambil default dari method getKodeAkun
                    ->label('Kode Akun')
                    ->required()
                    ->readonly(),
                TextInput::make('nama_akun')
                    ->label('Nama Akun')
                    ->placeholder('Masukkan nama akun')
                    ->required(),
                Select::make('jenis_akun')
                    ->label('Jenis Akun')
                    ->options([
                        'Asset' => 'Asset',
                        'Hutang' => 'Hutang',
                        'Modal' => 'Modal',
                        'Pendapatan' => 'Pendapatan',
                        'Biaya' => 'Biaya',
                    ])
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('kode_akun')->label('Kode Akun')->sortable()->searchable(),
                TextColumn::make('nama_akun')->label('Nama Akun')->sortable()->searchable(),
                TextColumn::make('jenis_akun')->label('Jenis Akun')->sortable()->searchable(),  
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
