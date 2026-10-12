<?php

namespace App\Filament\Admin\Resources\SessaoTreinoResource\Pages;

use App\Filament\Admin\Resources\SessaoTreinoResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSessaoTreinos extends ListRecords
{
    protected static string $resource = SessaoTreinoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
