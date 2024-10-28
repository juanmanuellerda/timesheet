<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->text('external_id');
            $table->string('task');
            $table->text('comment')->nulable();
            //$table->text('external_id');
            //$table->tipo fecha ('tart_task');
            //$table->tipo fecha ('job_time');
            
            //$table->tipo fecha ('finish_task');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
