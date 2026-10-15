<?php

namespace App\Utils\Enums\TicketMessage;

enum TicketMessageTypeEnum: string
{
    case REPLY = 'REPLY';

    case NOTE = 'NOTE';

    case SYSTEM_EVENT = 'SYSTEM_EVENT';
}