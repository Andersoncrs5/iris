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
        Schema::create('tickets', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary('tickets_id_primary');
            $table->bigIncrements('ticket_number')->unique('tickets_number_unique');

            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id', 'fk_tickets_user')
                  ->references('id')
                  ->on('users');

            $table->unsignedBigInteger('agent_id')->nullable();
            $table->foreign('agent_id', 'fk_tickets_agent')
                  ->references('id')
                  ->on('users')
                  ->onDelete('set null');

            $table->unsignedBigInteger('team_id')->nullable(); 

            $table->unsignedBigInteger('category_id');
            $table->foreign('category_id', 'fk_tickets_category')
                  ->references('id')
                  ->on('categories');

            $table->unsignedBigInteger('status_id');
            $table->foreign('status_id', 'fk_tickets_status')
                  ->references('id')
                  ->on('ticket_statuses');

            $table->unsignedBigInteger('priority_id');
            $table->foreign('priority_id', 'fk_tickets_priority')
                  ->references('id')
                  ->on('ticket_priorities');

            $table->string('subject', 255);
            $table->text('description');

            $table->enum('source', ['WEB', 'EMAIL', 'API', 'CHAT', 'WHATSAPP', 'PHONE'])
                  ->default('WEB');

            $table->unsignedSmallInteger('satisfaction_rating')->nullable();
            $table->string('satisfaction_comment', 500)->nullable();
            $table->boolean('is_escalated')->default(false);

            $table->unsignedBigInteger('version')->default(1);

            $table->timestamp('opened_at')->useCurrent();
            $table->timestamp('last_activity_at')->useCurrent();
            $table->timestamp('first_response_at')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->timestamp('due_at')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index('user_id', 'idx_tickets_user');
            $table->index('agent_id', 'idx_tickets_agent');
            $table->index('category_id', 'idx_tickets_category');
            $table->index('status_id', 'idx_tickets_status');
            $table->index('priority_id', 'idx_tickets_priority');
            $table->index('created_at', 'idx_tickets_created_at');
            $table->index('opened_at', 'idx_tickets_opened_at');
            $table->index('last_activity_at', 'idx_tickets_last_activity');
            $table->index(['agent_id', 'status_id'], 'idx_tickets_agent_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
