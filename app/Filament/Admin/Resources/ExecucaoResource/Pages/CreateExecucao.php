<?php

namespace App\Filament\Admin\Resources\ExecucaoResource\Pages;

use App\Filament\Admin\Resources\ExecucaoResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateExecucao extends CreateRecord
{
    protected static string $resource = ExecucaoResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}