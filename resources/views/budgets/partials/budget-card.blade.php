@php
    $percentage = $budget->progress_percentage;
    $statusColor = $budget->status_color;
    $isExceeded = $budget->progress_percentage >= 100;
    $isNearThreshold = $budget->progress_percentage >= $budget->threshold_percentage && !$isExceeded;
@endphp

<div class="budget-card border border-base-300 rounded-lg p-5 hover:shadow-lg transition-all">
    <!-- En-tête -->
    <div class="flex justify-between items-start mb-4">
        <div class="flex items-center">
            <div class="w-12 h-12 rounded-lg flex items-center justify-center mr-3" 
                 style="background-color: {{ $budget->category->color }}20">
                <i class="fas fa-{{ $budget->category->icon }} text-xl" 
                   style="color: {{ $budget->category->color }}"></i>
            </div>
            <div>
                <div class="font-bold text-lg">{{ $budget->category->name }}</div>
                <div class="flex items-center text-sm text-base-content/70">
                    <i class="fas fa-calendar-alt mr-1"></i>
                    <span>{{ $budget->formatted_period }}</span>
                    <span class="mx-2">•</span>
                    <i class="fas fa-bell mr-1"></i>
                    <span>Seuil: {{ $budget->threshold_percentage }}%</span>
                </div>
            </div>
        </div>
        
        <!-- Actions -->
        <div class="dropdown dropdown-end">
            <button class="btn btn-ghost btn-xs">
                <i class="fas fa-ellipsis-v"></i>
            </button>
            <ul tabindex="0" class="dropdown-content z-[1] menu p-2 shadow bg-base-100 rounded-box w-40">
                <li>
                    <a href="{{ route('budgets.edit', $budget) }}">
                        <i class="fas fa-edit"></i> Modifier
                    </a>
                </li>
                <li>
                    <a href="{{ route('budgets.show', $budget) }}">
                        <i class="fas fa-eye"></i> Détails
                    </a>
                </li>
                <li>
                    <button onclick="document.getElementById('delete-modal-{{ $budget->id }}').showModal()">
                        <i class="fas fa-trash text-error"></i> Supprimer
                    </button>
                </li>
            </ul>
        </div>
        
        <!-- Modal de suppression -->
        <dialog id="delete-modal-{{ $budget->id }}" class="modal">
            <div class="modal-box">
                <h3 class="font-bold text-lg">Confirmer la suppression</h3>
                <p class="py-4">
                    Êtes-vous sûr de vouloir supprimer le budget pour 
                    <strong>"{{ $budget->category->name }}"</strong> ?
                </p>
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
    </div>
    
    <!-- Barre de progression -->
    <div class="mb-4">
        <div class="flex justify-between text-sm mb-2">
            <div>
                <span class="font-medium">Progression</span>
                @if($isExceeded)
                    <span class="badge badge-error badge-sm ml-2">
                        <i class="fas fa-exclamation-circle mr-1"></i>Dépassé
                    </span>
                @elseif($isNearThreshold)
                    <span class="badge badge-warning badge-sm ml-2">
                        <i class="fas fa-exclamation-triangle mr-1"></i>Attention
                    </span>
                @endif
            </div>
            <div class="font-bold text-{{ $statusColor }}">
                {{ round($percentage, 1) }}%
            </div>
        </div>
        
        <div class="w-full bg-base-300 rounded-full h-3">
            <div class="progress-bar rounded-full bg-{{ $statusColor }}" 
                 style="width: {{ min($percentage, 100) }}%"></div>
        </div>
        
        <!-- Points de repère -->
        <div class="flex justify-between text-xs mt-1 text-base-content/50">
            <span>0%</span>
            <span>Seuil: {{ $budget->threshold_percentage }}%</span>
            <span>100%</span>
        </div>
    </div>
    
    <!-- Montants -->
    <div class="grid grid-cols-2 gap-4 mb-4">
        <div class="text-center p-3 bg-base-200 rounded-lg">
            <div class="text-sm text-base-content/70">Budget</div>
            <div class="text-xl font-bold text-success">{{ number_format($budget->amount, 2, ',', ' ') }} FDJ</div>
        </div>
        <div class="text-center p-3 bg-base-200 rounded-lg">
            <div class="text-sm text-base-content/70">Dépensé</div>
            <div class="text-xl font-bold text-error">{{ number_format($budget->spent_amount, 2, ',', ' ') }} FDJ</div>
        </div>
    </div>
    
    <!-- Restant -->
    <div class="text-center p-3 bg-base-200 rounded-lg mb-4">
        <div class="text-sm text-base-content/70">Reste à dépenser</div>
        <div class="text-2xl font-bold 
            @if($budget->remaining_amount > 0) text-success 
            @else text-error @endif">
            {{ number_format($budget->remaining_amount, 2, ',', ' ') }} FDJ
        </div>
    </div>
    
    <!-- Période -->
    <div class="flex justify-between items-center text-sm">
        <div class="text-base-content/70">
            <i class="fas fa-calendar mr-1"></i>
            {{ $budget->start_date->format('d/m/Y') }} - {{ $budget->end_date->format('d/m/Y') }}
        </div>
        <div class="flex items-center">
            @if($budget->notifications_enabled)
                <i class="fas fa-bell text-success mr-1"></i>
                <span class="text-success">Alertes activées</span>
            @else
                <i class="fas fa-bell-slash text-base-content/50 mr-1"></i>
                <span class="text-base-content/50">Alertes désactivées</span>
            @endif
        </div>
    </div>
    
    <!-- Estimation quotidienne -->
    @php
        $today = now();
        $daysLeft = max(0, $budget->end_date->diffInDays($today));
        $dailyBudget = $daysLeft > 0 ? $budget->remaining_amount / $daysLeft : $budget->remaining_amount;
    @endphp
    
    @if($daysLeft > 0 && $budget->remaining_amount > 0)
        <div class="mt-4 pt-4 border-t border-base-300">
            <div class="text-sm text-base-content/70 mb-1">
                <i class="fas fa-clock mr-1"></i>
                {{ $daysLeft }} jour(s) restant(s)
            </div>
            <div class="text-sm">
                Pour rester dans le budget : 
                <span class="font-bold text-success">{{ number_format($dailyBudget, 2, ',', ' ') }} FDJ/jour</span>
            </div>
        </div>
    @endif
    
    <!-- Bouton rapide pour ajouter une transaction -->
    <div class="mt-4">
        <a href="{{ route('transactions.create', ['category_id' => $budget->category_id, 'type' => 'expense']) }}" 
           class="btn btn-sm btn-outline w-full">
            <i class="fas fa-plus mr-1"></i> Ajouter une dépense
        </a>
    </div>
</div>