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
        Schema::create('permission_user', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary('permission_user_id_primary');
            
            $table->unsignedBigInteger('permission_id');
            $table->foreign('permission_id', 'fk_permission_user_permission')
                  ->references('id')
                  ->on('permissions')
                  ->onDelete('cascade');

            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id', 'fk_permission_user_user')
                  ->references('id')
                  ->on('users')
                  ->onDelete('cascade');

            $table->unsignedBigInteger('version')->default(1);
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['permission_id', 'user_id'], 'permission_user_unique');

            $table->index('user_id', 'idx_permission_user_user');
            $table->index('permission_id', 'idx_permission_user_permission');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('permission_user');
    }
};