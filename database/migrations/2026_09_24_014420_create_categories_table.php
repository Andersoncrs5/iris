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
        // 1. Criação da tabela com a Primary Key
        Schema::create('categories', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary('categories_id_primary');
            $table->string('name', 100)->unique('categories_name_unique');
            $table->string('slug', 150)->unique('categories_slug_unique');
            $table->string('description', 500)->nullable();
            
            $table->unsignedBigInteger('parent_id')->nullable();

            $table->string('icon', 255)->nullable();
            $table->integer('display_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->unsignedBigInteger('version')->default(1);

            $table->timestamps();
            $table->softDeletes(); 
            
            $table->index('parent_id', 'idx_categories_parent_id');
            $table->index('is_active', 'idx_categories_is_active');
            $table->index('display_order', 'idx_categories_display_order');
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->foreign('parent_id', 'fk_categories_parent')
                  ->references('id')
                  ->on('categories')
                  ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};