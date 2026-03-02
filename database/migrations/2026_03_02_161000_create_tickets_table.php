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
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('title');
            $table->text('description');
            $table->enum('status', ['pending', 'in_progress', 'answered', 'closed'])->default('pending');
            $table->timestamp('last_interaction_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('project_id', 'idx_tickets_project_id');
            $table->index('user_id', 'idx_tickets_user_id');
            $table->index('status', 'idx_tickets_status');
            $table->index(['project_id', 'status'], 'idx_tickets_project_status');
            $table->index(['status', 'created_at'], 'idx_tickets_status_created');
            $table->index('last_interaction_at', 'idx_tickets_last_interaction');
            $table->index('deleted_at', 'idx_tickets_deleted_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
