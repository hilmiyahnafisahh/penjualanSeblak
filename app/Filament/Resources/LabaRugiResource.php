<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LabaRugiResource\Pages;
use App\Models\JurnalDetail;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Table;

class LabaRugiResource extends Resource
{
    // Pakai JurnalDetail sebagai model (data laba rugi dihitung dari sini)
    protected static ?string $model = JurnalDetail::class;

    protected static ?string $navigationIcon   = 'heroicon-o-document-text';
    protected static ?string $navigationGroup  = 'Laporan';
    protected static ?string $navigationLabel  = 'Laba Rugi';
    protected static ?string $slug             = 'laporan-laba-rugi';
    protected static ?string $modelLabel       = 'Laba Rugi';
    protected static ?string $pluralModelLabel = 'Laba Rugi';
    protected static ?string $breadcrumb       = 'Laba Rugi';

    public static function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([])
            ->filters([])
            ->actions([])
            ->bulkActions([])
            ->paginated(false);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLabaRugis::route('/'),
        ];
    }
}
