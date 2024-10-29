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
            $table->string('external_id');
            $table->string('name');
            $table->text('comment')->nulable();
            
            // $table->unsignedBigInteger('user_id')->unique()->nullable();
            // $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
            
            $table->timestamp('date');
            $table->text('duration');       
            
            $table->timestamps();
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
