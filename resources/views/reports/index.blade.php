<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between">
            <h2 class="text-2xl font-bold text-base-content">
                <i class="fas fa-chart-bar mr-3"></i>Rapports & Analyses
            </h2>
            <div class="mt-2 md:mt-0">
                <div class="text-sm breadcrumbs">
                    <ul>
                        <li><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li>Rapports</li>
                    </ul>
                </div>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <!-- Introduction -->
        <div class="card bg-base-100 shadow-lg mb-8">
            <div class="card-body">
                <div class="flex items-start">
                    <div class="flex-shrink-0 p-3 rounded-lg bg-primary/10 mr-4">
                        <i class="fas fa-chart-pie text-2xl text-primary"></i>
                    </div>
                    <div>
                        <h3 class="card-title mb-2">Analysez vos finances</h3>
                        <p class="text-base-content/70">
                            Consultez des rapports détaillés, visualisez vos tendances financières 
                            et exportez vos données pour une analyse approfondie.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Cartes de navigation rapide -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <!-- Rapport mensuel -->
            <a href="{{ route('reports.monthly') }}" class="card bg-base-100 shadow hover:shadow-xl transition-all">
                <div class="card-body items-center text-center">
                    <div class="p-3 rounded-full bg-blue-100 mb-4">
                        <i class="fas fa-calendar-alt text-2xl text-blue-600"></i>
                    </div>
                    <h4 class="card-title text-lg">Mensuel</h4>
                    <p class="text-sm text-base-content/70">Analyse détaillée du mois</p>
                </div>
            </a>

            <!-- Rapport annuel -->
            <a href="{{ route('reports.yearly') }}" class="card bg-base-100 shadow hover:shadow-xl transition-all">
                <div class="card-body items-center text-center">
                    <div class="p-3 rounded-full bg-green-100 mb-4">
                        <i class="fas fa-chart-line text-2xl text-green-600"></i>
                    </div>
                    <h4 class="card-title text-lg">Annuel</h4>
                    <p class="text-sm text-base-content/70">Évolution sur l'année</p>
                </div>
            </a>

            <!-- Par catégorie -->
            <a href="{{ route('reports.category') }}" class="card bg-base-100 shadow hover:shadow-xl transition-all">
                <div class="card-body items-center text-center">
                    <div class="p-3 rounded-full bg-purple-100 mb-4">
                        <i class="fas fa-tags text-2xl text-purple-600"></i>
                    </div>
                    <h4 class="card-title text-lg">Par Catégorie</h4>
                    <p class="text-sm text-base-content/70">Répartition des dépenses</p>
                </div>
            </a>

            <!-- Tendances -->
            <a href="{{ route('reports.trends') }}" class="card bg-base-100 shadow hover:shadow-xl transition-all">
                <div class="card-body items-center text-center">
                    <div class="p-3 rounded-full bg-orange-100 mb-4">
                        <i class="fas fa-trend-up text-2xl text-orange-600"></i>
                    </div>
                    <h4 class="card-title text-lg">Tendances</h4>
                    <p class="text-sm text-base-content/70">Analyses prédictives</p>
                </div>
            </a>
        </div>

        <!-- Statistiques rapides -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
            <!-- Mois en cours -->
            <div class="card bg-base-100 shadow">
                <div class="card-body">
                    <h4 class="card-title mb-4">
                        <i class="fas fa-calendar-day mr-2"></i>Ce mois-ci
                    </h4>
                    @php
                        $currentMonth = now()->format('Y-m');
                        $monthExpenses = auth()->user()->transactions()
                            ->where('type', 'expense')
                            ->where('date', 'like', $currentMonth . '%')
                            ->sum('amount');
                        $monthIncomes = auth()->user()->transactions()
                            ->where('type', 'income')
                            ->where('date', 'like', $currentMonth . '%')
                            ->sum('amount');
                        $monthBalance = $monthIncomes - $monthExpenses;
                    @endphp
                    <div class="space-y-3">
                        <div>
                            <div class="flex justify-between mb-1">
                                <span class="text-sm text-base-content/70">Dépenses</span>
                                <span class="font-bold text-error">{{ number_format($monthExpenses, 0, ',', ' ') }} FDJ</span>
                            </div>
                        </div>
                        <div>
                            <div class="flex justify-between mb-1">
                                <span class="text-sm text-base-content/70">Revenus</span>
                                <span class="font-bold text-success">{{ number_format($monthIncomes, 0, ',', ' ') }} FDJ</span>
                            </div>
                        </div>
                        <div class="pt-3 border-t">
                            <div class="flex justify-between">
                                <span class="text-sm text-base-content/70">Balance</span>
                                <span class="font-bold {{ $monthBalance >= 0 ? 'text-success' : 'text-error' }}">
                                    {{ number_format($monthBalance, 0, ',', ' ') }} FDJ
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Catégorie principale -->
            <div class="card bg-base-100 shadow">
                <div class="card-body">
                    <h4 class="card-title mb-4">
                        <i class="fas fa-chart-pie mr-2"></i>Top Catégories
                    </h4>
                    @php
                        $topCategories = auth()->user()->categories()
                            ->withSum(['transactions as total_amount' => function($query) {
                                $query->where('type', 'expense')
                                      ->whereMonth('date', now()->month)
                                      ->whereYear('date', now()->year);
                            }], 'amount')
                            ->where('type', 'expense')
                            ->orderBy('total_amount', 'desc')
                            ->take(3)
                            ->get();
                    @endphp
                    <div class="space-y-4">
                        @foreach($topCategories as $category)
                            <div>
                                <div class="flex justify-between mb-1">
                                    <div class="flex items-center">
                                        <div class="w-6 h-6 rounded mr-2" style="background-color: {{ $category->color }}"></div>
                                        <span class="text-sm">{{ $category->name }}</span>
                                    </div>
                                    <span class="font-bold">{{ number_format($category->total_amount ?? 0, 0, ',', ' ') }} FDJ</span>
                                </div>
                                <div class="w-full bg-base-300 rounded-full h-2">
                                    <div class="h-2 rounded-full" style="width: {{ min(100, ($category->total_amount / max(1, $monthExpenses)) * 100) }}%; background-color: {{ $category->color }}"></div>
                                </div>
                            </div>
                        @endforeach
                        @if($topCategories->isEmpty())
                            <p class="text-center text-base-content/50 py-4">Aucune donnée ce mois-ci</p>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Actions rapides -->
            <div class="card bg-base-100 shadow">
                <div class="card-body">
                    <h4 class="card-title mb-4">
                        <i class="fas fa-download mr-2"></i>Export
                    </h4>
                    <p class="text-sm text-base-content/70 mb-4">
                        Exportez vos données pour analyse externe ou sauvegarde.
                    </p>
                    <div class="space-y-3">
                        <a href="{{ route('reports.export', ['format' => 'excel']) }}" class="btn btn-success btn-block">
                            <i class="fas fa-file-excel mr-2"></i>Excel
                        </a>
                        <a href="{{ route('reports.export', ['format' => 'pdf']) }}" class="btn btn-error btn-block">
                            <i class="fas fa-file-pdf mr-2"></i>PDF
                        </a>
                        <button onclick="document.getElementById('custom-export').showModal()" class="btn btn-outline btn-block">
                            <i class="fas fa-cog mr-2"></i>Export personnalisé
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Derniers rapports générés -->
        <div class="card bg-base-100 shadow">
            <div class="card-body">
                <h4 class="card-title mb-4">
                    <i class="fas fa-history mr-2"></i>Dernières analyses
                </h4>
                <div class="overflow-x-auto">
                    <table class="table table-zebra">
                        <thead>
                            <tr>
                                <th>Type</th>
                                <th>Période</th>
                                <th>Dépenses</th>
                                <th>Revenus</th>
                                <th>Balance</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach(['2025-12', '2025-11', '2025-10'] as $month)
                                @php
                                    $monthDate = \Carbon\Carbon::createFromFormat('Y-m', $month);
                                    $expenses = auth()->user()->transactions()
                                        ->where('type', 'expense')
                                        ->whereYear('date', $monthDate->year)
                                        ->whereMonth('date', $monthDate->month)
                                        ->sum('amount');
                                    $incomes = auth()->user()->transactions()
                                        ->where('type', 'income')
                                        ->whereYear('date', $monthDate->year)
                                        ->whereMonth('date', $monthDate->month)
                                        ->sum('amount');
                                @endphp
                                <tr>
                                    <td>Mensuel</td>
                                    <td>{{ $monthDate->translatedFormat('F Y') }}</td>
                                    <td class="text-error">{{ number_format($expenses, 0, ',', ' ') }} FDJ</td>
                                    <td class="text-success">{{ number_format($incomes, 0, ',', ' ') }} FDJ</td>
                                    <td class="{{ ($incomes - $expenses) >= 0 ? 'text-success' : 'text-error' }}">
                                        {{ number_format($incomes - $expenses, 0, ',', ' ') }} FDJ
                                    </td>
                                    <td>
                                        <a href="{{ route('reports.monthly', ['year' => $monthDate->year, 'month' => $monthDate->month]) }}" 
                                           class="btn btn-xs btn-outline">
                                            Voir
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Export Personnalisé -->
    <dialog id="custom-export" class="modal">
        <div class="modal-box">
            <h3 class="font-bold text-lg">Export personnalisé</h3>
            <form method="GET" action="{{ route('reports.export') }}">
                @csrf
                <div class="space-y-4 py-4">
                    <div class="form-control">
                        <label class="label">
                            <span class="label-text">Format</span>
                        </label>
                        <select name="format" class="select select-bordered">
                            <option value="excel">Excel (.xlsx)</option>
                            <option value="pdf">PDF (.pdf)</option>
                            <option value="csv">CSV (.csv)</option>
                        </select>
                    </div>
                    
                    <div class="form-control">
                        <label class="label">
                            <span class="label-text">Période</span>
                        </label>
                        <select name="period" class="select select-bordered">
                            <option value="month">Ce mois</option>
                            <option value="last_month">Mois dernier</option>
                            <option value="year">Cette année</option>
                            <option value="all">Toutes les données</option>
                            <option value="custom">Personnalisée</option>
                        </select>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-4 hidden" id="custom-dates">
                        <div class="form-control">
                            <label class="label">
                                <span class="label-text">Date début</span>
                            </label>
                            <input type="date" name="start_date" class="input input-bordered">
                        </div>
                        <div class="form-control">
                            <label class="label">
                                <span class="label-text">Date fin</span>
                            </label>
                            <input type="date" name="end_date" class="input input-bordered">
                        </div>
                    </div>
                    
                    <div class="form-control">
                        <label class="label">
                            <span class="label-text">Inclure</span>
                        </label>
                        <div class="space-y-2">
                            <label class="flex items-center">
                                <input type="checkbox" name="include_transactions" class="checkbox checkbox-sm mr-2" checked>
                                <span class="text-sm">Transactions</span>
                            </label>
                            <label class="flex items-center">
                                <input type="checkbox" name="include_categories" class="checkbox checkbox-sm mr-2" checked>
                                <span class="text-sm">Catégories</span>
                            </label>
                            <label class="flex items-center">
                                <input type="checkbox" name="include_budgets" class="checkbox checkbox-sm mr-2" checked>
                                <span class="text-sm">Budgets</span>
                            </label>
                        </div>
                    </div>
                </div>
                
                <div class="modal-action">
                    <button type="button" class="btn" onclick="document.getElementById('custom-export').close()">
                        Annuler
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-download mr-2"></i>Exporter
                    </button>
                </div>
            </form>
        </div>
        <form method="dialog" class="modal-backdrop">
            <button>close</button>
        </form>
    </dialog>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Gérer l'affichage des dates personnalisées
            const periodSelect = document.querySelector('select[name="period"]');
            const customDates = document.getElementById('custom-dates');
            
            periodSelect.addEventListener('change', function() {
                if (this.value === 'custom') {
                    customDates.classList.remove('hidden');
                } else {
                    customDates.classList.add('hidden');
                }
            });
        });
    </script>
    @endpush
</x-app-layout>