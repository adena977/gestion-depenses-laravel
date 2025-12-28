<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between">
            <h2 class="text-2xl font-bold text-base-content">
                <i class="fas fa-calendar-alt mr-3"></i>Rapport Mensuel
            </h2>
            <div class="mt-2 md:mt-0">
                <div class="text-sm breadcrumbs">
                    <ul>
                        <li><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li><a href="{{ route('reports.index') }}">Rapports</a></li>
                        <li>Mensuel</li>
                    </ul>
                </div>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <!-- Filtres -->
        @include('reports.partials.filters')
        
        <!-- En-tête du mois -->
        <div class="card bg-base-100 shadow-lg mb-6">
            <div class="card-body">
                <div class="flex flex-col md:flex-row md:items-center justify-between">
                    <div>
                        <h3 class="text-xl font-bold">
                            {{ \Carbon\Carbon::create($year, $month, 1)->translatedFormat('F Y') }}
                        </h3>
                        <p class="text-base-content/70">
                            {{ $stats['total_days'] ?? 30 }} jours • 
                            {{ $stats['transaction_count'] ?? 0 }} transactions
                        </p>
                    </div>
                    
                    <div class="mt-4 md:mt-0 flex items-center space-x-4">
                        <div class="text-center">
                            <div class="text-sm text-base-content/70">Balance</div>
                            <div class="text-2xl font-bold {{ $stats['balance'] >= 0 ? 'text-success' : 'text-error' }}">
                                {{ number_format($stats['balance'] ?? 0, 0, ',', ' ') }} FDJ
                            </div>
                        </div>
                        
                        <div class="dropdown">
                            <button class="btn btn-outline">
                                <i class="fas fa-ellipsis-v"></i> Actions
                            </button>
                            <ul class="dropdown-content z-[1] menu p-2 shadow bg-base-100 rounded-box w-52">
                                <li>
                                    <a href="{{ route('reports.export', ['format' => 'pdf', 'period' => 'month', 'year' => $year, 'month' => $month]) }}">
                                        <i class="fas fa-file-pdf"></i> Exporter en PDF
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('reports.export', ['format' => 'excel', 'period' => 'month', 'year' => $year, 'month' => $month]) }}">
                                        <i class="fas fa-file-excel"></i> Exporter en Excel
                                    </a>
                                </li>
                                <li><div class="divider my-0"></div></li>
                                <li>
                                    <a href="{{ route('transactions.create') }}">
                                        <i class="fas fa-plus"></i> Ajouter une transaction
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Statistiques principales -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <!-- Dépenses -->
            <div class="card bg-base-100 shadow">
                <div class="card-body">
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="stat-title text-error">Dépenses totales</div>
                            <div class="stat-value text-error text-3xl">
                                {{ number_format($stats['total_expenses'] ?? 0, 0, ',', ' ') }} FDJ
                            </div>
                            <div class="stat-desc">
                                {{ $stats['expense_transactions'] ?? 0 }} transactions
                            </div>
                        </div>
                        <div class="stat-figure text-error">
                            <i class="fas fa-arrow-down text-3xl"></i>
                        </div>
                    </div>
                    @if(isset($stats['avg_daily_expense']))
                        <div class="mt-4 text-sm">
                            <div class="flex justify-between mb-1">
                                <span>Moyenne journalière</span>
                                <span class="font-bold">{{ number_format($stats['avg_daily_expense'], 0, ',', ' ') }} FDJ</span>
                            </div>
                            @if(isset($stats['expense_vs_last_month']))
                                <div class="flex justify-between">
                                    <span>Vs mois dernier</span>
                                    <span class="{{ $stats['expense_vs_last_month'] >= 0 ? 'text-error' : 'text-success' }}">
                                        {{ $stats['expense_vs_last_month'] >= 0 ? '+' : '' }}{{ number_format($stats['expense_vs_last_month'], 1, ',', ' ') }}%
                                    </span>
                                </div>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
            
            <!-- Revenus -->
            <div class="card bg-base-100 shadow">
                <div class="card-body">
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="stat-title text-success">Revenus totaux</div>
                            <div class="stat-value text-success text-3xl">
                                {{ number_format($stats['total_incomes'] ?? 0, 0, ',', ' ') }} FDJ
                            </div>
                            <div class="stat-desc">
                                {{ $stats['income_transactions'] ?? 0 }} transactions
                            </div>
                        </div>
                        <div class="stat-figure text-success">
                            <i class="fas fa-arrow-up text-3xl"></i>
                        </div>
                    </div>
                    @if(isset($stats['avg_income']))
                        <div class="mt-4 text-sm">
                            <div class="flex justify-between mb-1">
                                <span>Moyenne par transaction</span>
                                <span class="font-bold">{{ number_format($stats['avg_income'], 0, ',', ' ') }} FDJ</span>
                            </div>
                            @if(isset($stats['income_vs_last_month']))
                                <div class="flex justify-between">
                                    <span>Vs mois dernier</span>
                                    <span class="{{ $stats['income_vs_last_month'] >= 0 ? 'text-success' : 'text-error' }}">
                                        {{ $stats['income_vs_last_month'] >= 0 ? '+' : '' }}{{ number_format($stats['income_vs_last_month'], 1, ',', ' ') }}%
                                    </span>
                                </div>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
            
            <!-- Économies -->
            <div class="card bg-base-100 shadow">
                <div class="card-body">
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="stat-title">Taux d'épargne</div>
                            <div class="stat-value text-3xl {{ ($stats['savings_rate'] ?? 0) >= 0 ? 'text-success' : 'text-error' }}">
                                {{ number_format($stats['savings_rate'] ?? 0, 1, ',', ' ') }}%
                            </div>
                            <div class="stat-desc">
                                {{ number_format($stats['balance'] ?? 0, 0, ',', ' ') }} FDJ épargnés
                            </div>
                        </div>
                        <div class="stat-figure {{ ($stats['savings_rate'] ?? 0) >= 0 ? 'text-success' : 'text-error' }}">
                            <i class="fas fa-piggy-bank text-3xl"></i>
                        </div>
                    </div>
                    @if(isset($stats['savings_rate']))
                        <div class="mt-4">
                            <div class="text-sm mb-2">Progression de l'épargne</div>
                            <div class="w-full bg-base-300 rounded-full h-3">
                                <div class="h-3 rounded-full {{ $stats['savings_rate'] >= 0 ? 'bg-success' : 'bg-error' }}" 
                                     style="width: {{ min(100, abs($stats['savings_rate'])) }}%"></div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
        
        <!-- Graphiques -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
            <!-- Dépenses par catégorie -->
            <div class="card bg-base-100 shadow">
                <div class="card-body">
                    <h4 class="card-title mb-4">
                        <i class="fas fa-chart-pie mr-2"></i>Dépenses par catégorie
                    </h4>
                    <div class="h-64">
                        <canvas id="categoryChart"></canvas>
                    </div>
                </div>
            </div>
            
            <!-- Évolution quotidienne -->
            <div class="card bg-base-100 shadow">
                <div class="card-body">
                    <h4 class="card-title mb-4">
                        <i class="fas fa-chart-line mr-2"></i>Évolution quotidienne
                    </h4>
                    <div class="h-64">
                        <canvas id="dailyChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Transactions du mois -->
        <div class="card bg-base-100 shadow">
            <div class="card-body">
                <div class="flex justify-between items-center mb-6">
                    <h4 class="card-title">
                        <i class="fas fa-list mr-2"></i>Transactions du mois
                    </h4>
                    <span class="badge badge-primary">{{ $transactions->count() }} transactions</span>
                </div>
                
                @if($transactions->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="table table-zebra">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Description</th>
                                    <th>Catégorie</th>
                                    <th class="text-right">Montant</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($transactions as $transaction)
                                    <tr>
                                        <td>{{ $transaction->date->format('d/m') }}</td>
                                        <td class="max-w-xs truncate">{{ $transaction->description ?? 'Sans description' }}</td>
                                        <td>
                                            <div class="flex items-center">
                                                <div class="w-3 h-3 rounded-full mr-2" style="background-color: {{ $transaction->category->color }}"></div>
                                                {{ $transaction->category->name }}
                                            </div>
                                        </td>
                                        <td class="text-right font-bold {{ $transaction->type === 'expense' ? 'text-error' : 'text-success' }}">
                                            {{ number_format($transaction->amount, 0, ',', ' ') }} FDJ
                                        </td>
                                        <td>
                                            <a href="{{ route('transactions.show', $transaction) }}" class="btn btn-xs btn-outline">
                                                Voir
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    
                    @if($transactions->hasPages())
                        <div class="mt-6">
                            {{ $transactions->links() }}
                        </div>
                    @endif
                @else
                    <div class="text-center py-8">
                        <div class="inline-block p-4 rounded-full bg-base-300 mb-4">
                            <i class="fas fa-receipt text-3xl text-base-content/50"></i>
                        </div>
                        <h4 class="text-lg font-semibold text-base-content/70 mb-2">
                            Aucune transaction ce mois-ci
                        </h4>
                        <p class="text-base-content/50 mb-4">
                            Commencez à ajouter des transactions pour voir les rapports.
                        </p>
                        <a href="{{ route('transactions.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus mr-2"></i>Ajouter une transaction
                        </a>
                    </div>
                @endif
            </div>
        </div>
        
        <!-- Navigation entre mois -->
        <div class="flex justify-between mt-6">
            @php
                $prevMonth = \Carbon\Carbon::create($year, $month, 1)->subMonth();
                $nextMonth = \Carbon\Carbon::create($year, $month, 1)->addMonth();
                $hasNextMonth = $nextMonth <= now();
            @endphp
            
            <a href="{{ route('reports.monthly', ['year' => $prevMonth->year, 'month' => $prevMonth->month]) }}" 
               class="btn btn-outline">
                <i class="fas fa-chevron-left mr-2"></i>
                {{ $prevMonth->translatedFormat('F Y') }}
            </a>
            
            <div class="flex space-x-2">
                <a href="{{ route('reports.monthly', ['year' => now()->year, 'month' => now()->month]) }}" 
                   class="btn btn-outline">
                    <i class="fas fa-calendar-day mr-2"></i>Mois courant
                </a>
            </div>
            
            @if($hasNextMonth)
                <a href="{{ route('reports.monthly', ['year' => $nextMonth->year, 'month' => $nextMonth->month]) }}" 
                   class="btn btn-outline">
                    {{ $nextMonth->translatedFormat('F Y') }}
                    <i class="fas fa-chevron-right ml-2"></i>
                </a>
            @else
                <button class="btn btn-outline" disabled>
                    <i class="fas fa-chevron-right ml-2"></i>Mois suivant
                </button>
            @endif
        </div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Données pour le graphique des catégories
            const categoryData = {
                labels: {!! json_encode($stats['category_labels'] ?? []) !!},
                datasets: [{
                    data: {!! json_encode($stats['category_values'] ?? []) !!},
                    backgroundColor: {!! json_encode($stats['category_colors'] ?? []) !!},
                    borderWidth: 1
                }]
            };
            
            // Configuration du graphique circulaire
            const categoryConfig = {
                type: 'doughnut',
                data: categoryData,
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'right'
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    let label = context.label || '';
                                    if (label) {
                                        label += ': ';
                                    }
                                    label += context.formattedValue + ' FDJ';
                                    return label;
                                }
                            }
                        }
                    }
                }
            };
            
            // Créer le graphique
            const categoryChart = new Chart(
                document.getElementById('categoryChart'),
                categoryConfig
            );
            
            // Données pour le graphique quotidien
            const dailyData = {
                labels: {!! json_encode($stats['daily_labels'] ?? []) !!},
                datasets: [{
                    label: 'Dépenses',
                    data: {!! json_encode($stats['daily_expenses'] ?? []) !!},
                    borderColor: 'rgb(239, 68, 68)',
                    backgroundColor: 'rgba(239, 68, 68, 0.1)',
                    tension: 0.4
                }, {
                    label: 'Revenus',
                    data: {!! json_encode($stats['daily_incomes'] ?? []) !!},
                    borderColor: 'rgb(34, 197, 94)',
                    backgroundColor: 'rgba(34, 197, 94, 0.1)',
                    tension: 0.4
                }]
            };
            
            // Configuration du graphique linéaire
            const dailyConfig = {
                type: 'line',
                data: dailyData,
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: {
                        mode: 'index',
                        intersect: false,
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return value.toLocaleString() + ' FDJ';
                                }
                            }
                        }
                    },
                    plugins: {
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return context.dataset.label + ': ' + context.parsed.y.toLocaleString() + ' FDJ';
                                }
                            }
                        }
                    }
                }
            };
            
            // Créer le graphique quotidien
            const dailyChart = new Chart(
                document.getElementById('dailyChart'),
                dailyConfig
            );
        });
    </script>
    @endpush
</x-app-layout>