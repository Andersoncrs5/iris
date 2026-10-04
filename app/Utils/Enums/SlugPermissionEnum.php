<?php

namespace App\Utils\Enums;

enum SlugPermissionEnum: string
{
    // AUTH
    case SUPER_ADM = 'super.adm';
    case SIMPLE_ADM = 'simple.adm';

    // USERS
    case USER_LIST = 'user.list';
    case USER_READ = 'user.read';
    case USER_CREATE = 'user.create';
    case USER_UPDATE = 'user.update';
    case USER_DELETE = 'user.delete';

    // PERMISSIONS
    case PERMISSION_LIST = 'permission.list';
    case PERMISSION_READ = 'permission.read';
    case PERMISSION_CREATE = 'permission.create';
    case PERMISSION_UPDATE = 'permission.update';
    case PERMISSION_DELETE = 'permission.delete';

    // USER PERMISSIONS
    case PERMISSION_USER_LIST = 'permission.user.list';
    case PERMISSION_USER_READ = 'permission.user.read';
    case PERMISSION_USER_CREATE = 'permission.user.create';
    case PERMISSION_USER_UPDATE = 'permission.user.update';
    case PERMISSION_USER_DELETE = 'permission.user.delete';
    case PERMISSION_USER_LINK = 'permission.user.link';

    // CATEGORIES
    case CATEGORY_LIST = 'category.list';
    case CATEGORY_READ = 'category.read';
    case CATEGORY_CREATE = 'category.create';
    case CATEGORY_UPDATE = 'category.update';
    case CATEGORY_DELETE = 'category.delete';

    // TAGS
    case TAG_LIST = 'tag.list';
    case TAG_READ = 'tag.read';
    case TAG_CREATE = 'tag.create';
    case TAG_UPDATE = 'tag.update';
    case TAG_DELETE = 'tag.delete';

    // TICKET STATUSES
    case TICKET_STATUS_LIST = 'ticket.status.list';
    case TICKET_STATUS_READ = 'ticket.status.read';
    case TICKET_STATUS_CREATE = 'ticket.status.create';
    case TICKET_STATUS_UPDATE = 'ticket.status.update';
    case TICKET_STATUS_DELETE = 'ticket.status.delete';

    // TICKET PRIORITIES
    case TICKET_PRIORITY_LIST = 'ticket.priority.list';
    case TICKET_PRIORITY_READ = 'ticket.priority.read';
    case TICKET_PRIORITY_CREATE = 'ticket.priority.create';
    case TICKET_PRIORITY_UPDATE = 'ticket.priority.update';
    case TICKET_PRIORITY_DELETE = 'ticket.priority.delete';

    // TICKETS
    case TICKET_LIST = 'ticket.list';
    case TICKET_READ = 'ticket.read';
    case TICKET_CREATE = 'ticket.create';
    case TICKET_UPDATE = 'ticket.update';
    case TICKET_DELETE = 'ticket.delete';

    // TICKET MESSAGES
    case TICKET_MESSAGE_LIST = 'ticket.message.list';
    case TICKET_MESSAGE_READ = 'ticket.message.read';
    case TICKET_MESSAGE_CREATE = 'ticket.message.create';
    case TICKET_MESSAGE_UPDATE = 'ticket.message.update';
    case TICKET_MESSAGE_DELETE = 'ticket.message.delete';

    // TICKET ATTACHMENTS
    case TICKET_ATTACHMENT_LIST = 'ticket.attachment.list';
    case TICKET_ATTACHMENT_READ = 'ticket.attachment.read';
    case TICKET_ATTACHMENT_CREATE = 'ticket.attachment.create';
    case TICKET_ATTACHMENT_DELETE = 'ticket.attachment.delete';

    // TICKET WATCHERS
    case TICKET_WATCHER_LIST = 'ticket.watcher.list';
    case TICKET_WATCHER_READ = 'ticket.watcher.read';
    case TICKET_WATCHER_CREATE = 'ticket.watcher.create';
    case TICKET_WATCHER_DELETE = 'ticket.watcher.delete';
}