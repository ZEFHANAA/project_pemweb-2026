<?php

namespace App\Filament\Admin\Resources\LokasiResource\Pages;

use App\Filament\Admin\Resources\LokasiResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListLokasis extends ListRecords
{
    protected static string $resource = LokasiResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
