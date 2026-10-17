<?php

use App\Utils\Enums\PermissionRequestStatus\PermissionRequestStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('permission_requests', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary('permission_requests_id_primary');

            // Request owner
            $table->unsignedBigInteger('user_id');

            $table->foreign('user_id', 'fk_permission_requests_user')
                ->references('id')
                ->on('users')
                ->onDelete('restrict');

            $table->unsignedBigInteger('permission_id');

            $table->foreign('permission_id', 'fk_permission_requests_permission')
                ->references('id')
                ->on('permissions')
                ->onDelete('restrict');

            $table->text('reason');

            $table->enum(
                'status',
                PermissionRequestStatus::values()
            )->default(PermissionRequestStatus::PENDING->value);

            // Request reviewer
            $table->unsignedBigInteger('reviewed_by')->nullable();

            $table->foreign('reviewed_by', 'fk_permission_requests_reviewer')
                ->references('id')
                ->on('users')
                ->onDelete('set null');

            $table->timestamp('reviewed_at')->nullable();

            $table->text('review_comment')->nullable();

            $table->timestamp('viewed_at')->nullable();

            $table->unsignedBigInteger('version')->default(1);

            $table->timestamps();

            $table->index(
                ['user_id', 'status'],
                'idx_permission_requests_user_status'
            );

            $table->index(
                ['status', 'created_at'],
                'idx_permission_requests_status_created'
            );

            $table->index(
                'permission_id',
                'idx_permission_requests_permission'
            );

            $table->index(
                'reviewed_by',
                'idx_permission_requests_reviewer'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('permission_requests');
    }
};