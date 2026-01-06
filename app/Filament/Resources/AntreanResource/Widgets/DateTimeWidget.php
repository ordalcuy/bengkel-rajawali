<?php

namespace App\Filament\Resources\AntreanResource\Widgets;

use Filament\Widgets\Widget;

class DateTimeWidget extends Widget
{
    protected static string $view = 'filament.widgets.date-time-widget';
    
    protected int | string | array $columnSpan = 'full';
    
    protected static ?int $sort = -1; // Display first
}
