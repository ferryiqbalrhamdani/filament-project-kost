<?php

namespace App\Filament\Clusters\DataMaster\Resources\AkunBankResource\Pages;

use App\Filament\Clusters\DataMaster\Resources\AkunBankResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageAkunBanks extends ManageRecords
{
    protected static string $resource = AkunBankResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
