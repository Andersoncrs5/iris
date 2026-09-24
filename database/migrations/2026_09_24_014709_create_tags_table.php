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
        Schema::create('tags', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary('tags_id_primary');
            $table->string('name', 100)->unique('tags_name_unique');
            $table->string('slug', 100)->unique('tags_slug_unique');
            $table->string('color', 20)->nullable();
            
            $table->enum('type', ['GENERAL', 'SYSTEM', 'CONTENT', 'PRODUCT', 'STATUS'])
                  ->default('GENERAL');
                  
            $table->string('description', 255)->nullable();
            $table->unsignedBigInteger('usage_count')->default(0);
            $table->boolean('is_active')->default(true);
            $table->unsignedBigInteger('version')->default(1);

            $table->timestamps();
            $table->softDeletes();

            $table->index('type', 'idx_tags_type');
            $table->index('is_active', 'idx_tags_is_active');
            $table->index('usage_count', 'idx_tags_usage_count');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tags');
    }
};