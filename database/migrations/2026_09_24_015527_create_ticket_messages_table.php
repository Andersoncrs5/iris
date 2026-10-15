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
            $table->unsignedBigInteger('id')
                ->primary('ticket_messages_id_primary');

            $table->unsignedBigInteger('ticket_id');

            $table->unsignedBigInteger('user_id');

            $table->unsignedBigInteger('parent_id')->nullable();

            $table->text('message');

            $table->enum('type', [
                'REPLY',
                'NOTE',
                'SYSTEM_EVENT',
            ])->default('REPLY');

            $table->boolean('is_internal')->default(false);

            $table->boolean('is_edited')->default(false);
            $table->timestamp('edited_at')->nullable();

            $table->json('attachments')->nullable();

            $table->unsignedBigInteger('version')->default(1);

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('ticket_id', 'fk_ticket_messages_ticket')
                ->references('id')
                ->on('tickets')
                ->cascadeOnDelete();

            $table->foreign('user_id', 'fk_ticket_messages_user')
                ->references('id')
                ->on('users');

            $table->foreign('parent_id', 'fk_ticket_messages_parent')
                ->references('id')
                ->on('ticket_messages')
                ->nullOnDelete();

            $table->index('ticket_id', 'idx_ticket_messages_ticket');
            $table->index('user_id', 'idx_ticket_messages_user');
            $table->index('parent_id', 'idx_ticket_messages_parent');
            $table->index('type', 'idx_ticket_messages_type');
            $table->index('created_at', 'idx_ticket_messages_created_at');

            $table->index(
                ['ticket_id', 'is_internal'],
                'idx_ticket_messages_ticket_internal'
            );

            $table->index(
                ['ticket_id', 'parent_id'],
                'idx_ticket_messages_ticket_parent'
            );
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