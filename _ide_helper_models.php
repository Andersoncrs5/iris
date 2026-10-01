<?php

// @formatter:off
// phpcs:ignoreFile
/**
 * A helper file for your Eloquent Models
 * Copy the phpDocs from this file to the correct Model,
 * And remove them from this file, to prevent double declarations.
 *
 * @author Barry vd. Heuvel <barryvdh@gmail.com>
 */


namespace App\Models{
/**
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string|null $description
 * @property int|null $parent_id
 * @property string|null $icon
 * @property int $display_order
 * @property bool $is_active
 * @property int $version
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, CategoryModel> $children
 * @property-read int|null $children_count
 * @property-read CategoryModel|null $parent
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CategoryModel newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CategoryModel newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CategoryModel onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CategoryModel query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CategoryModel whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CategoryModel whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CategoryModel whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CategoryModel whereDisplayOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CategoryModel whereIcon($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CategoryModel whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CategoryModel whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CategoryModel whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CategoryModel whereParentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CategoryModel whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CategoryModel whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CategoryModel whereVersion($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CategoryModel withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CategoryModel withoutTrashed()
 */
	class CategoryModel extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string $module
 * @property string|null $description
 * @property int $version
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, UserModel> $users
 * @property-read int|null $users_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PermissionModel newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PermissionModel newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PermissionModel onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PermissionModel query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PermissionModel whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PermissionModel whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PermissionModel whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PermissionModel whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PermissionModel whereModule($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PermissionModel whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PermissionModel whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PermissionModel whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PermissionModel whereVersion($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PermissionModel withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PermissionModel withoutTrashed()
 */
	class PermissionModel extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $permission_id
 * @property int $user_id
 * @property int $version
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \App\Models\PermissionModel|null $permission
 * @property-read \App\Models\UserModel|null $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PermissionUserModel newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PermissionUserModel newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PermissionUserModel onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PermissionUserModel query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PermissionUserModel whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PermissionUserModel whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PermissionUserModel whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PermissionUserModel wherePermissionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PermissionUserModel whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PermissionUserModel whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PermissionUserModel whereVersion($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PermissionUserModel withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PermissionUserModel withoutTrashed()
 */
	class PermissionUserModel extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string|null $color
 * @property string $type
 * @property string|null $description
 * @property int $usage_count
 * @property bool $is_active
 * @property int $version
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TagModel newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TagModel newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TagModel onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TagModel query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TagModel whereColor($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TagModel whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TagModel whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TagModel whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TagModel whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TagModel whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TagModel whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TagModel whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TagModel whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TagModel whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TagModel whereUsageCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TagModel whereVersion($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TagModel withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TagModel withoutTrashed()
 */
	class TagModel extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $ticket_id
 * @property int|null $message_id
 * @property int $user_id
 * @property string $original_filename
 * @property string $stored_filename
 * @property string $storage_path
 * @property string $disk
 * @property string|null $mime_type
 * @property int $size_bytes
 * @property string|null $checksum
 * @property bool $is_internal
 * @property int $version
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \App\Models\TicketMessageModel|null $message
 * @property-read \App\Models\UserModel|null $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TicketAttachmentModel newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TicketAttachmentModel newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TicketAttachmentModel onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TicketAttachmentModel query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TicketAttachmentModel whereChecksum($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TicketAttachmentModel whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TicketAttachmentModel whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TicketAttachmentModel whereDisk($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TicketAttachmentModel whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TicketAttachmentModel whereIsInternal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TicketAttachmentModel whereMessageId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TicketAttachmentModel whereMimeType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TicketAttachmentModel whereOriginalFilename($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TicketAttachmentModel whereSizeBytes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TicketAttachmentModel whereStoragePath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TicketAttachmentModel whereStoredFilename($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TicketAttachmentModel whereTicketId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TicketAttachmentModel whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TicketAttachmentModel whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TicketAttachmentModel whereVersion($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TicketAttachmentModel withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TicketAttachmentModel withoutTrashed()
 */
	class TicketAttachmentModel extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $ticket_id
 * @property int $user_id
 * @property string $message
 * @property string $type
 * @property bool $is_internal
 * @property bool $is_edited
 * @property \Illuminate\Support\Carbon|null $edited_at
 * @property array<array-key, mixed>|null $attachments
 * @property int $version
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\TicketAttachmentModel> $messageAttachments
 * @property-read int|null $message_attachments_count
 * @property-read \App\Models\UserModel|null $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TicketMessageModel newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TicketMessageModel newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TicketMessageModel onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TicketMessageModel query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TicketMessageModel whereAttachments($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TicketMessageModel whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TicketMessageModel whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TicketMessageModel whereEditedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TicketMessageModel whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TicketMessageModel whereIsEdited($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TicketMessageModel whereIsInternal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TicketMessageModel whereMessage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TicketMessageModel whereTicketId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TicketMessageModel whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TicketMessageModel whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TicketMessageModel whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TicketMessageModel whereVersion($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TicketMessageModel withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TicketMessageModel withoutTrashed()
 */
	class TicketMessageModel extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $ticket_number
 * @property int $user_id
 * @property int|null $agent_id
 * @property int|null $team_id
 * @property int $category_id
 * @property int $status_id
 * @property int $priority_id
 * @property string $subject
 * @property string $description
 * @property string $source
 * @property int|null $satisfaction_rating
 * @property string|null $satisfaction_comment
 * @property bool $is_escalated
 * @property int $version
 * @property \Illuminate\Support\Carbon $opened_at
 * @property \Illuminate\Support\Carbon $last_activity_at
 * @property \Illuminate\Support\Carbon|null $first_response_at
 * @property \Illuminate\Support\Carbon|null $resolved_at
 * @property \Illuminate\Support\Carbon|null $closed_at
 * @property \Illuminate\Support\Carbon|null $due_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \App\Models\UserModel|null $agent
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\TicketAttachmentModel> $attachments
 * @property-read int|null $attachments_count
 * @property-read \App\Models\CategoryModel|null $category
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\TicketMessageModel> $messages
 * @property-read int|null $messages_count
 * @property-read \App\Models\TicketPriorityModel|null $priority
 * @property-read \App\Models\TicketStatusModel|null $status
 * @property-read \App\Models\UserModel|null $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TicketModel newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TicketModel newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TicketModel onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TicketModel query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TicketModel whereAgentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TicketModel whereCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TicketModel whereClosedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TicketModel whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TicketModel whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TicketModel whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TicketModel whereDueAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TicketModel whereFirstResponseAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TicketModel whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TicketModel whereIsEscalated($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TicketModel whereLastActivityAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TicketModel whereOpenedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TicketModel wherePriorityId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TicketModel whereResolvedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TicketModel whereSatisfactionComment($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TicketModel whereSatisfactionRating($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TicketModel whereSource($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TicketModel whereStatusId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TicketModel whereSubject($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TicketModel whereTeamId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TicketModel whereTicketNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TicketModel whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TicketModel whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TicketModel whereVersion($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TicketModel withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TicketModel withoutTrashed()
 */
	class TicketModel extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $name
 * @property string $code
 * @property int $level
 * @property string|null $description
 * @property string|null $color
 * @property string|null $icon
 * @property int|null $response_time_minutes
 * @property int|null $resolution_time_minutes
 * @property int|null $escalation_time_minutes
 * @property bool $is_default
 * @property bool $is_active
 * @property int $version
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TicketPriorityModel newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TicketPriorityModel newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TicketPriorityModel onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TicketPriorityModel query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TicketPriorityModel whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TicketPriorityModel whereColor($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TicketPriorityModel whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TicketPriorityModel whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TicketPriorityModel whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TicketPriorityModel whereEscalationTimeMinutes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TicketPriorityModel whereIcon($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TicketPriorityModel whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TicketPriorityModel whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TicketPriorityModel whereIsDefault($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TicketPriorityModel whereLevel($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TicketPriorityModel whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TicketPriorityModel whereResolutionTimeMinutes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TicketPriorityModel whereResponseTimeMinutes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TicketPriorityModel whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TicketPriorityModel whereVersion($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TicketPriorityModel withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TicketPriorityModel withoutTrashed()
 */
	class TicketPriorityModel extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $name
 * @property string $code
 * @property string|null $description
 * @property string|null $color
 * @property string|null $icon
 * @property int $display_order
 * @property bool $is_initial
 * @property bool $is_final
 * @property bool $stops_sla
 * @property bool $requires_comment
 * @property bool $is_active
 * @property int $version
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TicketStatusModel newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TicketStatusModel newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TicketStatusModel onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TicketStatusModel query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TicketStatusModel whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TicketStatusModel whereColor($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TicketStatusModel whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TicketStatusModel whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TicketStatusModel whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TicketStatusModel whereDisplayOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TicketStatusModel whereIcon($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TicketStatusModel whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TicketStatusModel whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TicketStatusModel whereIsFinal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TicketStatusModel whereIsInitial($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TicketStatusModel whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TicketStatusModel whereRequiresComment($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TicketStatusModel whereStopsSla($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TicketStatusModel whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TicketStatusModel whereVersion($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TicketStatusModel withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TicketStatusModel withoutTrashed()
 */
	class TicketStatusModel extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $ticket_id
 * @property int $tag_id
 * @property int|null $created_by
 * @property int $version
 * @property \Illuminate\Support\Carbon $created_at
 * @property-read \App\Models\UserModel|null $creator
 * @property-read \App\Models\TagModel|null $tag
 * @property-read \App\Models\TicketModel|null $ticket
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TicketTagModel newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TicketTagModel newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TicketTagModel query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TicketTagModel whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TicketTagModel whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TicketTagModel whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TicketTagModel whereTagId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TicketTagModel whereTicketId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TicketTagModel whereVersion($value)
 */
	class TicketTagModel extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $ticket_id
 * @property int $user_id
 * @property int|null $added_by
 * @property string $reason
 * @property int $version
 * @property \Illuminate\Support\Carbon $created_at
 * @property-read \App\Models\UserModel|null $addedBy
 * @property-read \App\Models\TicketModel|null $ticket
 * @property-read \App\Models\UserModel|null $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TicketWatcherModel newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TicketWatcherModel newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TicketWatcherModel query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TicketWatcherModel whereAddedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TicketWatcherModel whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TicketWatcherModel whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TicketWatcherModel whereReason($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TicketWatcherModel whereTicketId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TicketWatcherModel whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TicketWatcherModel whereVersion($value)
 */
	class TicketWatcherModel extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $name
 * @property string $full_name
 * @property string $email
 * @property string $password
 * @property string $status
 * @property array<string>|null $permissions
 * @property \Illuminate\Support\Carbon|null $email_verified_at
 * @property \Illuminate\Support\Carbon|null $last_login_at
 * @property int $version
 * @property string|null $remember_token
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection<int, \Illuminate\Notifications\DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @property-read int|null $permissions_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserModel newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserModel newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserModel onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserModel query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserModel whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserModel whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserModel whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserModel whereEmailVerifiedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserModel whereFullName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserModel whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserModel whereLastLoginAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserModel whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserModel wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserModel wherePermissions($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserModel whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserModel whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserModel whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserModel whereVersion($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserModel withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserModel withoutTrashed()
 */
	class UserModel extends \Eloquent {}
}

