<div class="card bg-base-100 shadow mb-6">
    <div class="card-body">
        <form method="GET" action="{{ route('transactions.index') }}" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <!-- Recherche -->
                <div>
                    <label class="label">
                        <span class="label-text">Recherche</span>
                    </label>
                    <input type="text" 
                           name="search" 
                           id="search"
                           value="{{ request('search') }}"
                           placeholder="Description ou lieu..."
                           class="input input-bordered w-full" />
                </div>
                
                <!-- Type -->
                <div>
                    <label class="label">
                        <span class="label-text">Type</span>
                    </label>
                    <select name="type" class="select select-bordered w-full">
                        <option value="">Tous</option>
                        <option value="expense" {{ request('type') == 'expense' ? 'selected' : '' }}>Dépenses</option>
                        <option value="income" {{ request('type') == 'income' ? 'selected' : '' }}>Revenus</option>
                    </select>
                </div>
                
                <!-- Catégorie -->
                <div>
                    <label class="label">
                        <span class="label-text">Catégorie</span>
                    </label>
                    <select name="category_id" class="select select-bordered w-full">
                        <option value="">Toutes</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" 
                                    {{ request('category_id') == $category->id ? 'selected' : '' }}>
                                {{ $category->name }} ({{ $category->type === 'expense' ? 'Dépense' : 'Revenu' }})
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <!-- Période -->
                <div>
                    <label class="label">
                        <span class="label-text">Période</span>
                    </label>
                    <select name="period" id="date-filter" class="select select-bordered w-full">
                        <option value="">Toute période</option>
                        <option value="today" {{ request('period') == 'today' ? 'selected' : '' }}>Aujourd'hui</option>
                        <option value="week" {{ request('period') == 'week' ? 'selected' : '' }}>Cette semaine</option>
                        <option value="month" {{ request('period') == 'month' ? 'selected' : '' }}>Ce mois</option>
                        <option value="year" {{ request('period') == 'year' ? 'selected' : '' }}>Cette année</option>
                        <option value="custom" {{ request('start_date') ? 'selected' : '' }}>Personnalisée</option>
                    </select>
                </div>
            </div>
            
            <!-- Période personnalisée (masqué par défaut) -->
            <div id="custom-dates" class="grid grid-cols-1 md:grid-cols-2 gap-4 {{ request('start_date') ? '' : 'hidden' }}">
                <div>
                    <label class="label">
                        <span class="label-text">Date de début</span>
                    </label>
                    <input type="date" 
                           name="start_date" 
                           value="{{ request('start_date') }}"
                           class="input input-bordered w-full" />
                </div>
                <div>
                    <label class="label">
                        <span class="label-text">Date de fin</span>
                    </label>
                    <input type="date" 
                           name="end_date" 
                           value="{{ request('end_date') }}"
                           class="input input-bordered w-full" />
                </div>
            </div>
            
            <!-- Boutons d'action -->
            <div class="flex justify-between items-center pt-4 border-t border-base-300">
                <div class="text-sm text-base-content/70">
                    {{ $transactions->total() }} transactions trouvées
                </div>
                <div class="flex space-x-2">
                    <a href="{{ route('transactions.index') }}" class="btn btn-ghost">
                        <i class="fas fa-times mr-2"></i>Réinitialiser
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-filter mr-2"></i>Filtrer
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    // Afficher/masquer les dates personnalisées
    document.getElementById('date-filter')?.addEventListener('change', function() {
        const customDates = document.getElementById('custom-dates');
        if (this.value === 'custom') {
            customDates.classList.remove('hidden');
        } else {
            customDates.classList.add('hidden');
        }
        
        // Définir les dates automatiquement
        const today = new Date();
        let startDate = '';
        let endDate = today.toISOString().split('T')[0];
        
        switch(this.value) {
            case 'today':
                startDate = endDate;
                break;
            case 'week':
                startDate = new Date(today.setDate(today.getDate() - 7)).toISOString().split('T')[0];
                break;
            case 'month':
                startDate = new Date(today.setMonth(today.getMonth() - 1)).toISOString().split('T')[0];
                break;
            case 'year':
                startDate = new Date(today.setFullYear(today.getFullYear() - 1)).toISOString().split('T')[0];
                break;
        }
        
        if (startDate && this.value !== 'custom') {
            document.querySelector('input[name="start_date"]').value = startDate;
            document.querySelector('input[name="end_date"]').value = endDate;
            this.form.submit();
        }
    });
</script>
@endpush