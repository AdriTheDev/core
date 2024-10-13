<?php

namespace App\Filament\Resources\TransferringApplicationResource\Pages;

use App\Filament\Resources\TransferringApplicationResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTransferringApplications extends ListRecords
{
    protected static string $resource = TransferringApplicationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
