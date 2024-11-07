<?php

namespace App\Filament\Widgets;
use App\Models\Task;
use App\Models\User;
use App\Models\Project;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class TaskOverview extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Tasks', Task::query()->count('name')),
            Stat::make('Users', User::query()->count('name')),
            Stat::make('Projects', Task::query()->where('project_id', '!=' , 'null')->count()),
        ];
    }
}

//Stat::make('Cats', Patient::query()->where('type', 'cat')->count()),