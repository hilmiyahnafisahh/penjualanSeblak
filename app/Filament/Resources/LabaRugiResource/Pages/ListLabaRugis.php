<?php

namespace App\Filament\Resources\LabaRugiResource\Pages;

use App\Filament\Resources\LabaRugiResource;
use Filament\Resources\Pages\ListRecords;

class ListLabaRugis extends ListRecords
{
    protected static string $resource = LabaRugiResource::class;

    protected function getHeaderActions(): array
    {
        return []; // tidak ada tombol Create
    }

    protected function getHeaderWidgets(): array
    {
        return [
            \App\Filament\Resources\LabaRugiResource\Widgets\LabaRugi::class,
        ];
    }

    protected function getFooterWidgets(): array
    {
        return [
            \App\Filament\Widgets\GrafikLabaRugi::class,
        ];
    }
}
