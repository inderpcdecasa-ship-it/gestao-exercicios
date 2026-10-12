<?php

namespace App\Filament\Admin\Resources\SessaoTreinoResource\Pages;

use App\Filament\Admin\Resources\SessaoTreinoResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSessaoTreino extends EditRecord
{
    protected static string $resource = SessaoTreinoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
