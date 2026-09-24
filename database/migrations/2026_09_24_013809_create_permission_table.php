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
        Schema::create('permissions', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary('permission_role_id_primary');
            $table->string('name', 150)->unique('permissions_name_unique');
            $table->string('slug', 200)->unique('permissions_slug_unique');
            $table->string('module', 100)->default('system');
            $table->string('description', 500)->nullable();
            $table->unsignedBigInteger('version')->default(1);
            $table->timestamps();
            $table->softDeletes();

            $table->index('module', 'idx_permissions_module');
            $table->index('slug', 'idx_permissions_slug');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('permissions');
    }
};
