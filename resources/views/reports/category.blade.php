<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between">
            <h2 class="text-2xl font-bold text-base-content">
                <i class="fas fa-tags mr-3"></i>Analyse par Catégorie
            </h2>
            <div class="mt-2 md:mt-0">
                <div class="text-sm breadcrumbs">
                    <ul>
                        <li><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li><a href="{{ route('reports.index') }}">Rapports</a></li>
                        <li>Par Catégorie</li>
                    </ul>
                </div>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <!-- Filtres -->
        <div class="card bg-base-100 shadow-lg mb-6">
            <div class="card-body">
                <form method="GET" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="form-control">
                            <label class="label">
                                <span class="label-text">Période</span>
                            </label>
                            <select name="period" class="select select-bordered" onchange="this.form.submit()">
                                <option value="month" {{ $period === 'month' ? 'selected' : '' }}>Ce mois</option>
                                <option value="year" {{ $period === 'year' ? 'selected' : '' }}>Cette année</option>
                                <option value="custom" {{ $period === 'custom' ? 'selected' : '' }}>Personnalisée</option>
                            </select>
                        </div>
                        
                        @if($period === 'custom')
                            <div class="form-control">
                                <label class="label">
                                    <span class="label-text">Date début</span>
                                </label>
                                <input type="date" name="start_date" value="{{ $startDate }}" class="input input-bordered">
                            </div>
                            <div class="form-control">
                                <label class="label">
                                    <span class="label-text">Date fin</span>
                                </label>
                                <input type="date" name="end_date" value="{{ $endDate }}" class="input input-bordered">
                            </div>
                        @endif
                    </div>
                    
                    <div class="flex justify-between">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-filter mr-2"></i>Appliquer les filtres
                        </button>
                        <a href="{{ route('reports.category') }}" class="btn btn-outline">
                            <i class="fas fa-redo mr-2"></i>Réinitialiser
                        </a>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Période sélectionnée -->
        <div class="card bg-base-100 shadow-lg mb-8">
            <div class="card-body">
                <div class="flex flex-col md:flex-row md:items-center justify-between">
                    <div>
                        <h3 class="text-xl font-bold">
                            Analyse par catégorie
                        </h3>
                        <p class="text-base-content/70">
                            @if($period === 'month')
                                Mois de {{ \Carbon\Carbon::parse($startDate)->translatedFormat('F Y') }}
                            @elseif($period === 'year')
                                Année {{ \Carbon\Carbon::parse($startDate)->year }}
                            @else
                                Période du {{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }} au {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}
                            @endif
                        </p>
                    </div>
                    
                    <div class="mt-4 md:mt-0">
                        <div class="text-center">
                            <div class="text-sm text-base-content/70">Total analysé</div>
                            <div class="text-2xl font-bold">
                                {{ number_format($categoryStats->sum('total'), 0, ',', ' ') }} FDJ
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Statistiques globales -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
            <!-- Dépenses par catégorie -->
            <div class="card bg-base-100 shadow">
                <div class="card-body">
                    <h4 class="card-title mb-4">
                        <i class="fas fa-arrow-down text-error mr-2"></i>Top Dépenses
                    </h4>
                    @php
                        $expenseCategories = $categoryStats->filter(fn($stat) => $stat['expenses'] > 0)
                            ->sortByDesc('expenses')
                            ->take(5);
                    @endphp
                    
                    @if($expenseCategories->count() > 0)
                        <div class="space-y-4">
                            @foreach($expenseCategories as $stat)
                                <div>
                                    <div class="flex justify-between mb-1">
                                        <div class="flex items-center">
                                            <div class="w-4 h-4 rounded mr-2" style="background-color: {{ $stat['category']->color }}"></div>
                                            <span class="text-sm">{{ $stat['category']->name }}</span>
                                        </div>
                                        <span class="font-bold text-error">
                                            {{ number_format($stat['expenses'], 0, ',', ' ') }} FDJ
                                        </span>
                                    </div>
                                    <div class="w-full bg-base-300 rounded-full h-2">
                                        <div class="h-2 rounded-full" 
                                             style="width: {{ min(100, ($stat['expenses'] / max(1, $expenseCategories->sum('expenses'))) * 100) }}%; 
                                                    background-color: {{ $stat['category']->color }}"></div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-center text-base-content/50 py-4">Aucune dépense dans cette période</p>
                    @endif
                </div>
            </div>
            
            <!-- Revenus par catégorie -->
            <div class="card bg-base-100 shadow">
                <div class="card-body">
                    <h4 class="card-title mb-4">
                        <i class="fas fa-arrow-up text-success mr-2"></i>Top Revenus
                    </h4>
                    @php
                        $incomeCategories = $categoryStats->filter(fn($stat) => $stat['incomes'] > 0)
                            ->sortByDesc('incomes')
                            ->take(5);
                    @endphp
                    
                    @if($incomeCategories->count() > 0)
                        <div class="space-y-4">
                            @foreach($incomeCategories as $stat)
                                <div>
                                    <div class="flex justify-between mb-1">
                                        <div class="flex items-center">
                                            <div class="w-4 h-4 rounded mr-2" style="background-color: {{ $stat['category']->color }}"></div>
                                            <span class="text-sm">{{ $stat['category']->name }}</span>
                                        </div>
                                        <span class="font-bold text-success">
                                            {{ number_format($stat['incomes'], 0, ',', ' ') }} FDJ
                                        </span>
                                    </div>
                                    <div class="w-full bg-base-300 rounded-full h-2">
                                        <div class="h-2 rounded-full" 
                                             style="width: {{ min(100, ($stat['incomes'] / max(1, $incomeCategories->sum('incomes'))) * 100) }}%; 
                                                    background-color: {{ $stat['category']->color }}"></div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-center text-base-content/50 py-4">Aucun revenu dans cette période</p>
                    @endif
                </div>
            </div>
        </div>
        
        <!-- Tableau détaillé -->
        <div class="card bg-base-100 shadow">
            <div class="card-body">
                <div class="flex justify-between items-center mb-6">
                    <h4 class="card-title">
                        <i class="fas fa-list mr-2"></i>Détails par catégorie
                    </h4>
                    <span class="badge badge-primary">{{ $categoryStats->count() }} catégories</span>
                </div>
                
                @if($categoryStats->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="table table-zebra">
                            <thead>
                                <tr>
                                    <th>Catégorie</th>
                                    <th>Type</th>
                                    <th class="text-right">Dépenses</th>
                                    <th class="text-right">Revenus</th>
                                    <th class="text-right">Total</th>
                                    <th class="text-right">% du total</th>
                                    <th>Transactions</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($categoryStats as $stat)
                                    <tr>
                                        <td>
                                            <div class="flex items-center">
                                                <div class="w-4 h-4 rounded mr-2" style="background-color: {{ $stat['category']->color }}"></div>
                                                {{ $stat['category']->name }}
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge {{ $stat['category']->type === 'expense' ? 'badge-error' : 'badge-success' }}">
                                                {{ $stat['category']->type === 'expense' ? 'Dépense' : 'Revenu' }}
                                            </span>
                                        </td>
                                        <td class="text-right text-error">
                                            {{ number_format($stat['expenses'], 0, ',', ' ') }} FDJ
                                        </td>
                                        <td class="text-right text-success">
                                            {{ number_format($stat['incomes'], 0, ',', ' ') }} FDJ
                                        </td>
                                        <td class="text-right font-bold">
                                            {{ number_format($stat['total'], 0, ',', ' ') }} FDJ
                                        </td>
                                        <td class="text-right">
                                            <div class="flex items-center justify-end">
                                                <span class="mr-2">{{ number_format($stat['percentage'], 1) }}%</span>
                                                <div class="w-16 bg-base-300 rounded-full h-2">
                                                    <div class="h-2 rounded-full" 
                                                         style="width: {{ min(100, $stat['percentage']) }}%; 
                                                                background-color: {{ $stat['category']->color }}"></div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge badge-outline">{{ $stat['transaction_count'] }}</span>
                                        </td>
                                        <td>
                                            <a href="{{ route('categories.show', $stat['category']) }}" class="btn btn-xs btn-outline">
                                                Voir
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr class="font-bold">
                                    <td colspan="2">Totaux</td>
                                    <td class="text-right text-error">
                                        {{ number_format($categoryStats->sum('expenses'), 0, ',', ' ') }} FDJ
                                    </td>
                                    <td class="text-right text-success">
                                        {{ number_format($categoryStats->sum('incomes'), 0, ',', ' ') }} FDJ
                                    </td>
                                    <td class="text-right">
                                        {{ number_format($categoryStats->sum('total'), 0, ',', ' ') }} FDJ
                                    </td>
                                    <td class="text-right">100%</td>
                                    <td class="text-center">
                                        {{ $categoryStats->sum('transaction_count') }}
                                    </td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                @else
                    <div class="text-center py-8">
                        <div class="inline-block p-4 rounded-full bg-base-300 mb-4">
                            <i class="fas fa-tags text-3xl text-base-content/50"></i>
                        </div>
                        <h4 class="text-lg font-semibold text-base-content/70 mb-2">
                            Aucune donnée pour cette période
                        </h4>
                        <p class="text-base-content/50 mb-4">
                            Commencez à ajouter des transactions pour voir les analyses par catégorie.
                        </p>
                        <a href="{{ route('transactions.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus mr-2"></i>Ajouter une transaction
                        </a>
                    </div>
                @endif
            </div>
        </div>
        
        <!-- Navigation -->
        <div class="flex justify-between mt-6">
            <a href="{{ route('reports.index') }}" class="btn btn-outline">
                <i class="fas fa-chevron-left mr-2"></i>Retour aux rapports
            </a>
            
            <div class="flex space-x-2">
                <a href="{{ route('reports.monthly') }}" class="btn btn-outline">
                    <i class="fas fa-calendar-alt mr-2"></i>Rapport mensuel
                </a>
                <a href="{{ route('reports.yearly') }}" class="btn btn-outline">
                    <i class="fas fa-chart-line mr-2"></i>Rapport annuel
                </a>
            </div>
            
            <a href="{{ route('categories.index') }}" class="btn btn-primary">
                <i class="fas fa-tags mr-2"></i>Gérer les catégories
            </a>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Gérer l'affichage des champs de date personnalisée
            const periodSelect = document.querySelector('select[name="period"]');
            
            function toggleCustomDates() {
                const form = periodSelect.closest('form');
                const dateInputs = form.querySelectorAll('input[type="date"]');
                
                if (periodSelect.value === 'custom') {
                    dateInputs.forEach(input => input.closest('.form-control').style.display = 'block');
                } else {
                    dateInputs.forEach(input => input.closest('.form-control').style.display = 'none');
                }
            }
            
            // Initialiser et écouter les changements
            toggleCustomDates();
            periodSelect.addEventListener('change', toggleCustomDates);
        });
    </script>
    @endpush
</x-app-layout>