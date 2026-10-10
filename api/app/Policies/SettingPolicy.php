<?php

namespace App\Policies;

use App\Models\Setting;

class SettingPolicy extends ModulePolicy
{
    protected string $module = 'settings';
}
