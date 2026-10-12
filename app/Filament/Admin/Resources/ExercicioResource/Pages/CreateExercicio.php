<?php

namespace App\Filament\Admin\Resources\ExercicioResource\Pages;

use App\Filament\Admin\Resources\ExercicioResource;
use Filament\Resources\Pages\CreateRecord;

class CreateExercicio extends CreateRecord
{
    protected static string $resource = ExercicioResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}