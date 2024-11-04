<?php

namespace App\Filament\Resources\SewaKostResource\Pages;

use Filament\Actions;
use App\Models\SewaKost;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\SewaKostResource;
use Filament\Pages\Concerns\ExposesTableToWidgets;

class ListSewaKosts extends ListRecords
{
    use ExposesTableToWidgets;

    protected static string $resource = SewaKostResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Buat Sewa Kost Baru'),
        ];
    }

    public function getTabs(): array
    {
        return [
            'semua data' => Tab::make()
                ->modifyQueryUsing(fn(Builder $query) => $query),
            'lunas' => Tab::make()
                ->modifyQueryUsing(fn(Builder $query) => $query->where('status', 'Lunas')),
            'belum lunas' => Tab::make()
                ->modifyQueryUsing(fn(Builder $query) => $query->where('status', 'Belum Lunas')),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return SewaKostResource::getWidgets();
    }
}
