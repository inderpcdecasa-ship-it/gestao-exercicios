<?php

namespace App\Filament\Admin\Resources\ExercicioResource\Pages;

use App\Filament\Admin\Resources\ExercicioResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditExercicio extends EditRecord
{
    protected static string $resource = ExercicioResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}