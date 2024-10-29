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
        //$user->task_id = '0';

        $user->save();

        $task = new Task();

        $task->name = 'hacer un gestor de tareas';
        $task->comment = 'como dijo medina';
        $task->external_id = "TecZARA_01";
        $task->date = '28/10/24'; 
        $task->duration = '35';
        // $task->user_id = '0';

        $task->save();
    }       

        // User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);
}

