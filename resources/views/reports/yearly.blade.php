<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between">
            <h2 class="text-2xl font-bold text-base-content">
                <i class="fas fa-chart-line mr-3"></i>Rapport Annuel
            </h2>
            <div class="mt-2 md:mt-0">
                <div class="text-sm breadcrumbs">
                    <ul>
                        <li><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li><a href="{{ route('reports.index') }}">Rapports</a></li>
                        <li>Annuel</li>
                    </ul>
                </div>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <!-- Filtres -->
        <div class="card bg-base-100 shadow-lg mb-6">
            <div class="card-body">
                <form method="GET" class="flex flex-col md:flex-row md:items-end space-y-4 md:space-y-0 md:space-x-4">
                    <div class="form-control flex-1">
                        <label class="label">
                            <span class="label-text">Année</span>
                        </label>
                        <select name="year" class="select select-bordered" onchange="this.form.submit()">
                            @for($y = date('Y'); $y >= 2020; $y--)
                                <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>
                                    {{ $y }}
                                </option>
                            @endfor
                        </select>
                    </div>
                    
                    <div class="flex space-x-2">
                        <a href="{{ route('reports.yearly', ['year' => $year - 1]) }}" class="btn btn-outline">
                            <i class="fas fa-chevron-left"></i>
                        </a>
                        <a href="{{ route('reports.yearly', ['year' => now()->year]) }}" class="btn btn-outline">
                            Année courante
                        </a>
                        @if($year < now()->year)
                            <a href="{{ route('reports.yearly', ['year' => $year + 1]) }}" class="btn btn-outline">
                                <i class="fas fa-chevron-right"></i>
                            </a>
                        @else
                            <button class="btn btn-outline" disabled>
                                <i class="fas fa-chevron-right"></i>
                            </button>
                        @endif
                    </div>
                </form>
            </div>
        </div>
        
        <!-- En-tête de l'année -->
        <div class="card bg-base-100 shadow-lg mb-8">
            <div class="card-body">
                <div class="flex flex-col md:flex-row md:items-center justify-between">
                    <div>
                        <h3 class="text-xl font-bold">
                            Rapport Annuel {{ $year }}
                        </h3>
                        <p class="text-base-content/70">
                            {{ $yearlyStats['transaction_count'] }} transactions • 
                            {{ number_format($yearlyStats['balance'], 0, ',', ' ') }} FDJ de balance
                        </p>
                    </div>
                    
                    <div class="mt-4 md:mt-0 flex items-center space-x-4">
                        <div class="text-center">
                            <div class="text-sm text-base-content/70">Balance</div>
                            <div class="text-2xl font-bold {{ $yearlyStats['balance'] >= 0 ? 'text-success' : 'text-error' }}">
                                {{ number_format($yearlyStats['balance'], 0, ',', ' ') }} FDJ
                            </div>
                        </div>
                        
                        <div class="dropdown">
                            <button class="btn btn-outline">
                                <i class="fas fa-ellipsis-v"></i> Actions
                            </button>
                            <ul class="dropdown-content z-[1] menu p-2 shadow bg-base-100 rounded-box w-52">
                                <li>
                                    <a href="{{ route('reports.export', ['format' => 'pdf', 'period' => 'year', 'year' => $year]) }}">
                                        <i class="fas fa-file-pdf"></i> Exporter en PDF
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('reports.export', ['format' => 'excel', 'period' => 'year', 'year' => $year]) }}">
                                        <i class="fas fa-file-excel"></i> Exporter en Excel
                                    </a>
                                </li>
                                <li><div class="divider my-0"></div></li>
                                <li>
                                    <a href="{{ route('reports.monthly', ['year' => $year, 'month' => date('m')]) }}">
                                        <i class="fas fa-calendar-alt"></i> Voir ce mois
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
                            <div class="stat-title text-error">Dépenses annuelles</div>
                            <div class="stat-value text-error text-3xl">
                                {{ number_format($yearlyStats['total_expenses'], 0, ',', ' ') }} FDJ
                            </div>
                            <div class="stat-desc">
                                Moyenne mensuelle: {{ number_format($yearlyStats['total_expenses'] / 12, 0, ',', ' ') }} FDJ
                            </div>
                        </div>
                        <div class="stat-figure text-error">
                            <i class="fas fa-arrow-down text-3xl"></i>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Revenus -->
            <div class="card bg-base-100 shadow">
                <div class="card-body">
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="stat-title text-success">Revenus annuels</div>
                            <div class="stat-value text-success text-3xl">
                                {{ number_format($yearlyStats['total_incomes'], 0, ',', ' ') }} FDJ
                            </div>
                            <div class="stat-desc">
                                Moyenne mensuelle: {{ number_format($yearlyStats['total_incomes'] / 12, 0, ',', ' ') }} FDJ
                            </div>
                        </div>
                        <div class="stat-figure text-success">
                            <i class="fas fa-arrow-up text-3xl"></i>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Économies -->
            <div class="card bg-base-100 shadow">
                <div class="card-body">
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="stat-title">Taux d'épargne</div>
                            <div class="stat-value text-3xl {{ ($yearlyStats['total_incomes'] > 0 ? ($yearlyStats['balance'] / $yearlyStats['total_incomes'] * 100) : 0) >= 0 ? 'text-success' : 'text-error' }}">
                                {{ number_format($yearlyStats['total_incomes'] > 0 ? ($yearlyStats['balance'] / $yearlyStats['total_incomes'] * 100) : 0, 1, ',', ' ') }}%
                            </div>
                            <div class="stat-desc">
                                {{ number_format($yearlyStats['balance'], 0, ',', ' ') }} FDJ épargnés
                            </div>
                        </div>
                        <div class="stat-figure {{ $yearlyStats['balance'] >= 0 ? 'text-success' : 'text-error' }}">
                            <i class="fas fa-piggy-bank text-3xl"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Graphique annuel -->
        <div class="card bg-base-100 shadow mb-8">
            <div class="card-body">
                <h4 class="card-title mb-4">
                    <i class="fas fa-chart-bar mr-2"></i>Évolution mensuelle
                </h4>
                <div class="h-80">
                    <canvas id="yearlyChart"></canvas>
                </div>
            </div>
        </div>
        
        <!-- Tableau des mois -->
        <div class="card bg-base-100 shadow">
            <div class="card-body">
                <div class="flex justify-between items-center mb-6">
                    <h4 class="card-title">
                        <i class="fas fa-calendar mr-2"></i>Détails par mois
                    </h4>
                    <span class="badge badge-primary">12 mois</span>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="table table-zebra">
                        <thead>
                            <tr>
                                <th>Mois</th>
                                <th class="text-right">Dépenses</th>
                                <th class="text-right">Revenus</th>
                                <th class="text-right">Balance</th>
                                <th>Transactions</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($monthlyData as $data)
                                <tr>
                                    <td class="font-medium">{{ $data['month'] }}</td>
                                    <td class="text-right text-error">
                                        {{ number_format($data['expenses'], 0, ',', ' ') }} FDJ
                                    </td>
                                    <td class="text-right text-success">
                                        {{ number_format($data['incomes'], 0, ',', ' ') }} FDJ
                                    </td>
                                    <td class="text-right font-bold {{ $data['balance'] >= 0 ? 'text-success' : 'text-error' }}">
                                        {{ number_format($data['balance'], 0, ',', ' ') }} FDJ
                                    </td>
                                    <td class="text-center">
                                        <span class="badge badge-outline">{{ $data['transaction_count'] }}</span>
                                    </td>
                                    <td>
                                        <a href="{{ route('reports.monthly', ['year' => $year, 'month' => $data['month_number']]) }}" 
                                           class="btn btn-xs btn-outline">
                                            Détails
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="font-bold">
                                <td>Total {{ $year }}</td>
                                <td class="text-right text-error">
                                    {{ number_format($yearlyStats['total_expenses'], 0, ',', ' ') }} FDJ
                                </td>
                                <td class="text-right text-success">
                                    {{ number_format($yearlyStats['total_incomes'], 0, ',', ' ') }} FDJ
                                </td>
                                <td class="text-right {{ $yearlyStats['balance'] >= 0 ? 'text-success' : 'text-error' }}">
                                    {{ number_format($yearlyStats['balance'], 0, ',', ' ') }} FDJ
                                </td>
                                <td class="text-center">
                                    <span class="badge badge-primary">{{ $yearlyStats['transaction_count'] }}</span>
                                </td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
        
        <!-- Navigation -->
        <div class="flex justify-between mt-6">
            <a href="{{ route('reports.yearly', ['year' => $year - 1]) }}" class="btn btn-outline">
                <i class="fas fa-chevron-left mr-2"></i>
                {{ $year - 1 }}
            </a>
            
            <div class="flex space-x-2">
                <a href="{{ route('reports.index') }}" class="btn btn-outline">
                    <i class="fas fa-chart-bar mr-2"></i>Retour aux rapports
                </a>
                <a href="{{ route('reports.monthly') }}" class="btn btn-primary">
                    <i class="fas fa-calendar-alt mr-2"></i>Voir mensuel
                </a>
            </div>
            
            @if($year < now()->year)
                <a href="{{ route('reports.yearly', ['year' => $year + 1]) }}" class="btn btn-outline">
                    {{ $year + 1 }}
                    <i class="fas fa-chevron-right ml-2"></i>
                </a>
            @else
                <button class="btn btn-outline" disabled>
                    <i class="fas fa-chevron-right ml-2"></i>Année suivante
                </button>
            @endif
        </div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const monthlyData = @json($monthlyData);
            
            // Données pour le graphique
            const labels = monthlyData.map(item => item.month);
            const expenses = monthlyData.map(item => item.expenses);
            const incomes = monthlyData.map(item => item.incomes);
            const balances = monthlyData.map(item => item.balance);
            
            // Configuration du graphique
            const ctx = document.getElementById('yearlyChart').getContext('2d');
            const yearlyChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [
                        {
                            label: 'Dépenses',
                            data: expenses,
                            backgroundColor: 'rgba(239, 68, 68, 0.7)',
                            borderColor: 'rgb(239, 68, 68)',
                            borderWidth: 1
                        },
                        {
                            label: 'Revenus',
                            data: incomes,
                            backgroundColor: 'rgba(34, 197, 94, 0.7)',
                            borderColor: 'rgb(34, 197, 94)',
                            borderWidth: 1
                        },
                        {
                            label: 'Balance',
                            data: balances,
                            type: 'line',
                            backgroundColor: 'rgba(59, 130, 246, 0.2)',
                            borderColor: 'rgb(59, 130, 246)',
                            borderWidth: 2,
                            fill: true,
                            tension: 0.4
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
                                    let label = context.dataset.label || '';
                                    if (label) {
                                        label += ': ';
                                    }
                                    label += context.parsed.y.toLocaleString() + ' FDJ';
                                    return label;
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