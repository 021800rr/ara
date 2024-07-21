<?php

namespace App\Config;

enum UserStatus: string
{
    case inactive = 'inactive';
    case active = 'active';
    case deleted = 'deleted';
}
