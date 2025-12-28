@php
    // Calculer l'utilisation de la catégorie
    $usageCount = $category->transactions()->count();
    $usagePercent = min(100, ($usageCount / max(1, $category->user->transactions()->count())) * 100);
@endphp

<div class="category-card border border-base-300 rounded-lg p-4 hover:shadow-lg transition-all">
    <!-- En-tête de la carte -->
    <div class="flex justify-between items-start mb-3">
        <div class="flex items-center">
            <div class="w-12 h-12 rounded-lg flex items-center justify-center mr-3" 
                 style="background-color: {{ $category->color }}20">
                <i class="fas fa-{{ $category->icon }} text-xl" style="color: {{ $category->color }}"></i>
            </div>
            <div>
                <div class="font-bold text-lg">{{ $category->name }}</div>
                <div class="text-sm {{ $category->type === 'expense' ? 'text-error' : 'text-success' }}">
                    {{ $category->type === 'expense' ? 'Dépense' : 'Revenu' }}
                </div>
            </div>
        </div>
        
        <!-- Actions -->
        @if(!$category->is_default)
            <div class="dropdown dropdown-end">
                <button class="btn btn-ghost btn-xs">
                    <i class="fas fa-ellipsis-v"></i>
                </button>
                <ul tabindex="0" class="dropdown-content z-[1] menu p-2 shadow bg-base-100 rounded-box w-40">
                    <li>
                        <a href="{{ route('categories.edit', $category) }}">
                            <i class="fas fa-edit"></i> Modifier
                        </a>
                    </li>
                    <li>
                        <button onclick="document.getElementById('delete-modal-{{ $category->id }}').showModal()">
                            <i class="fas fa-trash text-error"></i> Supprimer
                        </button>
                    </li>
                </ul>
            </div>
            
            <!-- Modal de suppression -->
            <dialog id="delete-modal-{{ $category->id }}" class="modal">
                <div class="modal-box">
                    <h3 class="font-bold text-lg">Confirmer la suppression</h3>
                    <p class="py-4">
                        Êtes-vous sûr de vouloir supprimer la catégorie <strong>"{{ $category->name }}"</strong> ?
                    </p>
                    
                    @if($usageCount > 0)
                        <div class="alert alert-warning mb-4">
                            <i class="fas fa-exclamation-triangle"></i>
                            <span>
                                Cette catégorie est utilisée dans <strong>{{ $usageCount }} transactions</strong>.
                                La suppression réaffectera ces transactions à "Autres".
                            </span>
                        </div>
                    @endif
                    
                    <div class="modal-action">
                        <form method="dialog">
                            <button class="btn">Annuler</button>
                        </form>
                        <form action="{{ route('categories.destroy', $category) }}" method="POST">
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
        @else
            <span class="badge badge-sm">
                <i class="fas fa-lock mr-1"></i> Défaut
            </span>
        @endif
    </div>
    
    <!-- Statistiques d'utilisation -->
    <div class="mb-4">
        <div class="flex justify-between text-sm mb-1">
            <span class="text-base-content/70">Utilisation</span>
            <span class="font-medium">{{ $usageCount }} transactions</span>
        </div>
        <div class="w-full bg-base-300 rounded-full h-2">
            <div class="usage-bar rounded-full" 
                 style="width: {{ $usagePercent }}%; background-color: {{ $category->color }}"></div>
        </div>
    </div>
    
    <!-- Dépenses/Revenus totaux -->
    @php
        $totalAmount = $category->transactions()->sum('amount');
        $monthlyAmount = $category->transactions()
            ->whereMonth('date', now()->month)
            ->whereYear('date', now()->year)
            ->sum('amount');
    @endphp
    
    <div class="grid grid-cols-2 gap-2 text-sm">
        <div>
            <div class="text-base-content/70">Total</div>
            <div class="font-bold {{ $category->type === 'expense' ? 'text-error' : 'text-success' }}">
                {{ number_format($totalAmount, 2, ',', ' ') }} FDJ
            </div>
        </div>
        <div>
            <div class="text-base-content/70">Ce mois</div>
            <div class="font-bold">
                {{ number_format($monthlyAmount, 2, ',', ' ') }} FDJ
            </div>
        </div>
    </div>
    
    <!-- Dernière transaction -->
    @php
        $lastTransaction = $category->transactions()->latest()->first();
    @endphp
    
    @if($lastTransaction)
        <div class="mt-4 pt-4 border-t border-base-300">
            <div class="text-xs text-base-content/50 mb-1">Dernière transaction</div>
            <div class="flex justify-between items-center">
                <div class="truncate">
                    {{ $lastTransaction->description ?: 'Sans description' }}
                </div>
                <div class="text-xs whitespace-nowrap">
                    {{ $lastTransaction->date->format('d/m') }}
                </div>
            </div>
        </div>
    @endif
    
    <!-- Bouton rapide pour ajouter une transaction -->
    <div class="mt-4">
        <a href="{{ route('transactions.create', ['category_id' => $category->id, 'type' => $category->type]) }}" 
           class="btn btn-xs btn-outline w-full">
            <i class="fas fa-plus mr-1"></i> Ajouter une transaction
        </a>
    </div>
</div>