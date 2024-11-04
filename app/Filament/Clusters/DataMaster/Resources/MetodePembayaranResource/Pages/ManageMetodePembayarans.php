<?php

namespace App\Filament\Clusters\DataMaster\Resources\MetodePembayaranResource\Pages;

use App\Filament\Clusters\DataMaster\Resources\MetodePembayaranResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageMetodePembayarans extends ManageRecords
{
    protected static string $resource = MetodePembayaranResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
