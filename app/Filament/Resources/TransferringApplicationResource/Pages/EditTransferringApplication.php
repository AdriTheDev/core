<?php

namespace App\Filament\Resources\TransferringApplicationResource\Pages;

use App\Filament\Resources\TransferringApplicationResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTransferringApplication extends EditRecord
{
    protected static string $resource = TransferringApplicationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
