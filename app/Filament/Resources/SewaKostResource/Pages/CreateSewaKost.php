<?php

namespace App\Filament\Resources\SewaKostResource\Pages;

use App\Filament\Resources\SewaKostResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateSewaKost extends CreateRecord
{
    protected static string $resource = SewaKostResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return $data;
    }

    protected function afterCreate(): void
    {
        $this->record->update([
            'status' => 'Belum Lunas'
        ]);
    }
}
