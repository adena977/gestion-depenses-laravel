<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between">
            <h2 class="text-2xl font-bold text-base-content">
                <i class="fas fa-tags mr-3"></i>Catégories
            </h2>
            <div class="mt-2 md:mt-0 flex space-x-2">
                <a href="{{ route('categories.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus mr-2"></i>Nouvelle catégorie
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <!-- Statistiques -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <!-- Catégories dépenses -->
            <div class="card bg-base-100 shadow">
                <div class="card-body">
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="stat-title text-base-content/70">Catégories de dépenses</div>
                            <div class="stat-value text-error">{{ $expenseCategories->count() }}</div>
                            <div class="stat-desc">Personnalisées</div>
                        </div>
                        <div class="stat-figure text-error">
                            <i class="fas fa-arrow-down text-3xl"></i>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Catégories revenus -->
            <div class="card bg-base-100 shadow">
                <div class="card-body">
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="stat-title text-base-content/70">Catégories de revenus</div>
                            <div class="stat-value text-success">{{ $incomeCategories->count() }}</div>
                            <div class="stat-desc">Personnalisées</div>
                        </div>
                        <div class="stat-figure text-success">
                            <i class="fas fa-arrow-up text-3xl"></i>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Total -->
            <div class="card bg-base-100 shadow">
                <div class="card-body">
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="stat-title text-base-content/70">Total catégories</div>
                            <div class="stat-value text-primary">{{ $categories->count() }}</div>
                            <div class="stat-desc">Inclut {{ $defaultCategories->count() }} par défaut</div>
                        </div>
                        <div class="stat-figure text-primary">
                            <i class="fas fa-tags text-3xl"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Onglets -->
        <div class="tabs tabs-boxed mb-6">
            <a class="tab {{ $activeTab === 'expense' ? 'tab-active' : '' }}" 
               href="{{ route('categories.index', ['tab' => 'expense']) }}">
                <i class="fas fa-arrow-down mr-2"></i>Dépenses
                <span class="badge badge-error ml-2">{{ $expenseCategories->count() }}</span>
            </a>
            <a class="tab {{ $activeTab === 'income' ? 'tab-active' : '' }}" 
               href="{{ route('categories.index', ['tab' => 'income']) }}">
                <i class="fas fa-arrow-up mr-2"></i>Revenus
                <span class="badge badge-success ml-2">{{ $incomeCategories->count() }}</span>
            </a>
            <a class="tab {{ $activeTab === 'all' ? 'tab-active' : '' }}" 
               href="{{ route('categories.index', ['tab' => 'all']) }}">
                <i class="fas fa-list mr-2"></i>Toutes
                <span class="badge badge-primary ml-2">{{ $categories->count() }}</span>
            </a>
        </div>

        <!-- Catégories de dépenses -->
        @if($activeTab === 'expense' || $activeTab === 'all')
            <div class="card bg-base-100 shadow-lg mb-6">
                <div class="card-body">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="card-title text-error">
                            <i class="fas fa-arrow-down mr-2"></i>Catégories de dépenses
                        </h3>
                        <span class="badge badge-error">{{ $expenseCategories->count() }} catégories</span>
                    </div>
                    
                    @if($expenseCategories->count() > 0)
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            @foreach($expenseCategories as $category)
                                @include('categories.partials.category-card', ['category' => $category])
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-8">
                            <div class="inline-block p-4 rounded-full bg-error/10 mb-4">
                                <i class="fas fa-arrow-down text-3xl text-error"></i>
                            </div>
                            <h4 class="text-lg font-semibold text-base-content/70 mb-2">Aucune catégorie de dépense</h4>
                            <p class="text-base-content/50 mb-4">
                                Créez vos premières catégories pour organiser vos dépenses.
                            </p>
                            <a href="{{ route('categories.create', ['type' => 'expense']) }}" class="btn btn-error">
                                <i class="fas fa-plus mr-2"></i>Créer une catégorie de dépense
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        @endif

        <!-- Catégories de revenus -->
        @if($activeTab === 'income' || $activeTab === 'all')
            <div class="card bg-base-100 shadow-lg mb-6">
                <div class="card-body">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="card-title text-success">
                            <i class="fas fa-arrow-up mr-2"></i>Catégories de revenus
                        </h3>
                        <span class="badge badge-success">{{ $incomeCategories->count() }} catégories</span>
                    </div>
                    
                    @if($incomeCategories->count() > 0)
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            @foreach($incomeCategories as $category)
                                @include('categories.partials.category-card', ['category' => $category])
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-8">
                            <div class="inline-block p-4 rounded-full bg-success/10 mb-4">
                                <i class="fas fa-arrow-up text-3xl text-success"></i>
                            </div>
                            <h4 class="text-lg font-semibold text-base-content/70 mb-2">Aucune catégorie de revenu</h4>
                            <p class="text-base-content/50 mb-4">
                                Créez vos premières catégories pour organiser vos revenus.
                            </p>
                            <a href="{{ route('categories.create', ['type' => 'income']) }}" class="btn btn-success">
                                <i class="fas fa-plus mr-2"></i>Créer une catégorie de revenu
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        @endif

        <!-- Catégories par défaut (lecture seule) -->
        <div class="card bg-base-100 shadow-lg">
            <div class="card-body">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="card-title">
                        <i class="fas fa-star mr-2"></i>Catégories par défaut
                    </h3>
                    <span class="badge">{{ $defaultCategories->count() }} catégories</span>
                </div>
                
                <div class="alert alert-info mb-6">
                    <i class="fas fa-info-circle"></i>
                    <span>
                        Les catégories par défaut sont pré-définies et ne peuvent pas être modifiées ou supprimées.
                        Vous pouvez les utiliser directement dans vos transactions.
                    </span>
                </div>
                
                @if($defaultCategories->count() > 0)
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                        @foreach($defaultCategories as $category)
                            <div class="border border-base-300 rounded-lg p-4 opacity-80">
                                <div class="flex items-center mb-3">
                                    <div class="w-10 h-10 rounded-lg flex items-center justify-center mr-3" 
                                         style="background-color: {{ $category->color }}20">
                                        <i class="fas fa-{{ $category->icon }}" style="color: {{ $category->color }}"></i>
                                    </div>
                                    <div>
                                        <div class="font-bold">{{ $category->name }}</div>
                                        <div class="text-xs {{ $category->type === 'expense' ? 'text-error' : 'text-success' }}">
                                            {{ $category->type === 'expense' ? 'Dépense' : 'Revenu' }}
                                        </div>
                                    </div>
                                </div>
                                <div class="text-xs text-base-content/50">
                                    <i class="fas fa-lock mr-1"></i> Catégorie par défaut
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-4">
                        <p class="text-base-content/50">Aucune catégorie par défaut disponible.</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Actions rapides -->
        <div class="mt-8 grid grid-cols-1 md:grid-cols-2 gap-4">
            <a href="{{ route('transactions.index') }}" class="btn btn-outline">
                <i class="fas fa-exchange-alt mr-2"></i>Voir toutes les transactions
            </a>
            <a href="{{ route('categories.create') }}" class="btn btn-primary">
                <i class="fas fa-plus-circle mr-2"></i>Créer une nouvelle catégorie
            </a>
        </div>
    </div>

    @push('styles')
    <style>
        .category-card {
            transition: all 0.3s ease;
        }
        
        .category-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
        }
        
        .usage-bar {
            height: 4px;
            border-radius: 2px;
            transition: width 0.3s ease;
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