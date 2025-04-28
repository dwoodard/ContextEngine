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
            $table->string('pattern')->nullable();       // The agent pattern chosen (e.g., 'planner', 'parallel', etc.)
            $table->text('input');                       // The user prompt or task description
            $table->text('result')->nullable();          // Final result from the agent(s)
            $table->string('status')->default('pending'); // Task status: 'pending','running','completed','failed'
            $table->json('meta')->nullable();            // (Optional) store additional data (sub-results, logs, memory, etc.)
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
