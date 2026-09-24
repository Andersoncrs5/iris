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
        Schema::create('ticket_statuses', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary('ticket_statuses_id_primary');
            $table->string('name', 100)->unique('ticket_statuses_name_unique');
            $table->string('code', 50)->unique('ticket_statuses_code_unique');
            $table->string('description', 500)->nullable();
            $table->string('color', 30)->nullable();
            $table->string('icon', 100)->nullable();
            $table->integer('display_order')->default(0);
            $table->boolean('is_initial')->default(false);
            $table->boolean('is_final')->default(false);
            $table->boolean('stops_sla')->default(false);
            $table->boolean('requires_comment')->default(false);
            $table->boolean('is_active')->default(true);
            $table->unsignedBigInteger('version')->default(1);

            $table->timestamps();
            $table->softDeletes();

            $table->index('code', 'idx_ticket_statuses_code');
            $table->index('is_active', 'idx_ticket_statuses_is_active');
            $table->index('display_order', 'idx_ticket_statuses_display_order');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ticket_statuses');
    }
};
