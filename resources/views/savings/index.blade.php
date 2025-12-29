<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between">
            <h2 class="text-2xl font-bold text-base-content">
                <i class="fas fa-piggy-bank mr-3"></i>Objectifs d'épargne
            </h2>
            <div class="mt-2 md:mt-0 flex space-x-2">
                <a href="{{ route('savings.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus mr-2"></i>Nouvel objectif
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <!-- Statistiques -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <!-- Objectifs actifs -->
            <div class="card bg-base-100 shadow">
                <div class="card-body">
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="stat-title text-base-content/70">Objectifs actifs</div>
                            <div class="stat-value text-primary">{{ $activeGoals->count() }}</div>
                            <div class="stat-desc">En cours</div>
                        </div>
                        <div class="stat-figure text-primary">
                            <i class="fas fa-bullseye text-3xl"></i>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Objectifs terminés -->
            <div class="card bg-base-100 shadow">
                <div class="card-body">
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="stat-title text-base-content/70">Objectifs terminés</div>
                            <div class="stat-value text-success">{{ $completedGoals->count() }}</div>
                            <div class="stat-desc">Atteints</div>
                        </div>
                        <div class="stat-figure text-success">
                            <i class="fas fa-check-circle text-3xl"></i>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- En retard -->
            <div class="card bg-base-100 shadow">
                <div class="card-body">
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="stat-title text-base-content/70">En retard</div>
                            <div class="stat-value text-error">{{ $overdueGoals->count() }}</div>
                            <div class="stat-desc">À rattraper</div>
                        </div>
                        <div class="stat-figure text-error">
                            <i class="fas fa-clock text-3xl"></i>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Total économisé -->
            <div class="card bg-base-100 shadow">
                <div class="card-body">
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="stat-title text-base-content/70">Total économisé</div>
                            <div class="stat-value text-info">{{ number_format($totalSaved, 0, ',', ' ') }} FDJ</div>
                            <div class="stat-desc">Sur {{ number_format($totalTarget, 0, ',', ' ') }} FDJ</div>
                        </div>
                        <div class="stat-figure text-info">
                            <i class="fas fa-coins text-3xl"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Onglets -->
        <div class="tabs tabs-boxed mb-6">
            <a class="tab {{ $activeTab === 'active' ? 'tab-active' : '' }}" 
               href="{{ route('savings.index', ['tab' => 'active']) }}">
                <i class="fas fa-spinner mr-2"></i>Actifs
                <span class="badge badge-primary ml-2">{{ $activeGoals->count() }}</span>
            </a>
            <a class="tab {{ $activeTab === 'completed' ? 'tab-active' : '' }}" 
               href="{{ route('savings.index', ['tab' => 'completed']) }}">
                <i class="fas fa-check-circle mr-2"></i>Terminés
                <span class="badge badge-success ml-2">{{ $completedGoals->count() }}</span>
            </a>
            <a class="tab {{ $activeTab === 'overdue' ? 'tab-active' : '' }}" 
               href="{{ route('savings.index', ['tab' => 'overdue']) }}">
                <i class="fas fa-clock mr-2"></i>En retard
                <span class="badge badge-error ml-2">{{ $overdueGoals->count() }}</span>
            </a>
            <a class="tab {{ $activeTab === 'all' ? 'tab-active' : '' }}" 
               href="{{ route('savings.index', ['tab' => 'all']) }}">
                <i class="fas fa-list mr-2"></i>Tous
                <span class="badge badge-info ml-2">{{ $savingsGoals->count() }}</span>
            </a>
        </div>

        <!-- Liste des objectifs -->
        @if($savingsGoals->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($savingsGoals as $goal)
                    <div class="card bg-base-100 shadow hover:shadow-lg transition-shadow duration-300">
                        <div class="card-body">
                            <!-- En-tête avec statut -->
                            <div class="flex justify-between items-start mb-4">
                                <div>
                                    <div class="flex items-center mb-2">
                                        <h3 class="card-title text-lg">{{ $goal->name }}</h3>
                                        <div class="badge badge-{{ $goal->status_color }} badge-lg ml-3">
                                            {{ $goal->is_completed ? 'Terminé' : ($goal->is_overdue ? 'En retard' : 'Actif') }}
                                        </div>
                                    </div>
                                    @if($goal->description)
                                        <p class="text-sm opacity-70">{{ Str::limit($goal->description, 50) }}</p>
                                    @endif
                                </div>
                                <div class="dropdown dropdown-end">
                                    <div tabindex="0" role="button" class="btn btn-ghost btn-sm">
                                        <i class="fas fa-ellipsis-v"></i>
                                    </div>
                                    <ul tabindex="0" class="dropdown-content z-[1] menu p-2 shadow bg-base-100 rounded-box w-52">
                                        <li><a href="{{ route('savings.show', $goal) }}"><i class="fas fa-eye"></i>Voir</a></li>
                                        <li><a href="{{ route('savings.edit', $goal) }}"><i class="fas fa-edit"></i>Modifier</a></li>
                                        @if(!$goal->is_completed)
                                            <li><a href="#" onclick="quickAdd({{ $goal->id }}, 10000)"><i class="fas fa-plus"></i>Ajouter 10k</a></li>
                                        @endif
                                        <li><hr class="my-2"></li>
                                        <li>
                                            <form action="{{ route('savings.destroy', $goal) }}" method="POST" onsubmit="return confirm('Supprimer cet objectif ?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-error"><i class="fas fa-trash"></i>Supprimer</button>
                                            </form>
                                        </li>
                                    </ul>
                                </div>
                            </div>

                            <!-- Progression -->
                            <div class="mb-6">
                                <div class="flex justify-between mb-1">
                                    <span class="font-medium">
                                        {{ number_format($goal->current_amount, 0, ',', ' ') }} FDJ
                                    </span>
                                    <span class="font-bold">{{ $goal->progress_percentage }}%</span>
                                </div>
                                <progress class="progress progress-primary w-full h-3" 
                                          value="{{ $goal->progress_percentage }}" 
                                          max="100"></progress>
                                <div class="flex justify-between mt-1 text-sm opacity-70">
                                    <span>Objectif: {{ number_format($goal->target_amount, 0, ',', ' ') }} FDJ</span>
                                    <span>Reste: {{ number_format($goal->remaining_amount, 0, ',', ' ') }} FDJ</span>
                                </div>
                            </div>

                            <!-- Informations -->
                            <div class="space-y-2 text-sm">
                                @if($goal->deadline)
                                    <div class="flex justify-between">
                                        <span class="opacity-70">Date limite:</span>
                                        <span class="{{ $goal->is_overdue ? 'text-error font-bold' : '' }}">
                                            {{ $goal->formatted_deadline }}
                                        </span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="opacity-70">Temps restant:</span>
                                        <span>{{ $goal->days_left_text }}</span>
                                    </div>
                                @endif
                                <div class="flex justify-between">
                                    <span class="opacity-70">Créé le:</span>
                                    <span>{{ $goal->created_at->format('d/m/Y') }}</span>
                                </div>
                            </div>

                            <!-- Actions rapides -->
                            <div class="flex justify-between mt-4 pt-4 border-t border-base-300">
                                @if(!$goal->is_completed)
                                    <button onclick="quickAdd({{ $goal->id }}, 5000)" 
                                            class="btn btn-success btn-sm">
                                        +5,000 FDJ
                                    </button>
                                @else
                                    <form action="{{ route('savings.reactivate', $goal) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="btn btn-warning btn-sm">
                                            <i class="fas fa-redo mr-1"></i>Réactiver
                                        </button>
                                    </form>
                                @endif
                                <a href="{{ route('savings.show', $goal) }}" 
                                   class="btn btn-primary btn-sm">
                                    <i class="fas fa-chart-bar mr-1"></i>Détails
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="card bg-base-100 shadow">
                <div class="card-body">
                    <div class="text-center py-8">
                        <i class="fas fa-piggy-bank text-6xl opacity-20 mb-4"></i>
                        <h3 class="text-xl font-bold mb-2">Aucun objectif d'épargne</h3>
                        <p class="text-center opacity-70 mb-6">Commencez à épargner pour vos projets futurs</p>
                        <a href="{{ route('savings.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus-circle mr-2"></i>Créer mon premier objectif
                        </a>
                    </div>
                </div>
            </div>
        @endif
    </div>

    @push('scripts')
    <script>
        function quickAdd(goalId, amount) {
            if (confirm(`Ajouter ${amount.toLocaleString()} FDJ à cet objectif ?`)) {
                axios.post(`/savings/${goalId}/add-funds`, {
                    amount: amount,
                    description: 'Ajout rapide',
                    _token: '{{ csrf_token() }}'
                })
                .then(response => {
                    if (response.data.success) {
                        location.reload();
                    }
                })
                .catch(error => {
                    alert('Erreur: ' + (error.response?.data?.message || 'Erreur inconnue'));
                });
            }
        }
    </script>
    @endpush
</x-app-layout>