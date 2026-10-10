<?php

namespace App\Policies;

use App\Models\Product;

class ProductPolicy extends ModulePolicy
{
    protected string $module = 'products';
}
