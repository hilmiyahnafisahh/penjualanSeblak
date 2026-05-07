<?php

namespace App\Filament\Resources\CatatBebanResource\Pages;

use App\Filament\Resources\CatatBebanResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCatatBeban extends EditRecord
{
    protected static string $resource = CatatBebanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
