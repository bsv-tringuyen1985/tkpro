<?php

namespace App\Policies;

use App\Models\ContactMessage;

class ContactMessagePolicy extends ModulePolicy
{
    protected string $module = 'contact_messages';
}
