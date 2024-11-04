<?php

namespace App\Filament\Resources\SewaKostResource\Pages;

use App\Filament\Resources\SewaKostResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSewaKost extends EditRecord
{
    protected static string $resource = SewaKostResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
