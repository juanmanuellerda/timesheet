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
        $user->email ='admin@gmail.com';
        $user->type = 'admin';
        $user->password = bcrypt('25692569');

        $user->save();

        $task = new Task();

        $task->external_id = 'TecZara_01';
        $task->name = 'hacer un gestor de tareas';
        $task->comment = 'como dijo medina';
        $task->date = '28/10/24'; 
        $task->duration = '35';
        $task->user = 'Juan Manuel';

        $task->save();
    
        User::factory(4)->create();
        Task::factory(5)->create();
    
        // User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);
    }         
}

