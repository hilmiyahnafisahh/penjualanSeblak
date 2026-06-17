<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LaporanPenjualanResource\Pages;
use App\Models\LaporanPenjualanAi;
use Filament\Forms\Form;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\Section as InfoSection;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\ViewEntry;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Filament\Tables;



class LaporanPenjualanResource extends Resource
{
    protected static ?string $model = LaporanPenjualanAi::class;

    protected static ?string $navigationIcon  = 'heroicon-o-chart-bar-square';
    protected static ?string $navigationLabel = 'Penjualan';
    protected static ?string $navigationGroup = 'Laporan';
    protected static ?string $modelLabel      = 'Laporan Penjualan';
    protected static ?int    $navigationSort  = 2;
    protected static bool    $shouldRegisterNavigation = false;

    public static function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            InfoSection::make('Informasi Laporan')
                ->columns(3)
                ->schema([
                    TextEntry::make('periode')->label('Periode'),
                    TextEntry::make('tipe_periode')->label('Tipe')
                        ->formatStateUsing(fn ($s) => match($s) {
                            'daily'   => 'Harian',
                            'weekly'  => 'Mingguan',
                            'monthly' => 'Bulanan',
                            default   => $s,
                        }),
                    TextEntry::make('status_penjualan')
                        ->label('Status Penjualan')
                        ->badge()
                        ->color(fn (?string $state): string => match ($state) {
                            'Tinggi' => 'success',
                            'Sedang' => 'warning',
                            'Rendah' => 'danger',
                            default  => 'gray',
                        }),
                ]),

            InfoSection::make('Ringkasan Angka')
                ->columns(3)
                ->schema([
                    TextEntry::make('total_pesanan')->label('Total Pesanan'),
                    TextEntry::make('total_qty')->label('Total Item Terjual'),
                    TextEntry::make('total_pendapatan')->label('Total Pendapatan')
                        ->money('IDR'),
                ]),

            InfoSection::make('Menu & Topping Terlaris')
                ->columns(2)
                ->schema([
                    ViewEntry::make('top_menu')
                        ->label('Menu Terlaris')
                        ->view('filament.infolists.top-menu-entry'),
                    ViewEntry::make('top_topping')
                        ->label('Topping Favorit')
                        ->view('filament.infolists.top-topping-entry'),
                ]),

            InfoSection::make('Analisa AI')
                ->icon('heroicon-m-sparkles')
                ->schema([
                    TextEntry::make('ringkasan')->label('Ringkasan'),
                    TextEntry::make('rekomendasi')
                        ->label('Rekomendasi Tindakan')
                        ->listWithLineBreaks()
                        ->bulleted(),
                    TextEntry::make('proyeksi')->label('Proyeksi ke Depan'),
                ]),

            InfoSection::make('Detail Penjualan')
                ->schema([
                    ViewEntry::make('detail_rows')
                        ->label('')
                        ->view('filament.infolists.laporan-penjualan-detail'),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('periode')
                    ->label('Periode')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('tipe_periode')
                    ->label('Tipe')
                    ->formatStateUsing(fn ($s) => match($s) {
                        'daily' => 'Harian', 'weekly' => 'Mingguan', 'monthly' => 'Bulanan', default => $s,
                    }),
                Tables\Columns\TextColumn::make('total_pesanan')->label('Pesanan'),
                Tables\Columns\TextColumn::make('total_qty')->label('Item Terjual'),
                Tables\Columns\TextColumn::make('total_pendapatan')
                    ->label('Total Pendapatan')->money('IDR'),
                Tables\Columns\TextColumn::make('status_penjualan')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Tinggi' => 'success',
                        'Sedang' => 'warning',
                        'Rendah' => 'danger',
                        default  => 'gray',
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')->dateTime('d M Y H:i'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLaporanPenjualan::route('/'),
            'view'  => Pages\ViewLaporanPenjualan::route('/{record}'),
        ];
    }
}
