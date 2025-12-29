<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between">
            <div>
                <h2 class="text-2xl font-bold text-base-content">
                    <i class="fas fa-plus-circle mr-3"></i>Nouvelle transaction
                </h2>
                <div class="breadcrumbs text-sm mt-1">
                    <ul>
                        <li><a href="{{ route('dashboard') }}"><i class="fas fa-home"></i> Dashboard</a></li>
                        <li><a href="{{ route('transactions.index') }}">Transactions</a></li>
                        <li>Nouvelle</li>
                    </ul>
                </div>
            </div>
            <div class="mt-2 md:mt-0">
                <a href="{{ route('transactions.index') }}" class="btn btn-ghost">
                    <i class="fas fa-arrow-left mr-2"></i>Retour
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-2xl mx-auto">
            <!-- Type de transaction -->
            <div class="card bg-base-100 shadow-lg mb-6">
                <div class="card-body">
                    <h3 class="card-title mb-4">Type de transaction</h3>
                    <div class="grid grid-cols-2 gap-4">
                        <button type="button" 
                                id="btn-expense" 
                                class="btn btn-outline btn-error btn-lg {{ old('type', request('type', 'expense')) === 'expense' ? 'btn-active' : '' }}"
                                onclick="setTransactionType('expense')">
                            <i class="fas fa-arrow-down text-2xl mr-3"></i>
                            <div class="text-left">
                                <div class="font-bold">Dépense</div>
                                <div class="text-xs">Argent sortant</div>
                            </div>
                        </button>
                        
                        <button type="button" 
                                id="btn-income" 
                                class="btn btn-outline btn-success btn-lg {{ old('type', request('type')) === 'income' ? 'btn-active' : '' }}"
                                onclick="setTransactionType('income')">
                            <i class="fas fa-arrow-up text-2xl mr-3"></i>
                            <div class="text-left">
                                <div class="font-bold">Revenu</div>
                                <div class="text-xs">Argent entrant</div>
                            </div>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Formulaire -->
            <div class="card bg-base-100 shadow-lg">
                <div class="card-body">
                    <form method="POST" action="{{ route('transactions.store') }}" id="transaction-form">
                        @csrf
                        
                        <!-- Champ caché pour le type -->
                        <input type="hidden" name="type" id="transaction-type" value="{{ old('type', request('type', 'expense')) }}">
                        
                        <!-- Montant -->
                        <div class="form-control mb-4">
                            <label class="label">
                                <span class="label-text font-semibold">Montant *</span>
                            </label>
                            <div class="relative">
                                <span class="absolute left-3 top-1/2 transform -translate-y-1/2 text-2xl font-bold {{ old('type', request('type', 'expense')) === 'expense' ? 'text-error' : 'text-success' }}">
                                    {{ old('type', request('type', 'expense')) === 'expense' ? '-' : '+' }}
                                </span>
                                <input type="number" 
                                       name="amount" 
                                       id="amount"
                                       step="0.01"
                                       min="0.01"
                                       value="{{ old('amount') }}"
                                       placeholder="0.00"
                                       class="input input-bordered w-full pl-10 text-2xl font-bold {{ $errors->has('amount') ? 'input-error' : '' }}"
                                       required
                                       autofocus />
                                <span class="absolute right-3 top-1/2 transform -translate-y-1/2 text-xl">FDJ</span>
                            </div>
                            @error('amount')
                                <label class="label">
                                    <span class="label-text-alt text-error">{{ $message }}</span>
                                </label>
                            @enderror
                        </div>
                        
                        <!-- Catégorie -->
                        <div class="form-control mb-4">
                            <label class="label">
                                <span class="label-text font-semibold">Catégorie *</span>
                                <a href="{{ route('categories.create') }}" class="label-text-alt link link-primary" target="_blank">
                                    <i class="fas fa-plus-circle mr-1"></i>Nouvelle catégorie
                                </a>
                            </label>
                            <select name="category_id" 
                                    id="category-select"
                                    class="select select-bordered w-full {{ $errors->has('category_id') ? 'select-error' : '' }}"
                                    required>
                                <option value="">Sélectionnez une catégorie</option>
                                @foreach($categories as $category)
                                    @if($category->type === old('type', request('type', 'expense')))
                                        <option value="{{ $category->id }}" 
                                                {{ old('category_id') == $category->id ? 'selected' : '' }}
                                                style="color: {{ $category->color }}">
                                            <i class="fas fa-{{ $category->icon }} mr-2"></i>
                                            {{ $category->name }}
                                            @if($category->is_default)
                                                <span class="text-xs opacity-70">(par défaut)</span>
                                            @endif
                                        </option>
                                    @endif
                                @endforeach
                            </select>
                            @error('category_id')
                                <label class="label">
                                    <span class="label-text-alt text-error">{{ $message }}</span>
                                </label>
                            @enderror
                        </div>
                        
                        <!-- Description -->
                        <div class="form-control mb-4">
                            <label class="label">
                                <span class="label-text font-semibold">Description</span>
                                <span class="label-text-alt">Optionnel</span>
                            </label>
                            <input type="text" 
                                   name="description" 
                                   value="{{ old('description') }}"
                                   placeholder="Ex: Courses supermarché, Salaire, Restaurant..."
                                   class="input input-bordered w-full" />
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <!-- Date -->
                            <div class="form-control">
                                <label class="label">
                                    <span class="label-text font-semibold">Date *</span>
                                </label>
                                <input type="date" 
                                       name="date" 
                                       id="date"
                                       value="{{ old('date', date('Y-m-d')) }}"
                                       class="input input-bordered w-full {{ $errors->has('date') ? 'input-error' : '' }}"
                                       required />
                                @error('date')
                                    <label class="label">
                                        <span class="label-text-alt text-error">{{ $message }}</span>
                                    </label>
                                @enderror
                            </div>
                            
                            <!-- Mode de paiement -->
                            <div class="form-control">
                                <label class="label">
                                    <span class="label-text font-semibold">Mode de paiement</span>
                                </label>
                                <select name="payment_method" class="select select-bordered w-full">
                                    <option value="">Sélectionnez</option>
                                    <option value="cash" {{ old('payment_method') == 'cash' ? 'selected' : '' }}>Espèces</option>
                                    <option value="card" {{ old('payment_method') == 'card' ? 'selected' : '' }}>Carte bancaire</option>
                                    <option value="transfer" {{ old('payment_method') == 'transfer' ? 'selected' : '' }}>Virement</option>
                                    <option value="mobile_money" {{ old('payment_method') == 'mobile_money' ? 'selected' : '' }}>Mobile Money</option>
                                </select>
                            </div>
                        </div>
                        
                        <!-- Lieu -->
                        <div class="form-control mb-6">
                            <label class="label">
                                <span class="label-text font-semibold">Lieu</span>
                                <span class="label-text-alt">Optionnel</span>
                            </label>
                            <input type="text" 
                                   name="location" 
                                   value="{{ old('location') }}"
                                   placeholder="Ex: Carrefour, Uber, Entreprise..."
                                   class="input input-bordered w-full" />
                        </div>
                        
                        <!-- Section récurrente (optionnel) -->
                        <div class="collapse collapse-arrow border border-base-300 rounded-box mb-6">
                            <input type="checkbox" name="recurring-section" />
                            <div class="collapse-title font-semibold">
                                <i class="fas fa-repeat mr-2"></i>Transaction récurrente
                            </div>
                            <div class="collapse-content">
                                <div class="space-y-4 pt-4">
                                    <div class="form-control">
                                        <label class="cursor-pointer label justify-start">
                                            <input type="checkbox" 
                                                   name="is_recurring" 
                                                   id="is_recurring"
                                                   class="checkbox checkbox-primary mr-3"
                                                   {{ old('is_recurring') ? 'checked' : '' }} />
                                            <span class="label-text">Cette transaction se répète</span>
                                        </label>
                                    </div>
                                    
                                    <div id="recurring-options" class="{{ old('is_recurring') ? '' : 'hidden' }}">
                                        <div class="form-control">
                                            <label class="label">
                                                <span class="label-text">Fréquence</span>
                                            </label>
                                            <select name="recurring_frequency" class="select select-bordered w-full">
                                                <option value="">Sélectionnez</option>
                                                <option value="daily" {{ old('recurring_frequency') == 'daily' ? 'selected' : '' }}>Quotidienne</option>
                                                <option value="weekly" {{ old('recurring_frequency') == 'weekly' ? 'selected' : '' }}>Hebdomadaire</option>
                                                <option value="monthly" {{ old('recurring_frequency') == 'monthly' ? 'selected' : '' }}>Mensuelle</option>
                                                <option value="yearly" {{ old('recurring_frequency') == 'yearly' ? 'selected' : '' }}>Annuelle</option>
                                            </select>
                                        </div>
                                        
                                        <div class="alert alert-info">
                                            <i class="fas fa-info-circle"></i>
                                            <span>Une notification vous sera envoyée à chaque échéance</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Boutons d'action -->
                        <div class="flex justify-between items-center pt-6 border-t border-base-300">
                            <a href="{{ route('transactions.index') }}" class="btn btn-ghost">
                                <i class="fas fa-times mr-2"></i>Annuler
                            </a>
                            <div class="flex space-x-2">
                                <button type="button" onclick="resetForm()" class="btn btn-outline">
                                    <i class="fas fa-redo mr-2"></i>Réinitialiser
                                </button>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save mr-2"></i>Enregistrer
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Aperçu -->
            <div class="card bg-base-100 shadow-lg mt-6">
                <div class="card-body">
                    <h3 class="card-title mb-4">Aperçu</h3>
                    <div id="transaction-preview" class="space-y-3">
                        <div class="flex justify-between items-center p-4 bg-base-200 rounded-lg">
                            <div class="flex items-center">
                                <div id="preview-icon" class="w-12 h-12 rounded-lg bg-error/20 flex items-center justify-center mr-4">
                                    <i id="preview-icon-symbol" class="fas fa-arrow-down text-error text-xl"></i>
                                </div>
                                <div>
                                    <div id="preview-title" class="font-bold">Nouvelle dépense</div>
                                    <div id="preview-category" class="text-sm opacity-70">Sélectionnez une catégorie</div>
                                </div>
                            </div>
                            <div id="preview-amount" class="text-2xl font-bold text-error">0 FDJ</div>
                        </div>
                        
                        <div class="grid grid-cols-2 gap-4 text-sm">
                            <div>
                                <div class="font-semibold">Date</div>
                                <div id="preview-date">{{ date('d/m/Y') }}</div>
                            </div>
                            <div>
                                <div class="font-semibold">Mode de paiement</div>
                                <div id="preview-method">Non spécifié</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('styles')
    <style>
        .btn-active {
            @apply border-2;
        }
        
        #category-select option {
            padding: 8px;
        }
        
        #amount::-webkit-inner-spin-button,
        #amount::-webkit-outer-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }
        
        #amount {
            -moz-appearance: textfield;
        }
        
        #transaction-preview {
            transition: all 0.3s ease;
        }
    </style>
    @endpush

    @push('scripts')
    <script>
        // Définir le type de transaction
        function setTransactionType(type) {
            document.getElementById('transaction-type').value = type;
            
            // Mettre à jour les boutons
            document.getElementById('btn-expense').classList.toggle('btn-active', type === 'expense');
            document.getElementById('btn-income').classList.toggle('btn-active', type === 'income');
            
            // Mettre à jour la couleur du montant
            const amountInput = document.getElementById('amount');
            const amountSymbol = amountInput.previousElementSibling;
            
            if (type === 'expense') {
                amountSymbol.className = 'absolute left-3 top-1/2 transform -translate-y-1/2 text-2xl font-bold text-error';
                amountSymbol.textContent = '-';
            } else {
                amountSymbol.className = 'absolute left-3 top-1/2 transform -translate-y-1/2 text-2xl font-bold text-success';
                amountSymbol.textContent = '+';
            }
            
            // Filtrer les catégories
            filterCategories(type);
            
            // Mettre à jour l'aperçu
            updatePreview();
        }
        
        // Filtrer les catégories par type
        function filterCategories(type) {
            const select = document.getElementById('category-select');
            const options = select.options;
            
            for (let i = 0; i < options.length; i++) {
                const option = options[i];
                // Afficher toutes les options au début, puis filtrer
                option.style.display = '';
                
                if (option.value !== '') {
                    // Si l'option a un type spécifique (stocké dans data-type)
                    if (option.dataset.type && option.dataset.type !== type) {
                        option.style.display = 'none';
                    }
                }
            }
            
            // Réinitialiser la sélection
            select.value = '';
        }
        
        // Mettre à jour l'aperçu en temps réel
        function updatePreview() {
            const type = document.getElementById('transaction-type').value;
            const amount = document.getElementById('amount').value || '0';
            const categorySelect = document.getElementById('category-select');
            const selectedCategory = categorySelect.options[categorySelect.selectedIndex];
            const dateInput = document.getElementById('date');
            const methodSelect = document.querySelector('select[name="payment_method"]');
            const descriptionInput = document.querySelector('input[name="description"]');
            
            // Mettre à jour le titre
            document.getElementById('preview-title').textContent = 
                type === 'expense' ? 'Dépense' : 'Revenu';
            
            if (descriptionInput.value) {
                document.getElementById('preview-title').textContent = descriptionInput.value;
            }
            
            // Mettre à jour le montant
            const formattedAmount = parseFloat(amount).toLocaleString('fr-FR', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
            
            const amountElement = document.getElementById('preview-amount');
            amountElement.textContent = `${formattedAmount} FDJ`;
            amountElement.className = `text-2xl font-bold ${type === 'expense' ? 'text-error' : 'text-success'}`;
            
            // Mettre à jour la catégorie
            if (selectedCategory && selectedCategory.value !== '') {
                document.getElementById('preview-category').textContent = selectedCategory.text;
                
                // Mettre à jour l'icône et la couleur
                const iconContainer = document.getElementById('preview-icon');
                const iconSymbol = document.getElementById('preview-icon-symbol');
                
                // Extraire la couleur de la catégorie (si disponible)
                const colorMatch = selectedCategory.style.color || (type === 'expense' ? '#EF4444' : '#10B981');
                iconContainer.style.backgroundColor = `${colorMatch}20`;
                iconSymbol.className = `fas ${selectedCategory.dataset.icon || 'fa-receipt'} text-xl`;
                iconSymbol.style.color = colorMatch;
            } else {
                document.getElementById('preview-category').textContent = 'Sélectionnez une catégorie';
            }
            
            // Mettre à jour la date
            if (dateInput.value) {
                const date = new Date(dateInput.value);
                document.getElementById('preview-date').textContent = 
                    date.toLocaleDateString('fr-FR');
            }
            
            // Mettre à jour le mode de paiement
            if (methodSelect.value) {
                const methods = {
                    'cash': 'Espèces',
                    'card': 'Carte bancaire',
                    'transfer': 'Virement',
                    'mobile_money': 'Mobile Money'
                };
                document.getElementById('preview-method').textContent = methods[methodSelect.value] || methodSelect.value;
            } else {
                document.getElementById('preview-method').textContent = 'Non spécifié';
            }
        }
        
        // Gérer la section récurrente
        document.getElementById('is_recurring')?.addEventListener('change', function() {
            const options = document.getElementById('recurring-options');
            options.classList.toggle('hidden', !this.checked);
        });
        
        // Réinitialiser le formulaire
        function resetForm() {
            if (confirm('Voulez-vous vraiment réinitialiser le formulaire ?')) {
                document.getElementById('transaction-form').reset();
                setTransactionType('expense');
                document.getElementById('date').value = new Date().toISOString().split('T')[0];
                updatePreview();
            }
        }
        
        // Écouter les changements pour mettre à jour l'aperçu
        document.addEventListener('DOMContentLoaded', function() {
            // Initialiser
            setTransactionType('{{ old('type', request('type', 'expense')) }}');
            updatePreview();
            
            // Écouter les changements
            const inputs = ['amount', 'date', 'description', 'location'];
            inputs.forEach(id => {
                const element = document.getElementById(id);
                if (element) {
                    element.addEventListener('input', updatePreview);
                }
            });
            
            document.getElementById('category-select')?.addEventListener('change', updatePreview);
            document.querySelector('select[name="payment_method"]')?.addEventListener('change', updatePreview);
            
            // Initialiser la section récurrente
            const recurringCheckbox = document.getElementById('is_recurring');
            if (recurringCheckbox) {
                recurringCheckbox.dispatchEvent(new Event('change'));
            }
            
            // Focus sur le montant
            document.getElementById('amount')?.focus();
        });
    </script>
    @endpush
</x-app-layout>