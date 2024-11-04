<?php

namespace App\Filament\Widgets;
use App\Models\Task;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class TaskOverview extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Tasks', Task::query()->count('name')),
            Stat::make('Users', User::query()->count('name')),
            Stat::make('Proyects', Task::query()->where('proyect', '!=' , 'null')->count()),
        ];
    }
}

//Stat::make('Cats', Patient::query()->where('type', 'cat')->count()),