<?php

namespace Database\Seeders;

use App\Models\Task;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;


class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $user = new User();

        $user->name = 'Juan Manuel';
        $user->email ='juanmanuellerda@gmail.com';
        $user->type = 'guest';
        $user->password = bcrypt('25692569');

        $user->save();

        $task = new Task();

        $task->task = 'hacer un gestor de tareas';
        $task->comment = 'como dijo medina';
        $task->external_id = "TecZARA_01";
        $task->start_task = '28/10/24'; 
        $task->hours = '35';
        $task->user_id = '5';

        $task->save();
    }       

        // User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);
}

