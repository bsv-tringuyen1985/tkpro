<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy extends ModulePolicy
{
    protected string $module = 'users';
}
