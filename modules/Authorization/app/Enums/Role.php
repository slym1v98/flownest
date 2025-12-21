<?php

namespace Modules\Authorization\Enums;

enum Role: string
{
    case ADMIN = 'admin';
    case EDITOR = 'editor';
    case PUBLISHER = 'publisher';
    case GUEST = 'guest';
}
