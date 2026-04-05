<?php

namespace App\Filament\Resources;

use App\Filament\Resources\KaryawanResource\Pages;
use App\Filament\Resources\KaryawanResource\RelationManagers;
use App\Models\Karyawan;
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
use Filament\Forms\Components\FileUpload;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;//untuk tipe file

class KaryawanResource extends Resource
{
    protected static ?string $model = Karyawan::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('id_karyawan')
                    ->default(fn () => Karyawan::getIDKaryawan()) // SESUAI MODEL KAMU
                    ->label('ID Karyawan')
                    ->required()
                    ->readonly(),

                TextInput::make('nama')
                    ->label('Nama')
                    ->required(),

                TextInput::make('alamat')
                    ->label('Alamat')
                    ->required(),

                TextInput::make('no_telepon')
                    ->label('No Telepon')
                    ->required(),

                Select::make('status_karyawan')
                    ->label('Status Karyawan')
                    ->options([
                        'aktif' => 'Aktif',
                        'tidak_aktif' => 'Tidak Aktif',
                        'cuti' => 'Cuti',
                        'resign' => 'Resign',
                    ])
                    ->required(),

                Select::make('jabatan')
                    ->label('Jabatan')
                    ->options([
                        'kasir' => 'Kasir',
                        'koki' => 'Koki',
                        'admin' => 'Admin',
                        'owner' => 'Owner',
                    ])
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id_karyawan')
                    ->label('ID Karyawan')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('nama')
                    ->label('Nama')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('alamat')
                    ->label('Alamat'),

                TextColumn::make('no_telepon')
                    ->label('No Telepon'),

                TextColumn::make('status_karyawan')
                    ->label('Status')
                    ->badge()
                    ->color(fn ($state) => $state === 'aktif' ? 'success' : 'danger'),

                TextColumn::make('jabatan')
                    ->label('Jabatan')
                    ->badge(),
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
            'index' => Pages\ListKaryawans::route('/'),
            'create' => Pages\CreateKaryawan::route('/create'),
            'edit' => Pages\EditKaryawan::route('/{record}/edit'),
        ];
    }
}
