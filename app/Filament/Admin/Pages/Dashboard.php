<?php

namespace App\Filament\Admin\Pages;

use App\Filament\Admin\Widgets\CalendarioTreinosWidget;
use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    public function getWidgets(): array
    {
        return [
            CalendarioTreinosWidget::class,
        ];
    }
}