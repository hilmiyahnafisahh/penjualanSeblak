<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CashflowResource\Pages;
use App\Models\Cashflow;
use Filament\Forms\Form;
use Filament\Forms;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\Section as InfoSection;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Filament\Tables;
use Filament\Infolists\Components\ViewEntry;

class CashflowResource extends Resource
{
    protected static ?string $model = Cashflow::class;

    protected static ?string $navigationIcon  = 'heroicon-o-sparkles';
    protected static ?string $navigationLabel = 'Cashflow';
    protected static ?string $navigationGroup = 'Laporan';
    protected static ?string $modelLabel      = 'Analisa Arus Kas';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('periode')->disabled(),
            Forms\Components\TextInput::make('status_kesehatan')->disabled(),
        ]);
    }

    // INFOLIST -> tampilan hasil AI langsung di halaman detail (View)
    public static function infolist(Infolist $infolist): Infolist
{
    return $infolist->schema([
        InfoSection::make('Ringkasan')
            ->columns(2)
            ->schema([
                TextEntry::make('periode'),
                TextEntry::make('status_kesehatan')
                    ->badge()
                    ->color(fn (?string $state) => match ($state) {
                        'Sehat'   => 'success',
                        'Waspada' => 'warning',
                        'Kritis'  => 'danger',
                        default   => 'gray',
                    }),
            ]),

        InfoSection::make('Laporan Arus Kas')
            ->schema([
                ViewEntry::make('laporan')
                    ->hiddenLabel()
                    ->view('filament.infolists.cashflow-statement'),
            ]),

        InfoSection::make('Analisa AI')
            ->icon('heroicon-m-sparkles')
            ->schema([
                TextEntry::make('ringkasan'),
                TextEntry::make('rekomendasi')
                    ->label('Rekomendasi Tindakan')
                    ->listWithLineBreaks()
                    ->bulleted(),
                TextEntry::make('proyeksi')->label('Proyeksi Bulan Depan'),
            ]),
    ]);
}

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('periode')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('total_masuk')->money('IDR')->label('Kas Masuk'),
                Tables\Columns\TextColumn::make('total_keluar')->money('IDR')->label('Kas Keluar'),
                Tables\Columns\TextColumn::make('arus_bersih')->money('IDR')->label('Arus Bersih')
                    ->color(fn ($state) => $state >= 0 ? 'success' : 'danger'),
                Tables\Columns\TextColumn::make('status_kesehatan')
                    ->badge()
                    ->color(fn (?string $state) => match ($state) {
                        'Sehat'   => 'success',
                        'Waspada' => 'warning',
                        'Kritis'  => 'danger',
                        default   => 'gray',
                    }),
                Tables\Columns\TextColumn::make('created_at')->dateTime('d M Y H:i')->label('Dibuat'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(), // buka detail (infolist) di halaman
            ])
            ->defaultSort('periode', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCashflow::route('/'),
            'view'  => Pages\ViewCashflow::route('/{record}'),
        ];
    }
}