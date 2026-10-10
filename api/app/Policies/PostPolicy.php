<?php

namespace App\Policies;

use App\Models\Post;

class PostPolicy extends ModulePolicy
{
    protected string $module = 'posts';
}
