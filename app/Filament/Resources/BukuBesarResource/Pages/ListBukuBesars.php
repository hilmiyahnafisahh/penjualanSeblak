<?php

namespace App\Filament\Resources\BukuBesarResource\Pages;

use App\Filament\Resources\BukuBesarResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListBukuBesars extends ListRecords
{
    protected static string $resource = BukuBesarResource::class;

    protected static string $view = 'filament-panels::resources.pages.list-records';

    protected function getHeaderActions(): array
    {
        return [];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            \App\Filament\Resources\BukuBesarResource\Widgets\BukuBesar::class,
        ];
    }
}
