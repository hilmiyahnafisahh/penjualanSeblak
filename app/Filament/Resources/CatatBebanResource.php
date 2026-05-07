<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CatatBebanResource\Pages;
use App\Filament\Resources\CatatBebanResource\RelationManagers;
use App\Models\CatatBeban;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CatatBebanResource extends Resource
{
    protected static ?string $model = CatatBeban::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
{
    return $form
        ->schema([
            Forms\Components\Select::make('kode_akun')
                ->label('Akun')
                ->relationship('akun', 'nama_akun')
                ->searchable()
                ->required(),

            Forms\Components\DatePicker::make('tanggal')
                ->required(),

            Forms\Components\TextInput::make('jenis_beban')
                ->required(),

            Forms\Components\TextInput::make('total')
                ->numeric()
                ->required(),

            Forms\Components\Textarea::make('keterangan')
                ->nullable(),

            Forms\Components\Select::make('status')
                ->options([
                    'lunas' => 'Lunas',
                    'belum' => 'Belum',
                ])
                ->default('lunas')
                ->required(),
        ]);
}

public static function table(Table $table): Table
{
    return $table
        ->columns([
            Tables\Columns\TextColumn::make('akun.nama_akun')
                ->label('Akun')
                ->searchable(),

            Tables\Columns\TextColumn::make('tanggal')
                ->date(),

            Tables\Columns\TextColumn::make('jenis_beban')
                ->searchable(),

            Tables\Columns\TextColumn::make('total')
                ->money('IDR', true),

            Tables\Columns\TextColumn::make('status')
                ->badge(),

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
        return [
            //
        ];
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
