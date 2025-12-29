<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between">
            <div>
                <h2 class="text-2xl font-bold text-base-content">
                    <i class="fas fa-piggy-bank mr-3"></i>{{ $savingsGoal->name }}
                </h2>
                <div class="breadcrumbs text-sm mt-1">
                    <ul>
                        <li><a href="{{ route('savings.index') }}">Objectifs d'épargne</a></li>
                        <li>Détails</li>
                    </ul>
                </div>
            </div>
            <div class="mt-2 md:mt-0 flex space-x-2">
                <a href="{{ route('savings.index') }}" class="btn btn-ghost">
                    <i class="fas fa-arrow-left mr-2"></i>Retour
                </a>
                <a href="{{ route('savings.edit', $savingsGoal) }}" class="btn btn-primary">
                    <i class="fas fa-edit mr-2"></i>Modifier
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-4xl mx-auto">
            <!-- En-tête -->
            <div class="card bg-base-100 shadow-lg mb-6">
                <div class="card-body">
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between">
                        <div class="flex items-center">
                            <div class="w-16 h-16 rounded-lg mr-4" style="background-color: {{ $savingsGoal->color }};"></div>
                            <div>
                                <div class="flex items-center mb-2">
                                    <h1 class="text-3xl font-bold">{{ $savingsGoal->name }}</h1>
                                    <div class="badge badge-{{ $savingsGoal->status_color }} badge-lg ml-3">
                                        {{ $savingsGoal->is_completed ? 'Terminé' : ($savingsGoal->is_overdue ? 'En retard' : 'Actif') }}
                                    </div>
                                </div>
                                @if($savingsGoal->description)
                                    <p class="opacity-70">{{ $savingsGoal->description }}</p>
                                @endif
                            </div>
                        </div>
                        
                        <div class="mt-4 md:mt-0 text-right">
                            <div class="text-3xl font-bold text-success">
                                {{ number_format($savingsGoal->current_amount, 0, ',', ' ') }} FDJ
                            </div>
                            <div class="text-lg opacity-70">
                                sur {{ number_format($savingsGoal->target_amount, 0, ',', ' ') }} FDJ
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Colonne gauche : Progression -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Barre de progression -->
                    <div class="card bg-base-100 shadow">
                        <div class="card-body">
                            <h3 class="card-title mb-4">Progression</h3>
                            <div class="space-y-4">
                                <div>
                                    <div class="flex justify-between mb-2">
                                        <span class="font-bold">Avancement</span>
                                        <span class="font-bold text-xl">{{ $savingsGoal->progress_percentage }}%</span>
                                    </div>
                                    <progress class="progress progress-primary w-full h-4" 
                                              value="{{ $savingsGoal->progress_percentage }}" 
                                              max="100"></progress>
                                </div>
                                
                                <div class="grid grid-cols-2 gap-4">
                                    <div class="p-4 bg-base-200 rounded-lg text-center">
                                        <div class="text-2xl font-bold text-success">
                                            {{ number_format($savingsGoal->current_amount, 0, ',', ' ') }} FDJ
                                        </div>
                                        <div class="text-sm opacity-70">Économisé</div>
                                    </div>
                                    <div class="p-4 bg-base-200 rounded-lg text-center">
                                        <div class="text-2xl font-bold">
                                            {{ number_format($savingsGoal->remaining_amount, 0, ',', ' ') }} FDJ
                                        </div>
                                        <div class="text-sm opacity-70">Reste</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Informations -->
                    <div class="card bg-base-100 shadow">
                        <div class="card-body">
                            <h3 class="card-title mb-4">Informations</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <div class="text-sm text-gray-500">Date de création</div>
                                    <div class="font-medium">{{ $savingsGoal->created_at->format('d/m/Y H:i') }}</div>
                                </div>
                                @if($savingsGoal->deadline)
                                    <div>
                                        <div class="text-sm text-gray-500">Date limite</div>
                                        <div class="font-medium {{ $savingsGoal->is_overdue ? 'text-error' : '' }}">
                                            {{ $savingsGoal->formatted_deadline }}
                                        </div>
                                        <div class="text-sm text-gray-500">{{ $savingsGoal->days_left_text }}</div>
                                    </div>
                                @endif
                                @if($savingsGoal->is_completed)
                                    <div>
                                        <div class="text-sm text-gray-500">Date d'achèvement</div>
                                        <div class="font-medium">{{ $savingsGoal->completed_at->format('d/m/Y H:i') }}</div>
                                    </div>
                                @endif
                                <div>
                                    <div class="text-sm text-gray-500">Taux d'épargne recommandé</div>
                                    <div class="font-medium text-success">
                                        @if($savingsGoal->monthly_amount_needed > 0)
                                            {{ number_format($savingsGoal->monthly_amount_needed, 0, ',', ' ') }} FDJ/mois
                                        @else
                                            Objectif atteint
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Colonne droite : Actions -->
                <div class="space-y-6">
                    <!-- Actions rapides -->
                    <div class="card bg-base-100 shadow">
                        <div class="card-body">
                            <h3 class="card-title mb-4">Actions</h3>
                            <div class="space-y-3">
                                @if(!$savingsGoal->is_completed)
                                    <form action="{{ route('savings.add-funds', $savingsGoal) }}" method="POST" class="w-full">
                                        @csrf
                                        <input type="hidden" name="amount" value="10000">
                                        <input type="hidden" name="description" value="Dépôt manuel">
                                        <button type="submit" class="btn btn-success w-full justify-start">
                                            <i class="fas fa-plus mr-2"></i>Ajouter 10,000 FDJ
                                        </button>
                                    </form>
                                    
                                    <form action="{{ route('savings.add-funds', $savingsGoal) }}" method="POST" class="w-full">
                                        @csrf
                                        <input type="hidden" name="amount" value="50000">
                                        <input type="hidden" name="description" value="Dépôt manuel">
                                        <button type="submit" class="btn btn-success w-full justify-start">
                                            <i class="fas fa-plus mr-2"></i>Ajouter 50,000 FDJ
                                        </button>
                                    </form>
                                    
                                    @if($savingsGoal->current_amount > 0)
                                        <form action="{{ route('savings.withdraw-funds', $savingsGoal) }}" method="POST" class="w-full">
                                            @csrf
                                            <input type="hidden" name="amount" value="10000">
                                            <input type="hidden" name="description" value="Retrait manuel">
                                            <button type="submit" class="btn btn-error w-full justify-start">
                                                <i class="fas fa-minus mr-2"></i>Retirer 10,000 FDJ
                                            </button>
                                        </form>
                                    @endif
                                    
                                    <form action="{{ route('savings.complete', $savingsGoal) }}" method="POST" class="w-full">
                                        @csrf
                                        <button type="submit" class="btn btn-warning w-full justify-start">
                                            <i class="fas fa-check mr-2"></i>Marquer comme terminé
                                        </button>
                                    </form>
                                @else
                                    <form action="{{ route('savings.reactivate', $savingsGoal) }}" method="POST" class="w-full">
                                        @csrf
                                        <button type="submit" class="btn btn-warning w-full justify-start">
                                            <i class="fas fa-redo mr-2"></i>Réactiver l'objectif
                                        </button>
                                    </form>
                                @endif
                                
                                <form action="{{ route('savings.destroy', $savingsGoal) }}" method="POST" class="w-full" onsubmit="return confirm('Supprimer cet objectif ?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-error w-full justify-start">
                                        <i class="fas fa-trash mr-2"></i>Supprimer l'objectif
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Conseils -->
                    @if($savingsGoal->is_overdue)
                        <div class="alert alert-error">
                            <i class="fas fa-exclamation-triangle"></i>
                            <span>Votre objectif est en retard. Pensez à ajuster votre plan d'épargne.</span>
                        </div>
                    @elseif($savingsGoal->remaining_days !== null && $savingsGoal->remaining_days < 30)
                        <div class="alert alert-warning">
                            <i class="fas fa-clock"></i>
                            <span>Il reste moins d'un mois. Augmentez votre taux d'épargne si possible.</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>