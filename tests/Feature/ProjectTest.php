<?php

namespace Tests\Feature;

use App\Models\Project;

use App\Models\Task;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ProjectTest extends TestCase
{
    /**
     * A basic feature test example.
     */

    use RefreshDatabase; 

    public function test_example(): void
    {
        $project = Project::factory()->create();
        
        Task::factory()->for($project)->create();

        $this->assertDatabaseHas('tasks', [
            'project_id' => $project->id,
        ]);

        //Project::all()->dd();

    }
}
