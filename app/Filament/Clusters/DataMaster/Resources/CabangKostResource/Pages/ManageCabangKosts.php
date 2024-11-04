<?php

namespace App\Filament\Clusters\DataMaster\Resources\CabangKostResource\Pages;

use App\Filament\Clusters\DataMaster\Resources\CabangKostResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageCabangKosts extends ManageRecords
{
    protected static string $resource = CabangKostResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
