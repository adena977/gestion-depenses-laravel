<?php

namespace App\Policies;

use App\Models\Category;
use App\Models\User;

class CategoryPolicy
{
    public function view(User $user, Category $category): bool
    {
        return $user->id === $category->user_id || $category->is_default;
    }

    public function update(User $user, Category $category): bool
    {
        // Ne peut pas modifier les catégories par défaut
        return $user->id === $category->user_id && !$category->is_default;
    }

    public function delete(User $user, Category $category): bool
    {
        // Ne peut pas supprimer les catégories par défaut
        return $user->id === $category->user_id && !$category->is_default;
    }
}