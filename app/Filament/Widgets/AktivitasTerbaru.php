<?php

namespace App\Filament\Widgets;

use Filament\Widgets\TableWidget;
use Filament\Tables\Table;

class AktivitasTerbaru extends TableWidget
{
    protected static ?string $heading = 'Aktivitas Terbaru';

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                \App\Models\Barang::query()->latest()
            )
            ->columns([
                \Filament\Tables\Columns\TextColumn::make('id')
                    ->label('ID'),

                \Filament\Tables\Columns\TextColumn::make('nama_barang')
                    ->label('Nama Barang')
                    ->searchable(),

                \Filament\Tables\Columns\TextColumn::make('created_at')
                    ->label('Tanggal')
                    ->dateTime(),
            ]);
    }
}