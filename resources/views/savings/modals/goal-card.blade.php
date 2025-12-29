[Collez tout le code corrigé ci-dessus]
@props(['goal'])

@php
    // 1. CALCUL PROGRESSION
    $percentage = $goal->target_amount > 0 
        ? min(100, ($goal->current_amount / $goal->target_amount) * 100) 
        : 0;
    
    // 2. CALCUL DATES - CORRECTION DÉFINITIVE
    $isOverdue = false;
    $daysLeft = 0;
    $showDaysBadge = false;
    $daysBadgeColor = 'info';
    $daysText = '';
    
    if ($goal->deadline && !$goal->is_completed) {
        // SIMPLE ET FIABLE : Comparaison directe des dates
        $today = now()->startOfDay(); // Aujourd'hui à minuit
        $deadlineDate = $goal->deadline->startOfDay(); // Date limite à minuit
        
        // 1. Est-ce que la date limite est PASSÉE ?
        $isOverdue = $deadlineDate->lessThan($today);
        
        // 2. Calcul des jours (TOUJOURS POSITIF)
        if ($isOverdue) {
            // Date PASSÉE : combien de jours de retard ?
            $daysLeft = $today->diffInDays($deadlineDate); // Différence en jours
            $daysText = $daysLeft . ' jour' . ($daysLeft > 1 ? 's' : '') . ' de retard';
            $daysBadgeColor = 'error';
            $showDaysBadge = true;
        } else {
            // Date FUTURE : combien de jours restants ?
            $daysLeft = $deadlineDate->diffInDays($today); // Différence en jours
            
            // On affiche un badge seulement pour les dates proches
            if ($daysLeft <= 60) { // 2 mois ou moins
                if ($daysLeft === 0) {
                    $daysText = "Aujourd'hui !";
                    $daysBadgeColor = 'warning';
                } elseif ($daysLeft === 1) {
                    $daysText = "Demain";
                    $daysBadgeColor = 'warning';
                } elseif ($daysLeft <= 7) {
                    $daysText = $daysLeft . ' jours';
                    $daysBadgeColor = 'warning';
                } else {
                    $daysText = $daysLeft . ' jours';
                    $daysBadgeColor = 'info';
                }
                $showDaysBadge = true;
            }
        }
    }
    
    // 3. Couleur et montants
    $color = $goal->color ?? '#10B981';
    $remaining = $goal->target_amount - $goal->current_amount;
@endphp

<div class="card bg-base-100 shadow-lg hover:shadow-xl transition-shadow duration-300 mb-6">
    <div class="card-body">
        <!-- EN-TÊTE -->
        <div class="flex items-start justify-between mb-4">
            <div class="flex items-center">
                <div class="w-12 h-12 rounded-lg flex items-center justify-center mr-4" 
                     style="background-color: {{ $color }}20">
                    <i class="fas fa-piggy-bank text-xl" style="color: {{ $color }}"></i>
                </div>
                <div>
                    <h3 class="card-title text-lg font-bold">{{ $goal->name }}</h3>
                    
                    @if($goal->description)
                        <p class="text-sm text-base-content/70 mt-1">{{ $goal->description }}</p>
                    @endif
                    
                    @if($goal->deadline)
                        <div class="text-sm mt-1">
                            <i class="far fa-calendar-alt mr-1"></i>
                            Échéance : <strong>{{ $goal->deadline->format('d/m/Y') }}</strong>
                            
                            @if($showDaysBadge)
                                <span class="badge badge-{{ $daysBadgeColor }} badge-sm ml-2">
                                    {{ $daysText }}
                                </span>
                            @endif
                            
                            <!-- DEBUG VISUEL (optionnel - à désactiver après vérification) -->
                            @if(false) {{-- Mettez à true pour debug --}}
                            <div class="text-xs text-gray-500 mt-1">
                                [Debug] Date: {{ $goal->deadline->format('Y-m-d') }} | 
                                Today: {{ now()->format('Y-m-d') }} | 
                                Passée? {{ $isOverdue ? 'OUI' : 'NON' }} | 
                                Jours: {{ $daysLeft }}
                            </div>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
            
            <!-- STATUT -->
            <div class="text-right">
                @if($goal->is_completed)
                    <span class="badge badge-success badge-lg">
                        <i class="fas fa-check mr-1"></i>Terminé
                    </span>
                    @if($goal->completed_at)
                        <div class="text-xs opacity-70 mt-1">
                            {{ $goal->completed_at->format('d/m/Y') }}
                        </div>
                    @endif
                @elseif($isOverdue)
                    <span class="badge badge-error badge-lg">
                        <i class="fas fa-exclamation-triangle mr-1"></i>En retard
                    </span>
                @else
                    <span class="badge badge-info badge-lg">
                        <i class="fas fa-spinner mr-1"></i>Actif
                    </span>
                @endif
            </div>
        </div>
        
        <!-- PROGRESSION -->
        <div class="mb-6">
            <div class="flex justify-between items-center mb-2">
                <div class="font-medium">Progression</div>
                <div class="font-bold text-lg">{{ round($percentage, 1) }}%</div>
            </div>
            
            <!-- Barre de progression -->
            <div class="w-full bg-base-200 rounded-full h-4 mb-3">
                <div class="h-4 rounded-full transition-all duration-500" 
                     style="width: {{ $percentage }}%; background-color: {{ $color }}">
                </div>
            </div>
            
            <!-- MONTANTS -->
            <div class="grid grid-cols-3 gap-4 text-center">
                <div>
                    <div class="text-sm opacity-70">Économisé</div>
                    <div class="font-bold text-lg text-success">
                        {{ number_format($goal->current_amount, 0, ',', ' ') }} FDJ
                    </div>
                </div>
                <div>
                    <div class="text-sm opacity-70">Objectif</div>
                    <div class="font-bold text-lg text-primary">
                        {{ number_format($goal->target_amount, 0, ',', ' ') }} FDJ
                    </div>
                </div>
                <div>
                    <div class="text-sm opacity-70">Reste</div>
                    <div class="font-bold text-lg {{ $remaining > 0 ? 'text-error' : 'text-success' }}">
                        {{ number_format(abs($remaining), 0, ',', ' ') }} FDJ
                    </div>
                </div>
            </div>
            
            <!-- RECOMMANDATIONS (seulement si date future et pas terminé) -->
            @if($goal->deadline && !$goal->is_completed && !$isOverdue && $daysLeft > 0 && $remaining > 0)
                @php
                    // Calculs réalistes
                    $perDay = $daysLeft > 0 ? ceil($remaining / $daysLeft) : 0;
                    $perWeek = ceil($remaining / max(1, ceil($daysLeft / 7)));
                    $perMonth = ceil($remaining / max(1, ceil($daysLeft / 30)));
                @endphp
                
                @if($perDay < 1000000) {{-- Éviter les montants irréalistes --}}
                <div class="mt-4 p-3 bg-base-200 rounded-lg">
                    <div class="text-sm font-medium mb-2">Pour atteindre votre objectif à temps :</div>
                    <div class="grid grid-cols-3 gap-2 text-center text-xs">
                        <div>
                            <div class="font-bold">{{ number_format($perDay, 0, ',', ' ') }} FDJ</div>
                            <div class="opacity-70">par jour</div>
                        </div>
                        <div>
                            <div class="font-bold">{{ number_format($perWeek, 0, ',', ' ') }} FDJ</div>
                            <div class="opacity-70">par semaine</div>
                        </div>
                        <div>
                            <div class="font-bold">{{ number_format($perMonth, 0, ',', ' ') }} FDJ</div>
                            <div class="opacity-70">par mois</div>
                        </div>
                    </div>
                </div>
                @endif
            @endif
        </div>
        
        <!-- ACTIONS -->
        <div class="card-actions justify-end">
            @if(!$goal->is_completed)
                <!-- Ajouter des fonds -->
                <button onclick="openAddContributionModal({{ $goal->id }}, '{{ addslashes($goal->name) }}', {{ $goal->current_amount }})" 
                        class="btn btn-success btn-sm">
                    <i class="fas fa-plus mr-1"></i>Ajouter
                </button>
                
                <!-- Retirer -->
                @if($goal->current_amount > 0)
                    <button onclick="openWithdrawModal({{ $goal->id }}, '{{ addslashes($goal->name) }}', {{ $goal->current_amount }})" 
                            class="btn btn-warning btn-sm">
                        <i class="fas fa-minus mr-1"></i>Retirer
                    </button>
                @endif
                
                <!-- Marquer comme terminé -->
                @if($goal->current_amount >= $goal->target_amount)
                    <form action="{{ route('savings.complete', $goal) }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="btn btn-primary btn-sm" onclick="return confirm('Marquer cet objectif comme terminé ?')">
                            <i class="fas fa-check mr-1"></i>Terminer
                        </button>
                    </form>
                @endif
            @else
                <!-- Réactiver -->
                <form action="{{ route('savings.reactivate', $goal) }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="btn btn-info btn-sm" onclick="return confirm('Réactiver cet objectif ?')">
                        <i class="fas fa-redo mr-1"></i>Réactiver
                    </button>
                </form>
            @endif
            
            <!-- Modifier -->
            <a href="{{ route('savings.edit', $goal) }}" class="btn btn-primary btn-sm">
                <i class="fas fa-edit mr-1"></i>Modifier
            </a>
            
            <!-- Supprimer -->
            <form action="{{ route('savings.destroy', $goal) }}" method="POST" class="inline" 
                  onsubmit="return confirm('Supprimer définitivement cet objectif ?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-error btn-sm">
                    <i class="fas fa-trash mr-1"></i>Supprimer
                </button>
            </form>
        </div>
        
        <!-- ALERTES CONTEXTUELLES -->
        @if($goal->is_completed)
            <div class="mt-4 p-3 bg-success/10 border border-success/20 rounded-lg">
                <div class="flex items-center text-success">
                    <i class="fas fa-trophy mr-2"></i>
                    <span class="font-medium">Objectif atteint !</span>
                </div>
                @if($goal->completed_at)
                    <div class="text-sm opacity-70 mt-1">
                        Complété le {{ $goal->completed_at->format('d/m/Y') }}
                    </div>
                @endif
            </div>
        @elseif($isOverdue)
            <div class="mt-4 p-3 bg-error/10 border border-error/20 rounded-lg">
                <div class="flex items-center text-error">
                    <i class="fas fa-exclamation-circle mr-2"></i>
                    <span class="font-medium">Objectif en retard</span>
                </div>
                <div class="text-sm opacity-70 mt-1">
                    La date limite était le {{ $goal->deadline->format('d/m/Y') }}
                </div>
            </div>
        @elseif($daysLeft <= 3 && $daysLeft > 0)
            <div class="mt-4 p-3 bg-warning/10 border border-warning/20 rounded-lg">
                <div class="flex items-center text-warning">
                    <i class="fas fa-clock mr-2"></i>
                    <span class="font-medium">Derniers jours !</span>
                </div>
                <div class="text-sm opacity-70 mt-1">
                    Plus que {{ $daysLeft }} jour{{ $daysLeft > 1 ? 's' : '' }} avant l'échéance
                </div>
            </div>
        @endif
    </div>
</div>