<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BarangResource\Pages;
use App\Filament\Resources\BarangResource\RelationManagers;
use App\Models\Barang;
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

class BarangResource extends Resource
{
    protected static ?string $model = Barang::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('id_barang')
                    ->default(fn () => Barang::getKodeBarang()) // Ambil default dari method getKodeBarang
                    ->label('Kode Barang')
                    ->required()
                    ->readonly(),
                TextInput::make('nama_barang')
                    ->label('Nama Barang')
                    ->placeholder('Masukkan nama barang')
                    ->required(),
                TextInput::make('stok')
                    ->label('Stok')
                    ->placeholder('Masukkan jumlah stok')
                    ->required(),
                TextInput::make('satuan')
                    ->label('Satuan')
                    ->default('pcs')
                    ->readonly(),
                TextInput::make('harga_beli')
                    ->label('Harga Beli')
                    ->placeholder('Masukkan harga beli')
                    ->required(),
                TextInput::make('harga_jual')
                    ->label('Harga Jual')
                    ->placeholder('Masukkan harga jual')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Textcolumn::make('id_barang')
                    ->label('Kode Barang')
                    ->sortable()
                    ->searchable(),
                Textcolumn::make('nama_barang')
                    ->label('Nama Barang')
                    ->sortable()
                    ->searchable(),
                Textcolumn::make('stok')
                    ->label('Stok')
                    ->sortable()
                    ->searchable(),
                Textcolumn::make('satuan')
                    ->label('Satuan'),
                Textcolumn::make('harga_beli')
                    ->label('Harga Barang')
                    ->formatStateUsing(fn (string|int|null $state): string => rupiah($state))
                    ->extraAttributes(['class' => 'text-right']) // Tambahkan kelas CSS untuk rata kanan
                    ->sortable(),
                Textcolumn::make('harga_jual')
                    ->label('Harga Jual')
                    ->formatStateUsing(fn (string|int|null $state): string => rupiah($state))
                    ->extraAttributes(['class' => 'text-right']) // Tambahkan kelas CSS untuk rata kanan
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\ViewAction::make(),
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
            'index' => Pages\ListBarangs::route('/'),
            'create' => Pages\CreateBarang::route('/create'),
            'edit' => Pages\EditBarang::route('/{record}/edit'),
        ];
    }
}
