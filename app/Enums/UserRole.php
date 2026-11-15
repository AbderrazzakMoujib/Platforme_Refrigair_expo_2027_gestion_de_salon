<?php

namespace App\Enums;

enum UserRole: string
{
    case Admin = 'admin';
    case Commercial = 'commercial';
    case Exposant = 'exposant';
}
