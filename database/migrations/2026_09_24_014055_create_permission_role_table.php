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
        Schema::create('permission_role', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary('permission_role_id_primary');
            
            $table->unsignedBigInteger('permission_id');
            $table->foreign('permission_id', 'fk_permission_role_permission')
                  ->references('id')
                  ->on('permissions')
                  ->onDelete('cascade');

            $table->unsignedBigInteger('role_id');
            $table->foreign('role_id', 'fk_permission_role_role')
                  ->references('id')
                  ->on('roles')
                  ->onDelete('cascade');

            $table->unsignedBigInteger('version')->default(1);
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['permission_id', 'role_id'], 'permission_role_unique');

            $table->index('role_id', 'idx_permission_role_role');
            $table->index('permission_id', 'idx_permission_role_permission');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('permission_role');
    }
};
