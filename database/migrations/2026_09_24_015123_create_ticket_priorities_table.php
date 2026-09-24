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
        Schema::create('ticket_priorities', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary('ticket_priorities_id_primary');
            $table->string('name', 100)->unique('ticket_priorities_name_unique');
            $table->string('code', 50)->unique('ticket_priorities_code_unique');
            $table->unsignedSmallInteger('level')->unique('ticket_priorities_level_unique');
            $table->string('description', 500)->nullable();
            $table->string('color', 30)->nullable();
            $table->string('icon', 100)->nullable();
            
            $table->integer('response_time_minutes')->nullable();
            $table->integer('resolution_time_minutes')->nullable();
            $table->integer('escalation_time_minutes')->nullable();

            $table->boolean('is_default')->default(false);
            $table->boolean('is_active')->default(true);
            $table->unsignedBigInteger('version')->default(1);

            $table->timestamps();
            $table->softDeletes();

            $table->index('code', 'idx_ticket_priorities_code');
            $table->index('level', 'idx_ticket_priorities_level');
            $table->index('is_active', 'idx_ticket_priorities_is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ticket_priorities');
    }
};
