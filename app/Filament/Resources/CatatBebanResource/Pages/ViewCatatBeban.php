<?php

namespace App\Filament\Resources\CatatBebanResource\Pages;

use App\Filament\Resources\CatatBebanResource;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ImageEntry;

class ViewCatatBeban extends ViewRecord
{
    protected static string $resource = CatatBebanResource::class;

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([

                Section::make('Detail Beban')
                    ->schema([

                        TextEntry::make('jenis_beban')
                            ->label('Jenis Beban')
                            ->inlineLabel(),

                        TextEntry::make('akun.nama_akun')
                            ->label('Kategori')
                            ->inlineLabel(),

                        TextEntry::make('tanggal')
                            ->label('Tanggal')
                            ->date()
                            ->inlineLabel(),

                        TextEntry::make('total')
                            ->label('Total')
                            ->formatStateUsing(fn ($state) => 'Rp ' . number_format($state, 0, ',', '.'))
                            ->inlineLabel(),

                        TextEntry::make('status')
                            ->label('Status')
                            ->badge()
                            ->color(fn ($state) => $state === 'lunas' ? 'success' : 'danger')
                            ->inlineLabel(),

                        TextEntry::make('keterangan')
                            ->label('Keterangan')
                            ->inlineLabel()
                            ->columnSpanFull()
                            ->extraAttributes([
                                'style' => 'white-space: normal; word-break: break-word;',
                            ]),

                        ImageEntry::make('gambar')
                            ->label('Bukti Tagihan')
                            ->height(250)
                            ->columnSpanFull(),

                    ])
                    ->columns(1), 
            ]);
    }
}