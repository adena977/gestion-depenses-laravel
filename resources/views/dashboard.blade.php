<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between">
            <h2 class="text-2xl font-bold text-base-content">
                <i class="fas fa-chart-bar mr-3"></i>Tableau de Bord
            </h2>
            <div class="mt-2 md:mt-0 text-sm breadcrumbs">
                <ul>
                    <li><i class="fas fa-home"></i> Accueil</li> 
                    <li>Dashboard</li>
                </ul>
            </div>
        </div>
    </x-slot>

    @php
        // Fonction pour formater les montants sans les zéros après la virgule
        function formatAmount($amount) {
            // Formater le montant avec 2 décimales, virgule comme séparateur décimal et espace comme séparateur de milliers
            $formatted = number_format($amount, 2, ',', ' ');
            
            // Supprimer les zéros inutiles après la virgule
            if (strpos($formatted, ',00') !== false) {
                $formatted = str_replace(',00', '', $formatted);
            } elseif (strpos($formatted, ',0') !== false) {
                $formatted = str_replace(',0', '', $formatted);
            }
            
            return $formatted;
        }

        // Récupérer les données réelles depuis la base de données
        $user = auth()->user();
        $currentMonth = now()->month;
        $currentYear = now()->year;
        
        // Dépenses du mois courant
        $monthlyExpenses = App\Models\Transaction::where('user_id', $user->id)
            ->where('type', 'expense')
            ->whereMonth('date', $currentMonth)
            ->whereYear('date', $currentYear)
            ->sum('amount');
        
        // Revenus du mois courant
        $monthlyIncome = App\Models\Transaction::where('user_id', $user->id)
            ->where('type', 'income')
            ->whereMonth('date', $currentMonth)
            ->whereYear('date', $currentYear)
            ->sum('amount');
        
        // Total des transactions ce mois
        $transactionsCount = App\Models\Transaction::where('user_id', $user->id)
            ->whereMonth('date', $currentMonth)
            ->whereYear('date', $currentYear)
            ->count();
        
        // Économies totales
        $totalSavings = App\Models\SavingsGoal::where('user_id', $user->id)
            ->sum('current_amount');
        
        // Dépenses par catégorie
        $expensesByCategory = App\Models\Transaction::select(
                'categories.name',
                'categories.color',
                DB::raw('SUM(transactions.amount) as total')
            )
            ->join('categories', 'transactions.category_id', '=', 'categories.id')
            ->where('transactions.user_id', $user->id)
            ->where('transactions.type', 'expense')
            ->whereMonth('transactions.date', $currentMonth)
            ->whereYear('transactions.date', $currentYear)
            ->groupBy('categories.name', 'categories.color')
            ->orderBy('total', 'desc')
            ->limit(5)
            ->get();
        
        // Total des dépenses par catégorie
        $totalExpensesByCategory = $expensesByCategory->sum('total');
        
        // Dernières transactions
        $recentTransactions = App\Models\Transaction::with('category')
            ->where('user_id', $user->id)
            ->orderBy('date', 'desc')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
        
        // Alertes de budget
        $budgetAlerts = App\Models\Budget::with('category')
            ->where('user_id', $user->id)
            ->where('notifications_enabled', true)
            ->get()
            ->map(function ($budget) use ($user) {
                $spent = App\Models\Transaction::where('user_id', $user->id)
                    ->where('category_id', $budget->category_id)
                    ->where('type', 'expense')
                    ->whereBetween('date', [$budget->start_date, $budget->end_date])
                    ->sum('amount');
                
                $percentage = ($budget->amount > 0) ? ($spent / $budget->amount) * 100 : 0;
                
                if ($percentage >= $budget->threshold_percentage) {
                    $budget->percentage = round($percentage, 2);
                    $budget->spent = $spent;
                    return $budget;
                }
                return null;
            })
            ->filter();
    @endphp

    <div class="py-6 space-y-6">
        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <!-- Dépenses du mois -->
            <div class="card bg-base-100 shadow-lg hover:shadow-xl transition-shadow duration-300 card-hover">
                <div class="card-body p-4">
                    <div class="flex items-center justify-between">
                        <div class="flex-1 min-w-0">
                            <div class="stat-title text-base-content/70 truncate">Dépenses ce mois</div>
                            <div class="stat-value text-error text-2xl md:text-3xl truncate">
                                {{ formatAmount($monthlyExpenses) }}
                                <span class="text-lg font-normal">FDJ</span>
                            </div>
                            <div class="stat-desc @if($monthlyExpenses > 0) text-error @else text-base-content/50 @endif truncate">
                                @if($monthlyExpenses > 0)
                                    {{ now()->locale('fr')->translatedFormat('F Y') }}
                                @else
                                    Aucune dépense
                                @endif
                            </div>
                        </div>
                        <div class="stat-figure text-error ml-3">
                            <i class="fas fa-arrow-trend-up text-3xl"></i>
                        </div>
                    </div>
                    @if($monthlyExpenses > 0)
                    <div class="mt-4 pt-3 border-t border-base-300">
                        <div class="flex justify-between text-sm text-base-content/60">
                            <span>Moyenne/jour</span>
                            <span class="font-medium">{{ formatAmount($monthlyExpenses / now()->day) }} FDJ</span>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
            
            <!-- Revenus du mois -->
            <div class="card bg-base-100 shadow-lg hover:shadow-xl transition-shadow duration-300 card-hover">
                <div class="card-body p-4">
                    <div class="flex items-center justify-between">
                        <div class="flex-1 min-w-0">
                            <div class="stat-title text-base-content/70 truncate">Revenus ce mois</div>
                            <div class="stat-value text-success text-2xl md:text-3xl truncate">
                                {{ formatAmount($monthlyIncome) }}
                                <span class="text-lg font-normal">FDJ</span>
                            </div>
                            <div class="stat-desc @if($monthlyIncome > 0) text-success @else text-base-content/50 @endif truncate">
                                @if($monthlyIncome > 0)
                                    Solde net: {{ formatAmount($monthlyIncome - $monthlyExpenses) }} FDJ
                                @else
                                    Aucun revenu
                                @endif
                            </div>
                        </div>
                        <div class="stat-figure text-success ml-3">
                            <i class="fas fa-wallet text-3xl"></i>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Transactions -->
            <div class="card bg-base-100 shadow-lg hover:shadow-xl transition-shadow duration-300 card-hover">
                <div class="card-body p-4">
                    <div class="flex items-center justify-between">
                        <div class="flex-1 min-w-0">
                            <div class="stat-title text-base-content/70 truncate">Transactions</div>
                            <div class="stat-value text-primary text-2xl md:text-3xl truncate">{{ $transactionsCount }}</div>
                            <div class="stat-desc text-base-content/50 truncate">
                                Ce mois-ci
                                @if($transactionsCount > 0)
                                    <span class="text-primary">({{ now()->locale('fr')->translatedFormat('M') }})</span>
                                @endif
                            </div>
                        </div>
                        <div class="stat-figure text-primary ml-3">
                            <i class="fas fa-receipt text-3xl"></i>
                        </div>
                    </div>
                    @if($transactionsCount > 0)
                    <div class="mt-4 pt-3 border-t border-base-300">
                        <div class="text-sm text-base-content/60">
                            <span class="text-success">{{ App\Models\Transaction::where('user_id', $user->id)->where('type', 'income')->whereMonth('date', $currentMonth)->count() }} revenus</span>
                            <span class="mx-2">•</span>
                            <span class="text-error">{{ App\Models\Transaction::where('user_id', $user->id)->where('type', 'expense')->whereMonth('date', $currentMonth)->count() }} dépenses</span>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
            
            <!-- Économies -->
            <div class="card bg-base-100 shadow-lg hover:shadow-xl transition-shadow duration-300 card-hover">
                <div class="card-body p-4">
                    <div class="flex items-center justify-between">
                        <div class="flex-1 min-w-0">
                            <div class="stat-title text-base-content/70 truncate">Économies</div>
                            <div class="stat-value text-secondary text-2xl md:text-3xl truncate">
                                {{ formatAmount($totalSavings) }}
                                <span class="text-lg font-normal">FDJ</span>
                            </div>
                            <div class="stat-desc @if($totalSavings > 0) text-secondary @else text-base-content/50 @endif truncate">
                                @if($totalSavings > 0)
                                    Objectifs d'épargne
                                @else
                                    Aucun objectif
                                @endif
                            </div>
                        </div>
                        <div class="stat-figure text-secondary ml-3">
                            <i class="fas fa-piggy-bank text-3xl"></i>
                        </div>
                    </div>
                    @if($totalSavings > 0)
                    <div class="mt-4 pt-3 border-t border-base-300">
                        <div class="text-sm text-base-content/60">
                            {{ App\Models\SavingsGoal::where('user_id', $user->id)->count() }} objectif(s)
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Deuxième ligne : Graphiques et Alertes -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Dépenses par catégorie -->
            <div class="card bg-base-100 shadow-lg card-hover">
                <div class="card-body">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="card-title text-lg text-base-content">
                            <i class="fas fa-chart-pie mr-3"></i>
                            Dépenses par catégorie
                        </h3>
                        <span class="text-sm text-base-content/70">{{ now()->locale('fr')->translatedFormat('F Y') }}</span>
                    </div>
                    
                    @if($expensesByCategory->count() > 0)
                        <div class="h-64 flex flex-col">
                            <!-- Liste des catégories -->
                            <div class="space-y-3 flex-1 overflow-y-auto pr-2 scrollbar-custom">
                                @foreach($expensesByCategory as $category)
                                    @php
                                        $percentage = $totalExpensesByCategory > 0 ? ($category->total / $totalExpensesByCategory) * 100 : 0;
                                    @endphp
                                    <div class="space-y-1">
                                        <div class="flex justify-between items-center">
                                            <div class="flex items-center min-w-0">
                                                <div class="w-3 h-3 rounded-full mr-3 shrink-0" style="background-color: {{ $category->color }}"></div>
                                                <span class="font-medium text-base-content truncate">{{ $category->name }}</span>
                                            </div>
                                            <div class="text-right shrink-0 ml-2">
                                                <span class="font-semibold text-base-content">{{ formatAmount($category->total) }} <span class="text-sm">FDJ</span></span>
                                                <span class="text-sm text-base-content/70 ml-2">({{ round($percentage, 1) }}%)</span>
                                            </div>
                                        </div>
                                        <div class="w-full progress-bg rounded-full h-2">
                                            <div class="h-2 rounded-full" style="width: {{ $percentage }}%; background-color: {{ $category->color }}"></div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            
                            <!-- Total -->
                            <div class="pt-4 mt-4 border-t border-base-300">
                                <div class="flex justify-between items-center">
                                    <span class="font-semibold text-base-content">Total des dépenses</span>
                                    <span class="text-xl font-bold text-error">{{ formatAmount($totalExpensesByCategory) }} FDJ</span>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="h-64 flex flex-col items-center justify-center text-center">
                            <i class="fas fa-chart-pie text-5xl icon-empty mb-4"></i>
                            <h4 class="text-lg text-empty-title">Aucune donnée</h4>
                            <p class="text-empty-description mt-2">Aucune dépense enregistrée ce mois-ci</p>
                            <a href="{{ route('transactions.create') }}" class="btn btn-primary btn-sm mt-4 focus-ring">
                                <i class="fas fa-plus mr-2"></i>
                                Ajouter une dépense
                            </a>
                        </div>
                    @endif
                </div>
            </div>
            
            <!-- Alertes Budget -->
            <div class="card bg-base-100 shadow-lg card-hover">
                <div class="card-body">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="card-title text-lg text-base-content">
                            <i class="fas fa-exclamation-triangle mr-3"></i>
                            Alertes Budget
                        </h3>
                        @if($budgetAlerts->count() > 0)
                            <span class="badge badge-error badge-sm">{{ $budgetAlerts->count() }}</span>
                        @endif
                    </div>
                    
                    @if($budgetAlerts->count() > 0)
                        <div class="space-y-4 max-h-64 overflow-y-auto pr-2 scrollbar-custom">
                            @foreach($budgetAlerts as $alert)
                                <div class="p-4 bg-warning/10 dark:bg-warning/5 rounded-lg border border-warning/20 dark:border-warning/30">
                                    <div class="flex justify-between items-start mb-2">
                                        <div class="flex items-center min-w-0">
                                            <i class="fas fa-exclamation-circle text-warning mr-3 shrink-0"></i>
                                            <div class="min-w-0">
                                                <h4 class="font-semibold text-base-content truncate">{{ $alert->category->name }}</h4>
                                                <p class="text-sm text-base-content/70 truncate">
                                                    Budget: {{ formatAmount($alert->amount) }} FDJ
                                                    • Dépensé: {{ formatAmount($alert->spent) }} FDJ
                                                </p>
                                            </div>
                                        </div>
                                        <span class="font-bold text-warning shrink-0 ml-2">{{ round($alert->percentage, 0) }}%</span>
                                    </div>
                                    <div class="w-full progress-bg rounded-full h-2">
                                        <div class="h-2 rounded-full bg-warning" style="width: {{ min($alert->percentage, 100) }}%"></div>
                                    </div>
                                    <div class="flex justify-between text-sm mt-1 text-base-content/70">
                                        <span>0%</span>
                                        <span class="text-warning font-semibold">Seuil: {{ $alert->threshold_percentage }}%</span>
                                        <span>100%</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="h-64 flex flex-col items-center justify-center text-center">
                            <i class="fas fa-check-circle text-5xl text-success mb-4"></i>
                            <h4 class="text-lg text-empty-title">Tout est sous contrôle</h4>
                            <p class="text-empty-description mt-2">Aucune alerte de budget pour le moment</p>
                            <a href="{{ route('budgets.create') }}" class="btn btn-accent btn-sm mt-4 focus-ring">
                                <i class="fas fa-plus mr-2"></i>
                                Créer un budget
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Dernières Transactions -->
        <div class="card bg-base-100 shadow-lg card-hover">
            <div class="card-body">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="card-title text-lg text-base-content">
                        <i class="fas fa-history mr-3"></i>
                        Dernières transactions
                    </h3>
                    <a href="{{ route('transactions.index') }}" class="btn btn-ghost btn-sm focus-ring">
                        Voir tout <i class="fas fa-arrow-right ml-2"></i>
                    </a>
                </div>
                
                @if($recentTransactions->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="table table-zebra table-sm md:table-md">
                            <thead>
                                <tr>
                                    <th class="text-base-content">Date</th>
                                    <th class="text-base-content">Description</th>
                                    <th class="hidden md:table-cell text-base-content">Catégorie</th>
                                    <th class="text-base-content">Montant</th>
                                    <th class="text-center text-base-content">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentTransactions as $transaction)
                                    <tr class="hover transition-default">
                                        <td class="whitespace-nowrap">
                                            <div class="text-sm text-base-content">{{ $transaction->date->format('d/m/Y') }}</div>
                                            <div class="text-xs text-base-content/50">{{ $transaction->date->format('H:i') }}</div>
                                        </td>
                                        <td>
                                            <div class="flex items-center">
                                                <div class="w-8 h-8 rounded-lg flex items-center justify-center mr-3 hidden sm:flex transition-default shrink-0" style="background-color: {{ $transaction->category->color }}20">
                                                    <i class="fas fa-{{ $transaction->category->icon }} text-sm" style="color: {{ $transaction->category->color }}"></i>
                                                </div>
                                                <div class="min-w-0">
                                                    <div class="font-medium text-sm text-base-content truncate">{{ $transaction->description ?: 'Sans description' }}</div>
                                                    @if($transaction->payment_method)
                                                        <div class="text-xs text-base-content/50 capitalize truncate">{{ $transaction->payment_method }}</div>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                        <td class="hidden md:table-cell">
                                            <span class="badge badge-outline badge-sm truncate max-w-[120px]" style="border-color: {{ $transaction->category->color }}; color: {{ $transaction->category->color }}">
                                                {{ $transaction->category->name }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="font-semibold {{ $transaction->type === 'expense' ? 'text-error' : 'text-success' }} whitespace-nowrap">
                                                {{ $transaction->type === 'expense' ? '-' : '+' }}{{ formatAmount($transaction->amount) }}
                                                <span class="text-sm font-normal">FDJ</span>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="flex space-x-1 justify-center">
                                                <a href="{{ route('transactions.edit', $transaction) }}" class="btn btn-ghost btn-xs focus-ring" title="Modifier">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <form action="{{ route('transactions.destroy', $transaction) }}" method="POST" onsubmit="return confirm('Supprimer cette transaction ?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-ghost btn-xs text-error focus-ring" title="Supprimer">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-12">
                        <i class="fas fa-inbox text-5xl icon-empty mb-4"></i>
                        <h4 class="text-lg text-empty-title">Aucune transaction</h4>
                        <p class="text-empty-description mt-2">Commencez par ajouter vos premières transactions</p>
                        <a href="{{ route('transactions.create') }}" class="btn btn-primary mt-4 focus-ring">
                            <i class="fas fa-plus mr-2"></i>
                            Ajouter une transaction
                        </a>
                    </div>
                @endif
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="card bg-base-100 shadow-lg card-hover">
            <div class="card-body">
                <h3 class="card-title text-lg mb-6 text-base-content">
                    <i class="fas fa-bolt mr-3"></i>
                    Actions rapides
                </h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
                    <a href="{{ route('transactions.create', ['type' => 'expense']) }}" class="btn btn-primary flex flex-col items-center justify-center py-4 h-full focus-ring transition-default hover:scale-[1.02]">
                        <i class="fas fa-plus-circle text-2xl mb-2"></i>
                        <span class="text-sm">Nouvelle dépense</span>
                    </a>
                    
                    <a href="{{ route('transactions.create', ['type' => 'income']) }}" class="btn btn-success flex flex-col items-center justify-center py-4 h-full focus-ring transition-default hover:scale-[1.02]">
                        <i class="fas fa-money-bill-wave text-2xl mb-2"></i>
                        <span class="text-sm">Nouveau revenu</span>
                    </a>
                    
                    <a href="{{ route('categories.index') }}" class="btn btn-secondary flex flex-col items-center justify-center py-4 h-full focus-ring transition-default hover:scale-[1.02]">
                        <i class="fas fa-tags text-2xl mb-2"></i>
                        <span class="text-sm">Catégories</span>
                    </a>
                    
                    <a href="{{ route('budgets.create') }}" class="btn btn-accent flex flex-col items-center justify-center py-4 h-full focus-ring transition-default hover:scale-[1.02]">
                        <i class="fas fa-chart-pie text-2xl mb-2"></i>
                        <span class="text-sm">Budgets</span>
                    </a>
                </div>
                
                <!-- Statistiques supplémentaires -->
                <div class="divider my-6"></div>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="stats stats-vertical sm:stats-horizontal shadow bg-base-100">
                        <div class="stat">
                            <div class="stat-title text-base-content/70">Transactions totales</div>
                            <div class="stat-value text-lg text-base-content">{{ App\Models\Transaction::where('user_id', $user->id)->count() }}</div>
                            <div class="stat-desc text-base-content/50">Depuis le début</div>
                        </div>
                    </div>
                    
                    <div class="stats stats-vertical sm:stats-horizontal shadow bg-base-100">
                        <div class="stat">
                            <div class="stat-title text-base-content/70">Catégories actives</div>
                            <div class="stat-value text-lg text-base-content">{{ App\Models\Category::where('user_id', $user->id)->count() }}</div>
                            <div class="stat-desc text-base-content/50">Personnalisées</div>
                        </div>
                    </div>
                    
                    <div class="stats stats-vertical sm:stats-horizontal shadow bg-base-100">
                        <div class="stat">
                            <div class="stat-title text-base-content/70">Budgets actifs</div>
                            <div class="stat-value text-lg text-base-content">{{ App\Models\Budget::where('user_id', $user->id)->count() }}</div>
                            <div class="stat-desc text-base-content/50">En cours</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Animation pour les cartes avec un délai progressif
            const cards = document.querySelectorAll('.card-hover');
            cards.forEach((card, index) => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(20px)';
                setTimeout(() => {
                    card.style.transition = 'all 0.5s ease';
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, index * 100);
            });
            
            // Mise à jour des badges d'alerte en temps réel
            const alertCount = {{ $budgetAlerts->count() }};
            if (alertCount > 0) {
                // Ajouter une animation clignotante aux alertes
                const alertBadge = document.querySelector('.badge-error');
                if (alertBadge) {
                    setInterval(() => {
                        alertBadge.classList.toggle('opacity-50');
                    }, 1000);
                }
                
                // Notification toast pour les nouvelles alertes
                if (localStorage.getItem('newAlerts') !== 'shown') {
                    setTimeout(() => {
                        const toast = document.createElement('div');
                        toast.className = 'toast toast-end z-50';
                        toast.innerHTML = `
                            <div class="alert alert-warning shadow-lg">
                                <div>
                                    <i class="fas fa-exclamation-triangle"></i>
                                    <span>Vous avez ${alertCount} alerte(s) de budget</span>
                                </div>
                            </div>
                        `;
                        document.body.appendChild(toast);
                        localStorage.setItem('newAlerts', 'shown');
                        
                        setTimeout(() => {
                            toast.remove();
                        }, 5000);
                    }, 1000);
                }
            }
            
            // Mettre à jour les montants en temps réel
            const updateStats = () => {
                const today = new Date();
                const monthNames = ['Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 
                                   'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'];
                
                // Mettre à jour la date dans le header
                const dateElements = document.querySelectorAll('.stat-desc, .text-sm.text-base-content\\/70');
                dateElements.forEach(el => {
                    if (el.textContent.includes('décembre') || el.textContent.includes('Décembre')) {
                        const currentMonth = monthNames[today.getMonth()];
                        el.textContent = el.textContent.replace(/décembre|Décembre/, currentMonth);
                    }
                });
            };
            
            // Mettre à jour toutes les 30 secondes
            setInterval(updateStats, 30000);
            
            // Ajouter des tooltips aux boutons
            const tooltips = document.querySelectorAll('[title]');
            tooltips.forEach(tooltip => {
                tooltip.classList.add('tooltip');
                tooltip.setAttribute('data-tip', tooltip.getAttribute('title'));
            });
        });
    </script>
    @endpush
</x-app-layout>