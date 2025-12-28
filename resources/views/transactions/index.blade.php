<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between">
            <h2 class="text-2xl font-bold text-base-content">
                <i class="fas fa-exchange-alt mr-3"></i>Transactions
            </h2>
            <div class="mt-2 md:mt-0">
                <a href="{{ route('transactions.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus mr-2"></i>Nouvelle transaction
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <!-- Filtres -->
        @include('transactions.partials.filters')

        <!-- Stats rapides -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <div class="stat bg-base-100 p-4 rounded-lg shadow">
                <div class="stat-title">Total dépenses</div>
<div class="stat-value text-error text-xl">{{ number_format($totalExpenses, 0, ',', ' ') }} FDJ</div>
                <div class="stat-desc">Période sélectionnée</div>
            </div>
            
            <div class="stat bg-base-100 p-4 rounded-lg shadow">
                <div class="stat-title">Total revenus</div>
<div class="stat-value text-success text-xl">{{ number_format($totalIncome, 0, ',', ' ') }} FDJ</div>
                <div class="stat-desc">Période sélectionnée</div>
            </div>
            
            <div class="stat bg-base-100 p-4 rounded-lg shadow">
                <div class="stat-title">Solde</div>
              <div class="stat-value {{ $balance >= 0 ? 'text-success' : 'text-error' }} text-xl">
    {{ number_format($balance, 0, ',', ' ') }} FDJ
</div>
                <div class="stat-desc">{{ $transactions->count() }} transactions</div>
            </div>
        </div>

        <!-- Tableau des transactions -->
        <div class="card bg-base-100 shadow-lg">
            <div class="card-body">
                @if($transactions->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="table table-zebra">
                            <thead>
                                <tr>
                                    <th class="w-20">Date</th>
                                    <th>Description</th>
                                    <th class="w-32">Catégorie</th>
                                    <th class="w-32">Montant</th>
                                    <th class="w-24">Type</th>
                                    <th class="w-28">Actions</th>
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
                                            <div class="flex items-center">
                                                <div class="w-10 h-10 rounded-lg flex items-center justify-center mr-3" 
                                                     style="background-color: {{ $transaction->category->color }}20">
                                                    <i class="fas fa-{{ $transaction->category->icon }} text-lg" 
                                                       style="color: {{ $transaction->category->color }}"></i>
                                                </div>
                                                <div>
                                                    <div class="font-medium">
                                                        {{ $transaction->description ?: 'Sans description' }}
                                                    </div>
                                                    @if($transaction->payment_method)
                                                        <div class="text-xs opacity-70 capitalize">
                                                            {{ $transaction->payment_method }}
                                                        </div>
                                                    @endif
                                                    @if($transaction->location)
                                                        <div class="text-xs opacity-70">
                                                            <i class="fas fa-map-marker-alt mr-1"></i>{{ $transaction->location }}
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge badge-outline px-3 py-2" 
                                                  style="border-color: {{ $transaction->category->color }}; color: {{ $transaction->category->color }}">
                                                <i class="fas fa-{{ $transaction->category->icon }} mr-2"></i>
                                                {{ $transaction->category->name }}
                                            </span>
                                        </td>
                                        <td>
                                           <div class="font-bold text-lg {{ $transaction->type === 'expense' ? 'text-error' : 'text-success' }}">
    {{ $transaction->type === 'expense' ? '-' : '+' }}{{ number_format($transaction->amount, 0, ',', ' ') }} FDJ
</div>
                                            @if($transaction->is_recurring)
                                                <span class="badge badge-info badge-xs">
                                                    <i class="fas fa-repeat mr-1"></i>{{ $transaction->recurring_frequency }}
                                                </span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge {{ $transaction->type === 'expense' ? 'badge-error' : 'badge-success' }}">
                                                {{ $transaction->type === 'expense' ? 'Dépense' : 'Revenu' }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="flex space-x-2">
                                                <a href="{{ route('transactions.edit', $transaction) }}" 
                                                   class="btn btn-ghost btn-sm" 
                                                   title="Modifier">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                
                                                <button onclick="document.getElementById('delete-modal-{{ $transaction->id }}').showModal()" 
                                                        class="btn btn-ghost btn-sm text-error" 
                                                        title="Supprimer">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                                
                                                <!-- Modal de suppression -->
                                                <dialog id="delete-modal-{{ $transaction->id }}" class="modal">
                                                    <div class="modal-box">
                                                        <h3 class="font-bold text-lg">Confirmer la suppression</h3>
                                                        <p class="py-4">
                                                            Êtes-vous sûr de vouloir supprimer cette transaction ?<br>
                                                            <strong>{{ $transaction->description ?: 'Transaction sans description' }}</strong><br>
                                                            <span class="text-error">{{ number_format($transaction->amount, 2, ',', ' ') }} FDJ</span> - {{ $transaction->date->format('d/m/Y') }}
                                                        </p>
                                                        <div class="modal-action">
                                                            <form method="dialog">
                                                                <button class="btn">Annuler</button>
                                                            </form>
                                                            <form action="{{ route('transactions.destroy', $transaction) }}" method="POST">
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
                    <!-- État vide -->
                    <div class="text-center py-12">
                        <div class="inline-block p-6 rounded-full bg-base-200 mb-4">
                            <i class="fas fa-exchange-alt text-4xl text-base-content/30"></i>
                        </div>
                        <h3 class="text-xl font-semibold text-base-content/70 mb-2">Aucune transaction</h3>
                        <p class="text-base-content/50 mb-6">
                            Commencez par enregistrer vos premières dépenses ou revenus.
                        </p>
                        <a href="{{ route('transactions.create') }}" class="btn btn-primary btn-lg">
                            <i class="fas fa-plus mr-2"></i>Ajouter une transaction
                        </a>
                    </div>
                @endif
            </div>
        </div>

        <!-- Actions rapides -->
        <div class="mt-6 grid grid-cols-1 md:grid-cols-3 gap-4">
            <a href="{{ route('transactions.create', ['type' => 'expense']) }}" 
               class="btn btn-outline btn-error">
                <i class="fas fa-arrow-down mr-2"></i>Nouvelle dépense
            </a>
            
            <a href="{{ route('transactions.create', ['type' => 'income']) }}" 
               class="btn btn-outline btn-success">
                <i class="fas fa-arrow-up mr-2"></i>Nouveau revenu
            </a>
            
            <a href="{{ route('reports.index') }}" 
               class="btn btn-outline btn-primary">
                <i class="fas fa-chart-bar mr-2"></i>Voir les rapports
            </a>
        </div>
    </div>

    @push('styles')
    <style>
        .table-zebra tbody tr:nth-child(even) {
            background-color: hsl(var(--b2));
        }
        
        .table-zebra tbody tr:hover {
            background-color: hsl(var(--b3));
        }
        
        .stat {
            transition: transform 0.2s ease;
        }
        
        .stat:hover {
            transform: translateY(-2px);
        }
    </style>
    @endpush

    @push('scripts')
    <script>
        // Fonction pour exporter les transactions
        function exportTransactions(format) {
            let url = new URL(window.location.href);
            url.searchParams.set('export', format);
            window.open(url.toString(), '_blank');
        }
        
        // Filtrer par date
        document.getElementById('date-filter')?.addEventListener('change', function() {
            this.form.submit();
        });
        
        // Recherche en temps réel
        let searchTimeout;
        document.getElementById('search')?.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                this.form.submit();
            }, 500);
        });
    </script>
    @endpush
</x-app-layout>