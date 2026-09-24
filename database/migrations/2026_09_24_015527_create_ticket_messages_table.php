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
        Schema::create('ticket_messages', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary('ticket_messages_id_primary');

            $table->unsignedBigInteger('ticket_id');
            $table->foreign('ticket_id', 'fk_ticket_messages_ticket')
                  ->references('id')
                  ->on('tickets')
                  ->onDelete('cascade');

            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id', 'fk_ticket_messages_user')
                  ->references('id')
                  ->on('users');

            $table->text('message');

            $table->enum('type', ['REPLY', 'NOTE', 'SYSTEM_EVENT'])
                  ->default('REPLY');

            $table->boolean('is_internal')->default(false);
            $table->boolean('is_edited')->default(false);
            $table->timestamp('edited_at')->nullable();
            
            $table->json('attachments')->nullable();

            $table->unsignedBigInteger('version')->default(1);

            $table->timestamps();
            $table->softDeletes();

            $table->index('ticket_id', 'idx_ticket_messages_ticket');
            $table->index('user_id', 'idx_ticket_messages_user');
            $table->index('type', 'idx_ticket_messages_type');
            $table->index('created_at', 'idx_ticket_messages_created_at');
            $table->index(['ticket_id', 'is_internal'], 'idx_ticket_messages_ticket_internal');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ticket_messages');
    }
};
