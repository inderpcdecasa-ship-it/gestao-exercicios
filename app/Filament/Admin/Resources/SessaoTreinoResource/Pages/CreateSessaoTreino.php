<?php

namespace App\Filament\Admin\Resources\SessaoTreinoResource\Pages;

use App\Filament\Admin\Resources\SessaoTreinoResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateSessaoTreino extends CreateRecord
{
    protected static string $resource = SessaoTreinoResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}