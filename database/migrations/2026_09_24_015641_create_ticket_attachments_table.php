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
        Schema::create('ticket_attachments', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary('ticket_attachments_id_primary');

            $table->unsignedBigInteger('ticket_id');
            $table->foreign('ticket_id', 'fk_ticket_attachments_ticket')
                  ->references('id')
                  ->on('tickets')
                  ->onDelete('cascade');

            $table->unsignedBigInteger('message_id')->nullable();
            $table->foreign('message_id', 'fk_ticket_attachments_message')
                  ->references('id')
                  ->on('ticket_messages')
                  ->onDelete('set null');

            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id', 'fk_ticket_attachments_user')
                  ->references('id')
                  ->on('users');

            $table->string('original_filename', 255);
            $table->string('stored_filename', 255);
            $table->text('storage_path');
            $table->string('disk', 50)->default('s3');
            $table->string('mime_type', 150)->nullable();
            $table->unsignedBigInteger('size_bytes');
            $table->string('checksum', 128)->nullable();
            
            $table->boolean('is_internal')->default(false);
            $table->unsignedBigInteger('version')->default(1);

            $table->timestamps();
            $table->softDeletes();

            $table->index('ticket_id', 'idx_ticket_attachments_ticket');
            $table->index('message_id', 'idx_ticket_attachments_message');
            $table->index('user_id', 'idx_ticket_attachments_user');
            $table->index('created_at', 'idx_ticket_attachments_created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ticket_attachments');
    }
};
