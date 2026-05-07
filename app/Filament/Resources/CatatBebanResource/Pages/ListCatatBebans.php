<?php

namespace App\Filament\Resources\CatatBebanResource\Pages;

use App\Filament\Resources\CatatBebanResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCatatBebans extends ListRecords
{
    protected static string $resource = CatatBebanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
