<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MenuResource\Pages;
use App\Filament\Resources\MenuResource\RelationManagers;
use App\Models\Menu;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

// tambahan untuk komponen input form
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Radio;
// tambahan untuk komponen kolom
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Forms\Components\Grid;

class MenuResource extends Resource
{
    protected static ?string $model = Menu::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                 TextInput::make('id_menu')
                    ->label('ID Menu')
                    ->readonly()
                    ->required(),

                Select::make('kategori_menu')
                    ->options([
                        'Makanan' => 'Makanan',
                        'Minuman' => 'Minuman',
                    ])
                    ->reactive()
                    ->afterStateUpdated(function ($state, callable $set) {
                        if ($state === 'Makanan') {
                            $set('id_menu', Menu::getIDMakanan());
                        }
                    if ($state === 'Minuman') {
                        $set('id_menu', Menu::getIDMinuman());
                    }
                })
                ->required()
                ->label('Kategori'),

            TextInput::make('nama_menu')
                ->required()
                ->label('Nama Menu'),

            TextInput::make('harga_menu')
                ->required()
                ->numeric()
                ->prefix('Rp')
                ->label('Harga'),

            FileUpload::make('gambar_menu')
                ->image()
                ->directory('menu')
                ->label('Gambar'),

            TextInput::make('deskripsi')
                ->label('Deskripsi')
                ->nullable(),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id_menu')
                    ->searchable(),

            TextColumn::make('nama_menu')
                ->searchable()
                ->sortable()
                ->label('Nama Menu'),

            BadgeColumn::make('kategori_menu')
                    ->label('Kategori')
                    ->colors([
                        'Makanan' => 'green',
                        'Minuman' => 'blue',
                    ]),

            TextColumn::make('harga_menu')
                ->money('IDR')
                ->sortable(),


            ImageColumn::make('gambar_menu')
                ->label('Gambar')
                ->size(35),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
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
            'index' => Pages\ListMenus::route('/'),
            'create' => Pages\CreateMenu::route('/create'),
            'edit' => Pages\EditMenu::route('/{record}/edit'),
        ];
    }
}
