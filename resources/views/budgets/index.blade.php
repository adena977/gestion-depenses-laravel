<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between">
            <h2 class="text-2xl font-bold text-base-content">
                <i class="fas fa-chart-pie mr-3"></i>Budgets
            </h2>
            <div class="mt-2 md:mt-0 flex space-x-2">
                <a href="{{ route('budgets.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus mr-2"></i>Nouveau budget
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <!-- Statistiques -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <!-- Budgets actifs -->
            <div class="card bg-base-100 shadow">
                <div class="card-body">
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="stat-title text-base-content/70">Budgets actifs</div>
                            <div class="stat-value text-primary">{{ $activeBudgets->count() }}</div>
                            <div class="stat-desc">{{ $totalBudgets }} au total</div>
                        </div>
                        <div class="stat-figure text-primary">
                            <i class="fas fa-chart-line text-3xl"></i>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Budget total -->
            <div class="card bg-base-100 shadow">
                <div class="card-body">
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="stat-title text-base-content/70">Budget total</div>
                            <div class="stat-value text-success">{{ number_format($totalBudgetAmount, 2, ',', ' ') }} FDJ</div>
                            <div class="stat-desc">Ce mois</div>
                        </div>
                        <div class="stat-figure text-success">
                            <i class="fas fa-wallet text-3xl"></i>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Dépensé -->
            <div class="card bg-base-100 shadow">
                <div class="card-body">
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="stat-title text-base-content/70">Dépensé</div>
                            <div class="stat-value text-error">{{ number_format($totalSpent, 2, ',', ' ') }} FDJ</div>
                            <div class="stat-desc">{{ $totalSpentPercentage }}% du budget</div>
                        </div>
                        <div class="stat-figure text-error">
                            <i class="fas fa-money-bill-wave text-3xl"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Onglets -->
        <div class="tabs tabs-boxed mb-6">
            <a class="tab {{ $activeTab === 'active' ? 'tab-active' : '' }}" 
               href="{{ route('budgets.index', ['tab' => 'active']) }}">
                <i class="fas fa-play-circle mr-2"></i>Actifs
                <span class="badge badge-primary ml-2">{{ $activeBudgets->count() }}</span>
            </a>
            <a class="tab {{ $activeTab === 'all' ? 'tab-active' : '' }}" 
               href="{{ route('budgets.index', ['tab' => 'all']) }}">
                <i class="fas fa-list mr-2"></i>Tous
                <span class="badge ml-2">{{ $totalBudgets }}</span>
            </a>
            <a class="tab {{ $activeTab === 'expired' ? 'tab-active' : '' }}" 
               href="{{ route('budgets.index', ['tab' => 'expired']) }}">
                <i class="fas fa-calendar-times mr-2"></i>Expirés
                <span class="badge ml-2">{{ $expiredBudgets->count() }}</span>
            </a>
        </div>

        <!-- Alertes (si besoin) -->
        @if($budgetAlerts->count() > 0)
            <div class="alert alert-warning mb-6">
                <i class="fas fa-exclamation-triangle"></i>
                <div>
                    <span class="font-bold">{{ $budgetAlerts->count() }} budget(s) nécessitent votre attention</span>
                    <div class="text-sm mt-1">
                        Certains budgets sont proches ou ont dépassé leurs limites.
                    </div>
                </div>
            </div>
        @endif

        <!-- Budgets actifs -->
        @if($activeTab === 'active' || $activeTab === 'all')
            <div class="card bg-base-100 shadow-lg mb-6">
                <div class="card-body">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="card-title">
                            <i class="fas fa-play-circle mr-3"></i>Budgets actifs
                        </h3>
                        <span class="badge badge-primary">{{ $activeBudgets->count() }} budget(s)</span>
                    </div>
                    
                    @if($activeBudgets->count() > 0)
                        <div class="space-y-6">
                            @foreach($activeBudgets as $budget)
                                @include('budgets.partials.budget-card', ['budget' => $budget])
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-12">
                            <div class="inline-block p-6 rounded-full bg-base-200 mb-4">
                                <i class="fas fa-chart-pie text-4xl text-base-content/30"></i>
                            </div>
                            <h4 class="text-xl font-semibold text-base-content/70 mb-2">Aucun budget actif</h4>
                            <p class="text-base-content/50 mb-6">
                                Créez votre premier budget pour suivre vos dépenses.
                            </p>
                            <a href="{{ route('budgets.create') }}" class="btn btn-primary btn-lg">
                                <i class="fas fa-plus mr-2"></i>Créer un budget
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        @endif

        <!-- Budgets expirés -->
        @if($activeTab === 'expired' || $activeTab === 'all')
            @if($expiredBudgets->count() > 0)
                <div class="card bg-base-100 shadow-lg mb-6">
                    <div class="card-body">
                        <div class="flex justify-between items-center mb-6">
                            <h3 class="card-title">
                                <i class="fas fa-calendar-times mr-3"></i>Budgets expirés
                            </h3>
                            <span class="badge">{{ $expiredBudgets->count() }} budget(s)</span>
                        </div>
                        
                        <div class="space-y-6 opacity-70">
                            @foreach($expiredBudgets as $budget)
                                <div class="border border-base-300 rounded-lg p-4">
                                    <div class="flex justify-between items-start mb-4">
                                        <div class="flex items-center">
                                            <div class="w-10 h-10 rounded-lg flex items-center justify-center mr-3" 
                                                 style="background-color: {{ $budget->category->color }}20">
                                                <i class="fas fa-{{ $budget->category->icon }}" 
                                                   style="color: {{ $budget->category->color }}"></i>
                                            </div>
                                            <div>
                                                <div class="font-bold">{{ $budget->category->name }}</div>
                                                <div class="text-sm text-base-content/70">
                                                    {{ $budget->period === 'monthly' ? 'Mensuel' : ($budget->period === 'weekly' ? 'Hebdomadaire' : 'Annuel') }}
                                                </div>
                                            </div>
                                        </div>
                                        <div class="text-right">
                                            <div class="text-sm text-base-content/70">Terminé le</div>
                                            <div class="font-medium">{{ $budget->end_date->format('d/m/Y') }}</div>
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <div class="flex justify-between text-sm mb-1">
                                            <span>Budget: {{ number_format($budget->amount, 2, ',', ' ') }} FDJ</span>
                                            <span>Dépensé: {{ number_format($budget->spent_amount, 2, ',', ' ') }} FDJ</span>
                                        </div>
                                        <div class="w-full bg-base-300 rounded-full h-2">
                                            <div class="h-2 rounded-full 
                                                @if($budget->progress_percentage > 100) bg-error
                                                @elseif($budget->progress_percentage >= 80) bg-warning
                                                @else bg-success @endif"
                                                style="width: {{ min($budget->progress_percentage, 100) }}%">
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="flex justify-between text-sm">
                                        <div>
                                            <span class="font-medium">
                                                {{ round($budget->progress_percentage, 1) }}%
                                            </span>
                                            <span class="text-base-content/70 ml-2">
                                                @if($budget->progress_percentage > 100)
                                                    <i class="fas fa-exclamation-circle text-error mr-1"></i>Dépassé
                                                @else
                                                    Terminé
                                                @endif
                                            </span>
                                        </div>
                                        <div class="flex space-x-2">
                                            <a href="{{ route('budgets.create', ['category_id' => $budget->category_id]) }}" 
                                               class="btn btn-ghost btn-xs">
                                                <i class="fas fa-redo"></i> Renouveler
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif
        @endif

        <!-- Budgets par catégorie (graphique) -->
        <div class="card bg-base-100 shadow-lg">
            <div class="card-body">
                <h3 class="card-title mb-6">
                    <i class="fas fa-chart-bar mr-3"></i>Synthèse par catégorie
                </h3>
                
                @if($budgetsByCategory->count() > 0)
                    <div class="space-y-4">
                        @foreach($budgetsByCategory as $categoryBudget)
                            @php
                                $category = $categoryBudget['category'];
                                $budget = $categoryBudget['budget'];
                                $spent = $categoryBudget['spent'];
                                $percentage = $categoryBudget['percentage'];
                            @endphp
                            
                            <div class="flex items-center justify-between p-3 bg-base-200 rounded-lg">
                                <div class="flex items-center flex-1">
                                    <div class="w-10 h-10 rounded-lg flex items-center justify-center mr-3" 
                                         style="background-color: {{ $category->color }}20">
                                        <i class="fas fa-{{ $category->icon }}" 
                                           style="color: {{ $category->color }}"></i>
                                    </div>
                                    <div class="flex-1">
                                        <div class="font-medium">{{ $category->name }}</div>
                                        <div class="text-sm text-base-content/70">
                                            Budget: {{ number_format($budget?->amount ?? 0, 2, ',', ' ') }} FDJ
                                            • Dépensé: {{ number_format($spent, 2, ',', ' ') }} FDJ
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="text-right ml-4">
                                    @if($budget)
                                        <div class="font-bold {{ $percentage >= 100 ? 'text-error' : ($percentage >= 80 ? 'text-warning' : 'text-success') }}">
                                            {{ round($percentage, 1) }}%
                                        </div>
                                        <div class="text-xs text-base-content/70">
                                            {{ $budget->period === 'monthly' ? 'Mensuel' : 'Autre' }}
                                        </div>
                                    @else
                                        <div class="text-sm text-base-content/50">Aucun budget</div>
                                        <a href="{{ route('budgets.create', ['category_id' => $category->id]) }}" 
                                           class="btn btn-ghost btn-xs mt-1">
                                            <i class="fas fa-plus"></i> Créer
                                        </a>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-8">
                        <i class="fas fa-chart-bar text-4xl text-base-content/30 mb-4"></i>
                        <p class="text-base-content/50">Aucune catégorie avec budget défini</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Actions rapides -->
        <div class="mt-8 grid grid-cols-1 md:grid-cols-3 gap-4">
            <a href="{{ route('budgets.create') }}" class="btn btn-primary">
                <i class="fas fa-plus-circle mr-2"></i>Nouveau budget
            </a>
            <a href="{{ route('categories.index') }}" class="btn btn-outline">
                <i class="fas fa-tags mr-2"></i>Gérer les catégories
            </a>
            <a href="{{ route('reports.index') }}" class="btn btn-outline">
                <i class="fas fa-chart-bar mr-2"></i>Voir les rapports
            </a>
        </div>
    </div>

    @push('styles')
    <style>
        .budget-card {
            transition: all 0.3s ease;
        }
        
        .budget-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
        }
        
        .progress-bar {
            height: 8px;
            border-radius: 4px;
            transition: width 0.5s ease;
        }
        
        .tab {
            transition: all 0.2s ease;
        }
        
        .tab:hover:not(.tab-active) {
            background-color: hsl(var(--b3));
        }
    </style>
    @endpush
</x-app-layout>