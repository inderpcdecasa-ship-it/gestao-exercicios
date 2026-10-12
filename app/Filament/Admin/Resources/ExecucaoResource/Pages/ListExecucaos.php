<?php

namespace App\Filament\Admin\Resources\ExecucaoResource\Pages;

use App\Filament\Admin\Resources\ExecucaoResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListExecucaos extends ListRecords
{
    protected static string $resource = ExecucaoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
