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
            Stat::make('Tasks completed', Task::query()->where('status',1)->count()),
            Stat::make('Tasks uncompleted', Task::query()->where('status',0)->count()),
            Stat::make('Users', User::query()->count('name')),
            Stat::make('Projects', Project::query()->where('id', '!=' , 'null')->count()),
        ];
    }
}

//Stat::make('Cats', Patient::query()->where('type', 'cat')->count()),