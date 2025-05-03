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
            $table->string('a2a_task_id')->unique()->nullable();
            $table->string('a2a_status')->nullable()->index();
            $table->unsignedBigInteger('a2a_last_message_sequence')->nullable();
            $table->json('a2a_meta')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropColumn(['a2a_task_id', 'a2a_status', 'a2a_last_message_sequence', 'a2a_meta']);
        });

        Schema::dropIfExists('tasks');
    }
};
