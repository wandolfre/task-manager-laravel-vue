<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Creates the tasks table for the task management system.
     * Each task belongs to exactly one user (FK constraint).
     */
    public function up(): void
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete(); // Remove tasks when user is deleted
            $table->string('title'); // Short task summary, required
            $table->text('description')->nullable(); // Optional detailed task description
            $table->boolean('completed')->default(false); // Completion status, defaults to incomplete
            $table->date('due_date')->nullable(); // Optional deadline for the task
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
