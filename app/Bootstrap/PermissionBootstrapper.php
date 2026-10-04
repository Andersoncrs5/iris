<?php

namespace App\Bootstrap;

use App\Models\PermissionModel;
use App\Utils\Snowflake\IdGeneratorInterface;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class PermissionBootstrapper
{
    private const PERMISSIONS = [
        ['name' => 'Super Adm', 'slug' => 'super.adm', 'module' => 'auth', 'description' => 'Allows all in system'],     
        ['name' => 'Simple Adm', 'slug' => 'simple.adm', 'module' => 'auth', 'description' => 'Allows anything in system'],
        
        // ==========================================
        // USERS (Módulo de Usuários)
        // ==========================================
        ['name' => 'List Users', 'slug' => 'user.list', 'module' => 'users', 'description' => 'Allows listing all users.'],
        ['name' => 'Read User', 'slug' => 'user.read', 'module' => 'users', 'description' => 'Allows viewing user details.'],
        ['name' => 'Create User', 'slug' => 'user.create', 'module' => 'users', 'description' => 'Allows creating new users.'],
        ['name' => 'Update User', 'slug' => 'user.update', 'module' => 'users', 'description' => 'Allows updating user information.'],
        ['name' => 'Delete User', 'slug' => 'user.delete', 'module' => 'users', 'description' => 'Allows deleting users.'],

        // ==========================================
        // PERMISSIONS & USER PERMISSIONS
        // ==========================================
        ['name' => 'List Permissions', 'slug' => 'permission.list', 'module' => 'permissions', 'description' => 'Allows listing system permissions.'],
        ['name' => 'Read Permission', 'slug' => 'permission.read', 'module' => 'permissions', 'description' => 'Allows viewing permission details.'],
        ['name' => 'Create Permission', 'slug' => 'permission.create', 'module' => 'permissions', 'description' => 'Allows creating new permissions.'],
        ['name' => 'Update Permission', 'slug' => 'permission.update', 'module' => 'permissions', 'description' => 'Allows editing existing permissions.'],
        ['name' => 'Delete Permission', 'slug' => 'permission.delete', 'module' => 'permissions', 'description' => 'Allows deleting permissions.'],
        
        ['name' => 'List User Permissions', 'slug' => 'permission.user.list', 'module' => 'permissions', 'description' => 'Allows listing permissions assigned to users.'],
        ['name' => 'Read User Permission', 'slug' => 'permission.user.read', 'module' => 'permissions', 'description' => 'Allows viewing a user\'s specific permissions.'],
        ['name' => 'Grant User Permission', 'slug' => 'permission.user.create', 'module' => 'permissions', 'description' => 'Allows granting permissions to a user.'],
        ['name' => 'Update User Permissions', 'slug' => 'permission.user.update', 'module' => 'permissions', 'description' => 'Allows updating user permissions.'],
        ['name' => 'Revoke User Permission', 'slug' => 'permission.user.delete', 'module' => 'permissions', 'description' => 'Allows revoking permissions from a user.'],
        ['name' => 'Link User Permissions', 'slug' => 'permission.user.link', 'module' => 'permissions', 'description' => 'Allows bulk linking permissions to a user.'],

        // ==========================================
        // CATEGORIES (Categorias)
        // ==========================================
        ['name' => 'List Categories', 'slug' => 'category.list', 'module' => 'categories', 'description' => 'Allows listing ticket categories.'],
        ['name' => 'Read Category', 'slug' => 'category.read', 'module' => 'categories', 'description' => 'Allows viewing category details.'],
        ['name' => 'Create Category', 'slug' => 'category.create', 'module' => 'categories', 'description' => 'Allows creating categories.'],
        ['name' => 'Update Category', 'slug' => 'category.update', 'module' => 'categories', 'description' => 'Allows updating categories.'],
        ['name' => 'Delete Category', 'slug' => 'category.delete', 'module' => 'categories', 'description' => 'Allows deleting categories.'],

        // ==========================================
        // TAGS (Tags)
        // ==========================================
        ['name' => 'List Tags', 'slug' => 'tag.list', 'module' => 'tags', 'description' => 'Allows listing system tags.'],
        ['name' => 'Read Tag', 'slug' => 'tag.read', 'module' => 'tags', 'description' => 'Allows viewing tag details.'],
        ['name' => 'Create Tag', 'slug' => 'tag.create', 'module' => 'tags', 'description' => 'Allows creating new tags.'],
        ['name' => 'Update Tag', 'slug' => 'tag.update', 'module' => 'tags', 'description' => 'Allows updating tags.'],
        ['name' => 'Delete Tag', 'slug' => 'tag.delete', 'module' => 'tags', 'description' => 'Allows deleting tags.'],

        // ==========================================
        // TICKET STATUSES (Status de Tickets)
        // ==========================================
        ['name' => 'List Ticket Statuses', 'slug' => 'ticket.status.list', 'module' => 'ticket_statuses', 'description' => 'Allows listing ticket statuses.'],
        ['name' => 'Read Ticket Status', 'slug' => 'ticket.status.read', 'module' => 'ticket_statuses', 'description' => 'Allows viewing ticket status details.'],
        ['name' => 'Create Ticket Status', 'slug' => 'ticket.status.create', 'module' => 'ticket_statuses', 'description' => 'Allows creating ticket statuses.'],
        ['name' => 'Update Ticket Status', 'slug' => 'ticket.status.update', 'module' => 'ticket_statuses', 'description' => 'Allows updating ticket statuses.'],
        ['name' => 'Delete Ticket Status', 'slug' => 'ticket.status.delete', 'module' => 'ticket_statuses', 'description' => 'Allows deleting ticket statuses.'],

        // ==========================================
        // TICKET PRIORITIES (Prioridades de Tickets)
        // ==========================================
        ['name' => 'List Ticket Priorities', 'slug' => 'ticket.priority.list', 'module' => 'ticket_priorities', 'description' => 'Allows listing ticket priorities.'],
        ['name' => 'Read Ticket Priority', 'slug' => 'ticket.priority.read', 'module' => 'ticket_priorities', 'description' => 'Allows viewing priority details.'],
        ['name' => 'Create Ticket Priority', 'slug' => 'ticket.priority.create', 'module' => 'ticket_priorities', 'description' => 'Allows creating ticket priorities.'],
        ['name' => 'Update Ticket Priority', 'slug' => 'ticket.priority.update', 'module' => 'ticket_priorities', 'description' => 'Allows updating ticket priorities.'],
        ['name' => 'Delete Ticket Priority', 'slug' => 'ticket.priority.delete', 'module' => 'ticket_priorities', 'description' => 'Allows deleting ticket priorities.'],

        // ==========================================
        // TICKETS (Tickets Chamados)
        // ==========================================
        ['name' => 'List Tickets', 'slug' => 'ticket.list', 'module' => 'tickets', 'description' => 'Allows listing all support tickets.'],
        ['name' => 'Read Ticket', 'slug' => 'ticket.read', 'module' => 'tickets', 'description' => 'Allows viewing ticket details.'],
        ['name' => 'Create Ticket', 'slug' => 'ticket.create', 'module' => 'tickets', 'description' => 'Allows creating support tickets.'],
        ['name' => 'Update Ticket', 'slug' => 'ticket.update', 'module' => 'tickets', 'description' => 'Allows updating support tickets.'],
        ['name' => 'Delete Ticket', 'slug' => 'ticket.delete', 'module' => 'tickets', 'description' => 'Allows deleting support tickets.'],

        // ==========================================
        // TICKET MESSAGES (Mensagens/Respostas)
        // ==========================================
        ['name' => 'List Ticket Messages', 'slug' => 'ticket.message.list', 'module' => 'ticket_messages', 'description' => 'Allows listing messages inside a ticket.'],
        ['name' => 'Read Ticket Message', 'slug' => 'ticket.message.read', 'module' => 'ticket_messages', 'description' => 'Allows reading specific ticket messages.'],
        ['name' => 'Create Ticket Message', 'slug' => 'ticket.message.create', 'module' => 'ticket_messages', 'description' => 'Allows posting messages/replies on tickets.'],
        ['name' => 'Update Ticket Message', 'slug' => 'ticket.message.update', 'module' => 'ticket_messages', 'description' => 'Allows editing ticket messages.'],
        ['name' => 'Delete Ticket Message', 'slug' => 'ticket.message.delete', 'module' => 'ticket_messages', 'description' => 'Allows deleting ticket messages.'],

        // ==========================================
        // TICKET ATTACHMENTS (Anexos de Tickets)
        // ==========================================
        ['name' => 'List Ticket Attachments', 'slug' => 'ticket.attachment.list', 'module' => 'ticket_attachments', 'description' => 'Allows listing attachments.'],
        ['name' => 'Read Ticket Attachment', 'slug' => 'ticket.attachment.read', 'module' => 'ticket_attachments', 'description' => 'Allows viewing/downloading attachments.'],
        ['name' => 'Create Ticket Attachment', 'slug' => 'ticket.attachment.create', 'module' => 'ticket_attachments', 'description' => 'Allows uploading attachments.'],
        ['name' => 'Delete Ticket Attachment', 'slug' => 'ticket.attachment.delete', 'module' => 'ticket_attachments', 'description' => 'Allows deleting attachments.'],

        // ==========================================
        // TICKET WATCHERS (Observadores de Tickets)
        // ==========================================
        ['name' => 'List Ticket Watchers', 'slug' => 'ticket.watcher.list', 'module' => 'ticket_watchers', 'description' => 'Allows listing watchers on a ticket.'],
        ['name' => 'Read Ticket Watcher', 'slug' => 'ticket.watcher.read', 'module' => 'ticket_watchers', 'description' => 'Allows viewing ticket watcher details.'],
        ['name' => 'Add Ticket Watcher', 'slug' => 'ticket.watcher.create', 'module' => 'ticket_watchers', 'description' => 'Allows adding watchers to a ticket.'],
        ['name' => 'Remove Ticket Watcher', 'slug' => 'ticket.watcher.delete', 'module' => 'ticket_watchers', 'description' => 'Allows removing watchers from a ticket.'],
    ];

    public function run(): void
    {
        Cache::rememberForever('app_permissions_initialized', function () {
            
            /** @var IdGeneratorInterface $idGenerator */
            $idGenerator = app(IdGeneratorInterface::class);

            foreach (self::PERMISSIONS as $permission) {
                $permissionModel = PermissionModel::firstOrCreate(
                    ['slug' => $permission['slug']], 
                    fn () => [
                        'id'          => $idGenerator->generate(), 
                        'name'        => $permission['name'],
                        'module'      => $permission['module'],
                        'description' => $permission['description'],
                    ]
                );

                if ($permissionModel->wasRecentlyCreated) {
                    Log::info("[PermissionBootstrapper] Permission created successfully.", [
                        'id'     => $permissionModel->id,
                        'slug'   => $permissionModel->slug,
                        'module' => $permissionModel->module,
                    ]);
                } else {
                    Log::info("[PermissionBootstrapper] Permission already exists, skipping.", [
                        'slug'   => $permissionModel->slug,
                        'module' => $permissionModel->module,
                    ]);
                }
            }

            return true;
        });
    }
}