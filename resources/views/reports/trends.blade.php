<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between">
            <h2 class="text-2xl font-bold text-base-content">
                <i class="fas fa-trend-up mr-3"></i>Tendances Financières
            </h2>
            <div class="mt-2 md:mt-0">
                <div class="text-sm breadcrumbs">
                    <ul>
                        <li><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li><a href="{{ route('reports.index') }}">Rapports</a></li>
                        <li>Tendances</li>
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
                        <i class="fas fa-chart-line text-2xl text-primary"></i>
                    </div>
                    <div>
                        <h3 class="card-title mb-2">Analyse des tendances</h3>
                        <p class="text-base-content/70">
                            Visualisez l'évolution de vos finances sur les 12 derniers mois.
                            Identifiez des tendances, saisonnalités et prévisions pour mieux planifier votre budget.
                        </p>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Statistiques de tendance -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
            <!-- Moyennes -->
            <div class="card bg-base-100 shadow">
                <div class="card-body">
                    <h4 class="card-title mb-4">
                        <i class="fas fa-calculator mr-2"></i>Moyennes mensuelles
                    </h4>
                    <div class="space-y-4">
                        <div>
                            <div class="flex justify-between mb-1">
                                <span class="text-base-content/70">Dépenses moyennes</span>
                                <span class="font-bold text-error">
                                    {{ number_format($monthlyAverages['expenses'], 0, ',', ' ') }} FDJ
                                </span>
                            </div>
                            <div class="text-sm text-base-content/50">
                                Calculée sur les 6 derniers mois
                            </div>
                        </div>
                        <div>
                            <div class="flex justify-between mb-1">
                                <span class="text-base-content/70">Revenus moyens</span>
                                <span class="font-bold text-success">
                                    {{ number_format($monthlyAverages['incomes'], 0, ',', ' ') }} FDJ
                                </span>
                            </div>
                            <div class="text-sm text-base-content/50">
                                Calculée sur les 6 derniers mois
                            </div>
                        </div>
                        <div class="pt-3 border-t">
                            <div class="flex justify-between">
                                <span class="text-base-content/70">Balance moyenne</span>
                                <span class="font-bold {{ ($monthlyAverages['incomes'] - $monthlyAverages['expenses']) >= 0 ? 'text-success' : 'text-error' }}">
                                    {{ number_format($monthlyAverages['incomes'] - $monthlyAverages['expenses'], 0, ',', ' ') }} FDJ
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Prévisions -->
            <div class="card bg-base-100 shadow">
                <div class="card-body">
                    <h4 class="card-title mb-4">
                        <i class="fas fa-crystal-ball mr-2"></i>Projections
                    </h4>
                    <div class="space-y-4">
                        <div>
                            <div class="flex justify-between mb-1">
                                <span class="text-base-content/70">Dépenses annuelles estimées</span>
                                <span class="font-bold text-error">
                                    {{ number_format($monthlyAverages['expenses'] * 12, 0, ',', ' ') }} FDJ
                                </span>
                            </div>
                        </div>
                        <div>
                            <div class="flex justify-between mb-1">
                                <span class="text-base-content/70">Revenus annuels estimés</span>
                                <span class="font-bold text-success">
                                    {{ number_format($monthlyAverages['incomes'] * 12, 0, ',', ' ') }} FDJ
                                </span>
                            </div>
                        </div>
                        <div class="pt-3 border-t">
                            <div class="flex justify-between">
                                <span class="text-base-content/70">Épargne annuelle estimée</span>
                                <span class="font-bold {{ (($monthlyAverages['incomes'] - $monthlyAverages['expenses']) * 12) >= 0 ? 'text-success' : 'text-error' }}">
                                    {{ number_format(($monthlyAverages['incomes'] - $monthlyAverages['expenses']) * 12, 0, ',', ' ') }} FDJ
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Graphique des tendances -->
        <div class="card bg-base-100 shadow mb-8">
            <div class="card-body">
                <h4 class="card-title mb-4">
                    <i class="fas fa-chart-line mr-2"></i>Tendances sur 12 mois
                </h4>
                <div class="h-80">
                    <canvas id="trendsChart"></canvas>
                </div>
            </div>
        </div>
        
        <!-- Tableaux de tendances -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
            <!-- Tendances des dépenses -->
            <div class="card bg-base-100 shadow">
                <div class="card-body">
                    <h4 class="card-title mb-4">
                        <i class="fas fa-arrow-down text-error mr-2"></i>Évolution des dépenses
                    </h4>
                    
                    @if(count($expenseTrends) > 0)
                        <div class="overflow-x-auto">
                            <table class="table table-zebra table-sm">
                                <thead>
                                    <tr>
                                        <th>Mois</th>
                                        <th class="text-right">Montant</th>
                                        <th class="text-right">Variation</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @for($i = 0; $i < min(6, count($expenseTrends)); $i++)
                                        @php
                                            $trend = $expenseTrends[$i];
                                            $prevTrend = $expenseTrends[$i + 1] ?? null;
                                            $variation = $prevTrend ? (($trend['amount'] - $prevTrend['amount']) / max(1, $prevTrend['amount'])) * 100 : 0;
                                        @endphp
                                        <tr>
                                            <td>{{ $trend['month'] }}</td>
                                            <td class="text-right text-error">
                                                {{ number_format($trend['amount'], 0, ',', ' ') }} FDJ
                                            </td>
                                            <td class="text-right {{ $variation >= 0 ? 'text-error' : 'text-success' }}">
                                                {{ $variation >= 0 ? '+' : '' }}{{ number_format($variation, 1) }}%
                                            </td>
                                        </tr>
                                    @endfor
                                </tbody>
                            </table>
                        </div>
                        
                        <div class="mt-4 text-center">
                            <a href="{{ route('reports.monthly') }}" class="btn btn-xs btn-outline">
                                Voir tous les mois
                            </a>
                        </div>
                    @else
                        <p class="text-center text-base-content/50 py-4">
                            Pas assez de données pour analyser les tendances
                        </p>
                    @endif
                </div>
            </div>
            
            <!-- Tendances des revenus -->
            <div class="card bg-base-100 shadow">
                <div class="card-body">
                    <h4 class="card-title mb-4">
                        <i class="fas fa-arrow-up text-success mr-2"></i>Évolution des revenus
                    </h4>
                    
                    @if(count($incomeTrends) > 0)
                        <div class="overflow-x-auto">
                            <table class="table table-zebra table-sm">
                                <thead>
                                    <tr>
                                        <th>Mois</th>
                                        <th class="text-right">Montant</th>
                                        <th class="text-right">Variation</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @for($i = 0; $i < min(6, count($incomeTrends)); $i++)
                                        @php
                                            $trend = $incomeTrends[$i];
                                            $prevTrend = $incomeTrends[$i + 1] ?? null;
                                            $variation = $prevTrend ? (($trend['amount'] - $prevTrend['amount']) / max(1, $prevTrend['amount'])) * 100 : 0;
                                        @endphp
                                        <tr>
                                            <td>{{ $trend['month'] }}</td>
                                            <td class="text-right text-success">
                                                {{ number_format($trend['amount'], 0, ',', ' ') }} FDJ
                                            </td>
                                            <td class="text-right {{ $variation >= 0 ? 'text-success' : 'text-error' }}">
                                                {{ $variation >= 0 ? '+' : '' }}{{ number_format($variation, 1) }}%
                                            </td>
                                        </tr>
                                    @endfor
                                </tbody>
                            </table>
                        </div>
                        
                        <div class="mt-4 text-center">
                            <a href="{{ route('reports.monthly') }}" class="btn btn-xs btn-outline">
                                Voir tous les mois
                            </a>
                        </div>
                    @else
                        <p class="text-center text-base-content/50 py-4">
                            Pas assez de données pour analyser les tendances
                        </p>
                    @endif
                </div>
            </div>
        </div>
        
        <!-- Conseils et recommandations -->
        <div class="card bg-base-100 shadow">
            <div class="card-body">
                <h4 class="card-title mb-4">
                    <i class="fas fa-lightbulb mr-2"></i>Recommandations basées sur vos tendances
                </h4>
                
                @php
                    $avgExpense = $monthlyAverages['expenses'];
                    $avgIncome = $monthlyAverages['incomes'];
                    $balance = $avgIncome - $avgExpense;
                    $savingsRate = $avgIncome > 0 ? ($balance / $avgIncome) * 100 : 0;
                @endphp
                
                <div class="space-y-4">
                    <!-- Conseil 1: Épargne -->
                    <div class="alert {{ $savingsRate >= 10 ? 'alert-success' : ($savingsRate >= 0 ? 'alert-warning' : 'alert-error') }}">
                        <div>
                            <h5 class="font-bold">
                                @if($savingsRate >= 10)
                                    <i class="fas fa-trophy mr-2"></i>Excellent taux d'épargne!
                                @elseif($savingsRate >= 0)
                                    <i class="fas fa-info-circle mr-2"></i>Taux d'épargne modéré
                                @else
                                    <i class="fas fa-exclamation-triangle mr-2"></i>Attention au déficit
                                @endif
                            </h5>
                            <p>
                                Votre taux d'épargne est de {{ number_format($savingsRate, 1) }}%.
                                @if($savingsRate >= 10)
                                    Vous épargnez plus que la recommandation standard de 10%. Continuez !
                                @elseif($savingsRate >= 0)
                                    Essayez d'atteindre au moins 10% d'épargne chaque mois.
                                @else
                                    Vous dépensez plus que vous ne gagnez. Revoyez vos dépenses.
                                @endif
                            </p>
                        </div>
                    </div>
                    
                    <!-- Conseil 2: Dépenses -->
                    <div class="alert {{ $avgExpense <= ($avgIncome * 0.7) ? 'alert-success' : 'alert-warning' }}">
                        <div>
                            <h5 class="font-bold">
                                @if($avgExpense <= ($avgIncome * 0.7))
                                    <i class="fas fa-check-circle mr-2"></i>Dépenses bien contrôlées
                                @else
                                    <i class="fas fa-exclamation-circle mr-2"></i>Dépenses élevées
                                @endif
                            </h5>
                            <p>
                                Vos dépenses représentent {{ number_format(($avgExpense / max(1, $avgIncome)) * 100, 0) }}% de vos revenus.
                                @if($avgExpense <= ($avgIncome * 0.7))
                                    Vous respectez la règle des 70% pour les dépenses courantes.
                                @else
                                    Essayez de réduire vos dépenses à maximum 70% de vos revenus.
                                @endif
                            </p>
                        </div>
                    </div>
                    
                    <!-- Conseil 3: Stabilité -->
                    @if(count($expenseTrends) >= 3)
                        @php
                            $last3Expenses = array_slice($expenseTrends, 0, 3);
                            $avgLast3 = array_sum(array_column($last3Expenses, 'amount')) / 3;
                            $stability = (abs($avgLast3 - $avgExpense) / max(1, $avgExpense)) * 100;
                        @endphp
                        <div class="alert {{ $stability <= 15 ? 'alert-success' : 'alert-warning' }}">
                            <div>
                                <h5 class="font-bold">
                                    @if($stability <= 15)
                                        <i class="fas fa-chart-line mr-2"></i>Dépenses stables
                                    @else
                                        <i class="fas fa-chart-area mr-2"></i>Dépenses variables
                                    @endif
                                </h5>
                                <p>
                                    Vos dépenses des 3 derniers mois varient de {{ number_format($stability, 1) }}% par rapport à la moyenne.
                                    @if($stability <= 15)
                                        Excellente stabilité dans vos dépenses.
                                    @else
                                        Essayez de stabiliser vos dépenses mensuelles.
                                    @endif
                                </p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
        
        <!-- Navigation -->
        <div class="flex justify-between mt-6">
            <a href="{{ route('reports.index') }}" class="btn btn-outline">
                <i class="fas fa-chevron-left mr-2"></i>Retour aux rapports
            </a>
            
            <div class="flex space-x-2">
                <a href="{{ route('reports.monthly') }}" class="btn btn-outline">
                    <i class="fas fa-calendar-alt mr-2"></i>Mensuel
                </a>
                <a href="{{ route('reports.yearly') }}" class="btn btn-outline">
                    <i class="fas fa-chart-line mr-2"></i>Annuel
                </a>
                <a href="{{ route('reports.category') }}" class="btn btn-outline">
                    <i class="fas fa-tags mr-2"></i>Par catégorie
                </a>
            </div>
            
            <button onclick="window.print()" class="btn btn-primary">
                <i class="fas fa-print mr-2"></i>Imprimer l'analyse
            </button>
        </div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const expenseTrends = @json($expenseTrends);
            const incomeTrends = @json($incomeTrends);
            
            // Données pour le graphique
            const labels = expenseTrends.map(item => item.month);
            const expenses = expenseTrends.map(item => item.amount);
            const incomes = incomeTrends.map(item => item.amount);
            
            // Configuration du graphique
            const ctx = document.getElementById('trendsChart').getContext('2d');
            const trendsChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [
                        {
                            label: 'Dépenses',
                            data: expenses,
                            borderColor: 'rgb(239, 68, 68)',
                            backgroundColor: 'rgba(239, 68, 68, 0.1)',
                            tension: 0.4,
                            fill: true
                        },
                        {
                            label: 'Revenus',
                            data: incomes,
                            borderColor: 'rgb(34, 197, 94)',
                            backgroundColor: 'rgba(34, 197, 94, 0.1)',
                            tension: 0.4,
                            fill: true
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
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
            });
        });
    </script>
    @endpush
</x-app-layout>