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
        Schema::create('ticket_watchers', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary('ticket_watchers_id_primary');

            $table->unsignedBigInteger('ticket_id');
            $table->foreign('ticket_id', 'fk_ticket_watchers_ticket')
                  ->references('id')
                  ->on('tickets')
                  ->onDelete('cascade');

            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id', 'fk_ticket_watchers_user')
                  ->references('id')
                  ->on('users')
                  ->onDelete('cascade');

            $table->unsignedBigInteger('added_by')->nullable();
            $table->foreign('added_by', 'fk_ticket_watchers_added_by')
                  ->references('id')
                  ->on('users')
                  ->onDelete('set null');

            $table->string('reason', 100)->default('MANUAL');
            $table->unsignedBigInteger('version')->default(1);
            $table->timestamp('created_at')->useCurrent();

            $table->unique(['ticket_id', 'user_id'], 'ticket_watchers_unique');

            $table->index('ticket_id', 'idx_ticket_watchers_ticket');
            $table->index('user_id', 'idx_ticket_watchers_user');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ticket_watchers');
    }
};
