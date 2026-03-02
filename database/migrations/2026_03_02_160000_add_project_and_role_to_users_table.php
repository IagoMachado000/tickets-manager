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
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('project_id')->nullable()->constrained('projects')->cascadeOnDelete()->after('id');
            $table->enum('role', ['user', 'support'])->default('user')->after('password');
            $table->softDeletes();

            $table->index('project_id', 'idx_users_project_id');
            $table->index('role', 'idx_users_role');
            $table->index('deleted_at', 'idx_users_deleted_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Drop indexes first
            $table->dropIndex('idx_users_project_id');
            $table->dropIndex('idx_users_role');
            $table->dropIndex('idx_users_deleted_at');

            // Drop foreign key and columns
            $table->dropForeign(['project_id']);
            $table->dropColumn(['project_id', 'role', 'deleted_at']);
        });
    }
};
