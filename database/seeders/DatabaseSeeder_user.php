<?php

namespace Database\Seeders;

use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder_user extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = new User();

        $user->name = 'Juan Manuel';
        $user->email ='juanmanuellerda@gmail.com';
        $user->type = 'guest';
        $user->password = bcrypt('123');

        $user->save();

        $user->name = 'Sandro';
        $user->email ='sandro@gmail.com';
        $user->type = 'admin';
        $user->password = bcrypt('123');

        $user->save();


        $task = new Task();

        $task->external_id = 'TecZara_02';
        $task->name = 'gestor de tareas';
        $task->comment = 'segun especificaciones';
        $task->date = '28/10/24'; 
        $task->duration = '44';


        $task->save();
    }
}
