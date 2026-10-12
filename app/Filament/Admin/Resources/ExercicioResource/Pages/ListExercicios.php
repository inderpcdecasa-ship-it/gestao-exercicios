<?php

namespace App\Filament\Admin\Resources\ExercicioResource\Pages;

use App\Filament\Admin\Resources\ExercicioResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListExercicios extends ListRecords
{
    protected static string $resource = ExercicioResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
