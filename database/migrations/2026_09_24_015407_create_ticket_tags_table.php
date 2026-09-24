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
        Schema::create('ticket_tags', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary('ticket_tags_id_primary');

            $table->unsignedBigInteger('ticket_id');
            $table->foreign('ticket_id', 'fk_ticket_tags_ticket')
                  ->references('id')
                  ->on('tickets')
                  ->onDelete('cascade');

            $table->unsignedBigInteger('tag_id');
            $table->foreign('tag_id', 'fk_ticket_tags_tag')
                  ->references('id')
                  ->on('tags')
                  ->onDelete('cascade');

            $table->unsignedBigInteger('created_by')->nullable();
            $table->foreign('created_by', 'fk_ticket_tags_created_by')
                  ->references('id')
                  ->on('users')
                  ->onDelete('set null');

            $table->unsignedBigInteger('version')->default(1);
            $table->timestamp('created_at')->useCurrent();

            $table->unique(['ticket_id', 'tag_id'], 'ticket_tags_unique');

            $table->index('ticket_id', 'idx_ticket_tags_ticket');
            $table->index('tag_id', 'idx_ticket_tags_tag');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ticket_tags');
    }
};
