<?php

namespace App\Filament\Clusters\DataMaster\Resources\BiayaKostResource\Pages;

use App\Filament\Clusters\DataMaster\Resources\BiayaKostResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageBiayaKosts extends ManageRecords
{
    protected static string $resource = BiayaKostResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
