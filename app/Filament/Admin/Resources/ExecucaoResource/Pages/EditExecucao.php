<?php

namespace App\Filament\Admin\Resources\ExecucaoResource\Pages;

use App\Filament\Admin\Resources\ExecucaoResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditExecucao extends EditRecord
{
    protected static string $resource = ExecucaoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
