<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LayananResource\Pages;
use App\Models\Layanan;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Table;

// Form Components
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;

// Table Components
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;

class LayananResource extends Resource
{
    protected static ?string $model = Layanan::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form->schema([
            TextInput::make('id_layanan')
                ->label('Kode Layanan')
                ->default(fn () => Layanan::getIDLayanan())
                ->readonly(), // SESUAI MODEL KAMU

            Select::make('nama_layanan')
                ->required()
                ->options([
                    'Dine In' => 'Dine In',
                    'Take Away' => 'Take Away',
                ]),

            Textarea::make('deskripsi')
                ->required(),

            Select::make('status_layanan')
                ->options([
                    'Tersedia' => 'Tersedia',
                    'Tidak Tersedia' => 'Tidak Tersedia',
                ])
                ->required(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->sortable(),

                TextColumn::make('id_layanan')
                    ->label('Kode')
                    ->searchable(),

                TextColumn::make('nama_layanan')
                    ->badge()
                    ->color(fn (string $state): string => match ($state){
                        'Dine In' => 'primary',
                        'Take Away' => 'secondary',
                    }),

                TextColumn::make('deskripsi')
                    ->limit(30),

                TextColumn::make('status_layanan')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Tersedia' => 'success',
                        'Tidak Tersedia' => 'danger',
                    }),

                TextColumn::make('created_at')
                    ->dateTime(),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                DeleteBulkAction::make(),
            ]);
    }

    // ================= PAGES =================
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLayanans::route('/'),
            'create' => Pages\CreateLayanan::route('/create'),
            'edit' => Pages\EditLayanan::route('/{record}/edit'),
        ];
    }
}