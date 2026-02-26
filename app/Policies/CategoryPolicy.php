<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Category;

class CategoryPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('categories.view');
    }

    public function view(User $user, Category $category): bool
    {
        return $user->hasPermission('categories.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('categories.create');
    }

    public function update(User $user, Category $category): bool
    {
        return $user->hasPermission('categories.update');
    }

    public function delete(User $user, Category $category): bool
    {
        return $user->hasPermission('categories.delete');
    }
}
