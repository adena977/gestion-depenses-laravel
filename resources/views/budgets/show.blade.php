<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between">
            <div>
                <h2 class="text-2xl font-bold text-base-content">
                    <i class="fas fa-eye mr-3"></i>Détails du budget
                </h2>
                <div class="breadcrumbs text-sm mt-1">
                    <ul>
                        <li><a href="{{ route('dashboard') }}"><i class="fas fa-home"></i> Dashboard</a></li>
                        <li><a href="{{ route('budgets.index') }}">Budgets</a></li>
                        <li>Détails</li>
                    </ul>
                </div>
            </div>
            <div class="mt-2 md:mt-0 flex space-x-2">
                <a href="{{ route('budgets.edit', $budget) }}" class="btn btn-secondary">
                    <i class="fas fa-edit mr-2"></i>Modifier
                </a>
                <a href="{{ route('budgets.index') }}" class="btn btn-ghost">
                    <i class="fas fa-arrow-left mr-2"></i>Retour
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-6xl mx-auto">
            <!-- En-tête du budget -->
            <div class="card bg-base-100 shadow-lg mb-6">
                <div class="card-body">
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-6">
                        <div class="flex items-center mb-4 md:mb-0">
                            <div class="w-20 h-20 rounded-2xl flex items-center justify-center mr-6" 
                                 style="background-color: {{ $budget->category->color }}20">
                                <i class="fas fa-{{ $budget->category->icon }} text-3xl" 
                                   style="color: {{ $budget->category->color }}"></i>
                            </div>
                            <div>
                                <h1 class="text-3xl font-bold">{{ $budget->category->name }}</h1>
                                <div class="flex items-center text-lg text-base-content/70 mt-1">
                                    <i class="fas fa-calendar-alt mr-2"></i>
                                    <span>{{ $budget->formatted_period }}</span>
                                    <span class="mx-3">•</span>
                                    <i class="fas fa-clock mr-2"></i>
                                    <span>
                                        {{ $budget->start_date->format('d/m/Y') }} - {{ $budget->end_date->format('d/m/Y') }}
                                    </span>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Statut -->
                        <div class="text-right">
                            <div class="badge badge-lg {{ $budget->status_color === 'error' ? 'badge-error' : ($budget->status_color === 'warning' ? 'badge-warning' : 'badge-success') }} mb-2">
                                @if($budget->progress_percentage >= 100)
                                    <i class="fas fa-exclamation-circle mr-2"></i>Dépassé
                                @elseif($budget->progress_percentage >= $budget->threshold_percentage)
                                    <i class="fas fa-exclamation-triangle mr-2"></i>Attention
                                @else
                                    <i class="fas fa-check-circle mr-2"></i>Dans les clous
                                @endif
                            </div>
                            <div class="text-2xl font-bold {{ $budget->status_color === 'error' ? 'text-error' : ($budget->status_color === 'warning' ? 'text-warning' : 'text-success') }}">
                                {{ round($budget->progress_percentage, 1) }}%
                            </div>
                        </div>
                    </div>
                    
                    <!-- Barre de progression détaillée -->
                    <div class="mb-8">
                        <div class="flex justify-between text-sm mb-3">
                            <div>
                                <span class="font-semibold">Progression du budget</span>
                                <span class="text-base-content/70 ml-2">
                                    ({{ number_format($budget->spent_amount, 2, ',', ' ') }} FDJ / {{ number_format($budget->amount, 2, ',', ' ') }} FDJ)
                                </span>
                            </div>
                            <div class="font-bold">
                                {{ number_format($budget->remaining_amount, 2, ',', ' ') }} FDJ restants
                            </div>
                        </div>
                        
                        <div class="w-full bg-base-300 rounded-full h-4 mb-2">
                            <div class="h-4 rounded-full {{ $budget->status_color === 'error' ? 'bg-error' : ($budget->status_color === 'warning' ? 'bg-warning' : 'bg-success') }}"
                                 style="width: {{ min($budget->progress_percentage, 100) }}%">
                            </div>
                        </div>
                        
                        <!-- Points de repère -->
                        <div class="flex justify-between text-xs text-base-content/50">
                            <div class="text-left">
                                <div>0%</div>
                                <div>0 FDJ</div>
                            </div>
                            <div class="text-center">
                                <div>Seuil: {{ $budget->threshold_percentage }}%</div>
                                <div>{{ number_format(($budget->amount * $budget->threshold_percentage / 100), 2, ',', ' ') }} FDJ</div>
                            </div>
                            <div class="text-right">
                                <div>100%</div>
                                <div>{{ number_format($budget->amount, 2, ',', ' ') }} FDJ</div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Statistiques -->
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div class="stat bg-base-200 rounded-lg p-4">
                            <div class="stat-title">Budget total</div>
                            <div class="stat-value text-success text-xl">{{ number_format($budget->amount, 2, ',', ' ') }} FDJ</div>
                        </div>
                        
                        <div class="stat bg-base-200 rounded-lg p-4">
                            <div class="stat-title">Dépensé</div>
                            <div class="stat-value text-error text-xl">{{ number_format($budget->spent_amount, 2, ',', ' ') }} FDJ</div>
                        </div>
                        
                        <div class="stat bg-base-200 rounded-lg p-4">
                            <div class="stat-title">Reste à dépenser</div>
                            <div class="stat-value {{ $budget->remaining_amount > 0 ? 'text-success' : 'text-error' }} text-xl">
                                {{ number_format($budget->remaining_amount, 2, ',', ' ') }} FDJ
                            </div>
                        </div>
                        
                        <div class="stat bg-base-200 rounded-lg p-4">
                            <div class="stat-title">Transactions</div>
                            <div class="stat-value text-primary text-xl">{{ $budget->category->transactions()->whereBetween('date', [$budget->start_date, $budget->end_date])->count() }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Informations détaillées -->
                <div class="lg:col-span-2">
                    <!-- Transactions récentes -->
                    <div class="card bg-base-100 shadow-lg mb-6">
                        <div class="card-body">
                            <div class="flex justify-between items-center mb-6">
                                <h3 class="card-title">
                                    <i class="fas fa-history mr-3"></i>Transactions récentes
                                </h3>
                                <a href="{{ route('transactions.create', ['category_id' => $budget->category_id, 'type' => 'expense']) }}" 
                                   class="btn btn-primary btn-sm">
                                    <i class="fas fa-plus mr-2"></i>Ajouter
                                </a>
                            </div>
                            
                            @if($transactions->count() > 0)
                                <div class="overflow-x-auto">
                                    <table class="table table-zebra">
                                        <thead>
                                            <tr>
                                                <th>Date</th>
                                                <th>Description</th>
                                                <th>Montant</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($transactions as $transaction)
                                                <tr>
                                                    <td class="whitespace-nowrap">
                                                        <div class="font-medium">{{ $transaction->date->format('d/m/Y') }}</div>
                                                        <div class="text-xs opacity-70">{{ $transaction->date->format('H:i') }}</div>
                                                    </td>
                                                    <td>
                                                        <div class="font-medium">{{ $transaction->description ?: 'Sans description' }}</div>
                                                        @if($transaction->payment_method)
                                                            <div class="text-xs opacity-70 capitalize">{{ $transaction->payment_method }}</div>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <div class="font-bold text-error">{{ number_format($transaction->amount, 2, ',', ' ') }} FDJ</div>
                                                    </td>
                                                    <td>
                                                        <a href="{{ route('transactions.edit', $transaction) }}" 
                                                           class="btn btn-ghost btn-xs" 
                                                           title="Modifier">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                                
                                <!-- Pagination -->
                                <div class="mt-6">
                                    {{ $transactions->links() }}
                                </div>
                            @else
                                <div class="text-center py-8">
                                    <div class="inline-block p-4 rounded-full bg-base-200 mb-4">
                                        <i class="fas fa-inbox text-3xl text-base-content/30"></i>
                                    </div>
                                    <h4 class="text-lg font-semibold text-base-content/70 mb-2">Aucune transaction</h4>
                                    <p class="text-base-content/50 mb-4">
                                        Aucune dépense enregistrée pour ce budget.
                                    </p>
                                    <a href="{{ route('transactions.create', ['category_id' => $budget->category_id, 'type' => 'expense']) }}" 
                                       class="btn btn-primary">
                                        <i class="fas fa-plus mr-2"></i>Ajouter une dépense
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
                
                <!-- Sidebar informations -->
                <div>
                    <!-- Informations du budget -->
                    <div class="card bg-base-100 shadow-lg mb-6">
                        <div class="card-body">
                            <h3 class="card-title mb-4">
                                <i class="fas fa-info-circle mr-3"></i>Informations
                            </h3>
                            
                            <div class="space-y-4">
                                <div>
                                    <div class="text-sm text-base-content/70">Catégorie</div>
                                    <div class="flex items-center mt-1">
                                        <div class="w-6 h-6 rounded mr-2" style="background-color: {{ $budget->category->color }}"></div>
                                        <span class="font-medium">{{ $budget->category->name }}</span>
                                    </div>
                                </div>
                                
                                <div>
                                    <div class="text-sm text-base-content/70">Période</div>
                                    <div class="font-medium mt-1">{{ $budget->formatted_period }}</div>
                                </div>
                                
                                <div>
                                    <div class="text-sm text-base-content/70">Dates</div>
                                    <div class="font-medium mt-1">
                                        {{ $budget->start_date->format('d/m/Y') }} → {{ $budget->end_date->format('d/m/Y') }}
                                    </div>
                                    <div class="text-xs text-base-content/50 mt-1">
                                        @php
                                            $daysTotal = $budget->start_date->diffInDays($budget->end_date) + 1;
                                            $daysPassed = min($daysTotal, max(0, now()->diffInDays($budget->start_date) + 1));
                                            $daysLeft = max(0, $budget->end_date->diffInDays(now()) + 1);
                                        @endphp
                                        {{ $daysPassed }} jour(s) passé(s) • {{ $daysLeft }} jour(s) restant(s)
                                    </div>
                                </div>
                                
                                <div>
                                    <div class="text-sm text-base-content/70">Seuil d'alerte</div>
                                    <div class="flex items-center mt-1">
                                        <span class="font-medium">{{ $budget->threshold_percentage }}%</span>
                                        <div class="w-24 h-2 bg-base-300 rounded-full ml-3">
                                            <div class="h-2 rounded-full bg-warning" style="width: {{ $budget->threshold_percentage }}%"></div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div>
                                    <div class="text-sm text-base-content/70">Notifications</div>
                                    <div class="flex items-center mt-1">
                                        @if($budget->notifications_enabled)
                                            <i class="fas fa-bell text-success mr-2"></i>
                                            <span class="font-medium text-success">Activées</span>
                                        @else
                                            <i class="fas fa-bell-slash text-base-content/50 mr-2"></i>
                                            <span class="font-medium text-base-content/50">Désactivées</span>
                                        @endif
                                    </div>
                                </div>
                                
                                <div>
                                    <div class="text-sm text-base-content/70">Créé le</div>
                                    <div class="font-medium mt-1">{{ $budget->created_at->format('d/m/Y à H:i') }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Estimation quotidienne -->
                    <div class="card bg-base-100 shadow-lg mb-6">
                        <div class="card-body">
                            <h3 class="card-title mb-4">
                                <i class="fas fa-calculator mr-3"></i>Estimation quotidienne
                            </h3>
                            
                            @php
                                $today = now();
                                $daysLeft = max(0, $budget->end_date->diffInDays($today) + 1);
                                $dailyBudget = $daysLeft > 0 ? $budget->remaining_amount / $daysLeft : $budget->remaining_amount;
                                
                                $daysTotal = $budget->start_date->diffInDays($budget->end_date) + 1;
                                $averagePerDay = $budget->amount / $daysTotal;
                                $actualAveragePerDay = $budget->spent_amount / max(1, $daysTotal - $daysLeft);
                            @endphp
                            
                            <div class="space-y-4">
                                <div>
                                    <div class="text-sm text-base-content/70">Budget quotidien initial</div>
                                    <div class="font-bold text-lg text-success mt-1">
                                        {{ number_format($averagePerDay, 2, ',', ' ') }} FDJ/jour
                                    </div>
                                </div>
                                
                                <div>
                                    <div class="text-sm text-base-content/70">Moyenne actuelle</div>
                                    <div class="font-bold text-lg {{ $actualAveragePerDay > $averagePerDay ? 'text-error' : 'text-success' }} mt-1">
                                        {{ number_format($actualAveragePerDay, 2, ',', ' ') }} FDJ/jour
                                    </div>
                                </div>
                                
                                @if($daysLeft > 0 && $budget->remaining_amount > 0)
                                    <div class="p-3 bg-base-200 rounded-lg">
                                        <div class="text-sm text-base-content/70 mb-1">
                                            Pour respecter le budget :
                                        </div>
                                        <div class="font-bold text-2xl text-success">
                                            {{ number_format($dailyBudget, 2, ',', ' ') }} FDJ/jour
                                        </div>
                                        <div class="text-xs text-base-content/50 mt-1">
                                            pendant {{ $daysLeft }} jour(s)
                                        </div>
                                    </div>
                                @elseif($daysLeft <= 0)
                                    <div class="alert alert-info">
                                        <i class="fas fa-info-circle"></i>
                                        <span>Période terminée</span>
                                    </div>
                                @else
                                    <div class="alert alert-error">
                                        <i class="fas fa-exclamation-circle"></i>
                                        <span>Budget déjà dépassé</span>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                    
                    <!-- Actions rapides -->
                    <div class="card bg-base-100 shadow-lg">
                        <div class="card-body">
                            <h3 class="card-title mb-4">
                                <i class="fas fa-bolt mr-3"></i>Actions
                            </h3>
                            
                            <div class="space-y-2">
                                <a href="{{ route('transactions.create', ['category_id' => $budget->category_id, 'type' => 'expense']) }}" 
                                   class="btn btn-primary w-full justify-start">
                                    <i class="fas fa-plus mr-3"></i>Nouvelle dépense
                                </a>
                                
                                <a href="{{ route('budgets.edit', $budget) }}" 
                                   class="btn btn-secondary w-full justify-start">
                                    <i class="fas fa-edit mr-3"></i>Modifier le budget
                                </a>
                                
                                <a href="{{ route('budgets.create', ['category_id' => $budget->category_id]) }}" 
                                   class="btn btn-outline w-full justify-start">
                                    <i class="fas fa-redo mr-3"></i>Renouveler le budget
                                </a>
                                
                                <button onclick="document.getElementById('delete-modal').showModal()" 
                                        class="btn btn-outline btn-error w-full justify-start">
                                    <i class="fas fa-trash mr-3"></i>Supprimer le budget
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de suppression -->
    <dialog id="delete-modal" class="modal">
        <div class="modal-box">
            <h3 class="font-bold text-lg">Confirmer la suppression</h3>
            <div class="py-4 space-y-4">
                <p>
                    Êtes-vous sûr de vouloir supprimer le budget pour 
                    <strong>"{{ $budget->category->name }}"</strong> ?
                </p>
                
                @php
                    $transactionCount = $budget->category->transactions()
                        ->where('user_id', auth()->id())
                        ->whereBetween('date', [$budget->start_date, $budget->end_date])
                        ->count();
                @endphp
                
                @if($transactionCount > 0)
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i>
                        <span>
                            Ce budget contient <strong>{{ $transactionCount }} transactions</strong>.
                            Les transactions ne seront pas supprimées, seul le budget le sera.
                        </span>
                    </div>
                @endif
            </div>
            
            <div class="modal-action">
                <form method="dialog">
                    <button class="btn">Annuler</button>
                </form>
                <form action="{{ route('budgets.destroy', $budget) }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-error">
                        <i class="fas fa-trash mr-2"></i>Supprimer
                    </button>
                </form>
            </div>
        </div>
        <form method="dialog" class="modal-backdrop">
            <button>close</button>
        </form>
    </dialog>

    @push('styles')
    <style>
        .table-zebra tbody tr:nth-child(even) {
            background-color: hsl(var(--b2));
        }
        
        .progress-bar {
            transition: width 0.5s ease;
        }
        
        .stat:hover {
            transform: translateY(-2px);
            transition: transform 0.2s ease;
        }
    </style>
    @endpush

    @push('scripts')
    <script>
        // Initialiser les tooltips
        document.addEventListener('DOMContentLoaded', function() {
            // Animation pour les stats
            const stats = document.querySelectorAll('.stat');
            stats.forEach((stat, index) => {
                stat.style.animationDelay = `${index * 0.1}s`;
                stat.classList.add('animate-fadeInUp');
            });
            
            // Mettre à jour le compteur de jours
            function updateDaysCounter() {
                const startDate = new Date('{{ $budget->start_date->format("Y-m-d") }}');
                const endDate = new Date('{{ $budget->end_date->format("Y-m-d") }}');
                const today = new Date();
                
                const daysTotal = Math.ceil((endDate - startDate) / (1000 * 60 * 60 * 24)) + 1;
                const daysPassed = Math.min(daysTotal, Math.max(0, Math.ceil((today - startDate) / (1000 * 60 * 60 * 24)) + 1));
                const daysLeft = Math.max(0, Math.ceil((endDate - today) / (1000 * 60 * 60 * 24)) + 1);
                
                // Mettre à jour l'affichage si nécessaire
                const daysElement = document.getElementById('days-counter');
                if (daysElement) {
                    daysElement.textContent = `${daysPassed} jour(s) passé(s) • ${daysLeft} jour(s) restant(s)`;
                }
            }
            
            // Mettre à jour toutes les heures
            updateDaysCounter();
            setInterval(updateDaysCounter, 3600000); // Toutes les heures
        });
    </script>
    @endpush
</x-app-layout>