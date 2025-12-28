<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;
use App\Models\User;

class CategoriesTableSeeder extends Seeder
{
    public function run(): void
    {
        // Catégories de dépenses par défaut (pour tous les utilisateurs)
        $defaultExpenseCategories = [
            ['name' => 'Alimentation', 'type' => 'expense', 'color' => '#10B981', 'icon' => 'fas fa-shopping-cart'],
            ['name' => 'Transport', 'type' => 'expense', 'color' => '#3B82F6', 'icon' => 'fas fa-car'],
            ['name' => 'Logement', 'type' => 'expense', 'color' => '#8B5CF6', 'icon' => 'fas fa-home'],
            ['name' => 'Loisirs', 'type' => 'expense', 'color' => '#F59E0B', 'icon' => 'fas fa-film'],
            ['name' => 'Santé', 'type' => 'expense', 'color' => '#EF4444', 'icon' => 'fas fa-heart'],
            ['name' => 'Éducation', 'type' => 'expense', 'color' => '#8B5CF6', 'icon' => 'fas fa-book'],
            ['name' => 'Shopping', 'type' => 'expense', 'color' => '#EC4899', 'icon' => 'fas fa-shopping-bag'],
            ['name' => 'Restaurant', 'type' => 'expense', 'color' => '#F59E0B', 'icon' => 'fas fa-utensils'],
            ['name' => 'Abonnements', 'type' => 'expense', 'color' => '#6366F1', 'icon' => 'fas fa-repeat'],
            ['name' => 'Autres Dépenses', 'type' => 'expense', 'color' => '#6B7280', 'icon' => 'fas fa-ellipsis-h'],
        ];

        // Catégories de revenus par défaut
        $defaultIncomeCategories = [
            ['name' => 'Salaire', 'type' => 'income', 'color' => '#10B981', 'icon' => 'fas fa-money-check'],
            ['name' => 'Freelance', 'type' => 'income', 'color' => '#3B82F6', 'icon' => 'fas fa-laptop'],
            ['name' => 'Investissements', 'type' => 'income', 'color' => '#8B5CF6', 'icon' => 'fas fa-chart-line'],
            ['name' => 'Cadeaux', 'type' => 'income', 'color' => '#F59E0B', 'icon' => 'fas fa-gift'],
            ['name' => 'Remboursements', 'type' => 'income', 'color' => '#EF4444', 'icon' => 'fas fa-undo'],
        ];

        // Récupérer tous les utilisateurs
        $users = User::all();

        // Si pas d'utilisateurs, créer un utilisateur test
        if ($users->isEmpty()) {
            $user = User::create([
                'name' => 'Admin',
                'email' => 'admin@example.com',
                'password' => bcrypt('password'),
            ]);
            $users = collect([$user]);
        }

        foreach ($users as $user) {
            // Ajouter les catégories de dépenses
            foreach ($defaultExpenseCategories as $categoryData) {
                Category::firstOrCreate(
                    [
                        'user_id' => $user->id,
                        'name' => $categoryData['name'],
                        'type' => $categoryData['type'],
                    ],
                    [
                        'color' => $categoryData['color'],
                        'icon' => $categoryData['icon'],
                        'is_default' => true,
                    ]
                );
            }

            // Ajouter les catégories de revenus
            foreach ($defaultIncomeCategories as $categoryData) {
                Category::firstOrCreate(
                    [
                        'user_id' => $user->id,
                        'name' => $categoryData['name'],
                        'type' => $categoryData['type'],
                    ],
                    [
                        'color' => $categoryData['color'],
                        'icon' => $categoryData['icon'],
                        'is_default' => true,
                    ]
                );
            }
        }
    }
}