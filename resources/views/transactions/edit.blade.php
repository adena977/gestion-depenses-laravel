<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between">
            <div>
                <h2 class="text-2xl font-bold text-base-content">
                    <i class="fas fa-edit mr-3"></i>Modifier la transaction
                </h2>
                <div class="breadcrumbs text-sm mt-1">
                    <ul>
                        <li><a href="{{ route('dashboard') }}"><i class="fas fa-home"></i> Dashboard</a></li>
                        <li><a href="{{ route('transactions.index') }}">Transactions</a></li>
                        <li>Modifier</li>
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
            <!-- Information sur la transaction -->
            <div class="card bg-base-100 shadow-lg mb-6">
                <div class="card-body">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="card-title">Transaction actuelle</h3>
                        <span class="badge {{ $transaction->type === 'expense' ? 'badge-error' : 'badge-success' }} text-sm">
                            {{ $transaction->type === 'expense' ? 'Dépense' : 'Revenu' }}
                        </span>
                    </div>
                    
                    <div class="flex items-center justify-between p-4 bg-base-200 rounded-lg">
                        <div class="flex items-center">
                            <div class="w-12 h-12 rounded-lg flex items-center justify-center mr-4" 
                                 style="background-color: {{ $transaction->category->color }}20">
                                <i class="fas fa-{{ $transaction->category->icon }} text-xl" 
                                   style="color: {{ $transaction->category->color }}"></i>
                            </div>
                            <div>
                                <div class="font-bold">{{ $transaction->description ?: 'Sans description' }}</div>
                                <div class="text-sm opacity-70">
                                    <i class="fas fa-{{ $transaction->category->icon }} mr-1"></i>
                                    {{ $transaction->category->name }}
                                </div>
                                @if($transaction->payment_method)
                                    <div class="text-xs opacity-70 capitalize">
                                        {{ $transaction->payment_method }}
                                    </div>
                                @endif
                            </div>
                        </div>
                        <div class="text-right">
                            <div class="text-2xl font-bold {{ $transaction->type === 'expense' ? 'text-error' : 'text-success' }}">
                                {{ $transaction->type === 'expense' ? '-' : '+' }}{{ number_format($transaction->amount, 2, ',', ' ') }} FDJ
                            </div>
                            <div class="text-sm opacity-70">{{ $transaction->date->format('d/m/Y') }}</div>
                        </div>
                    </div>
                    
                    <!-- Informations supplémentaires -->
                    @if($transaction->location || $transaction->is_recurring)
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4 text-sm">
                            @if($transaction->location)
                                <div class="flex items-center">
                                    <i class="fas fa-map-marker-alt text-base-content/50 mr-2"></i>
                                    <span>{{ $transaction->location }}</span>
                                </div>
                            @endif
                            
                            @if($transaction->is_recurring)
                                <div class="flex items-center">
                                    <i class="fas fa-repeat text-base-content/50 mr-2"></i>
                                    <span>Récurrente ({{ $transaction->recurring_frequency }})</span>
                                </div>
                            @endif
                        </div>
                    @endif
                </div>
            </div>

            <!-- Formulaire de modification -->
            <div class="card bg-base-100 shadow-lg">
                <div class="card-body">
                    <form method="POST" action="{{ route('transactions.update', $transaction) }}">
                        @csrf
                        @method('PUT')
                        
                        <!-- Type (affichage seulement, non modifiable) -->
                        <div class="form-control mb-6">
                            <label class="label">
                                <span class="label-text font-semibold">Type de transaction</span>
                            </label>
                            <div class="flex items-center p-3 bg-base-200 rounded-lg">
                                <div class="w-10 h-10 rounded-full {{ $transaction->type === 'expense' ? 'bg-error/20' : 'bg-success/20' }} flex items-center justify-center mr-3">
                                    <i class="fas fa-{{ $transaction->type === 'expense' ? 'arrow-down' : 'arrow-up' }} {{ $transaction->type === 'expense' ? 'text-error' : 'text-success' }}"></i>
                                </div>
                                <div>
                                    <div class="font-bold">{{ $transaction->type === 'expense' ? 'Dépense' : 'Revenu' }}</div>
                                    <div class="text-sm opacity-70">
                                        Le type de transaction ne peut pas être modifié
                                    </div>
                                </div>
                            </div>
                            <input type="hidden" name="type" value="{{ $transaction->type }}">
                        </div>
                        
                        <!-- Montant -->
                        <div class="form-control mb-4">
                            <label class="label">
                                <span class="label-text font-semibold">Montant *</span>
                            </label>
                            <div class="relative">
                                <span class="absolute left-3 top-1/2 transform -translate-y-1/2 text-2xl font-bold {{ $transaction->type === 'expense' ? 'text-error' : 'text-success' }}">
                                    {{ $transaction->type === 'expense' ? '-' : '+' }}
                                </span>
                                <input type="number" 
                                       name="amount" 
                                       id="amount"
                                       step="0.01"
                                       min="0.01"
                                       value="{{ old('amount', $transaction->amount) }}"
                                       placeholder="0.00"
                                       class="input input-bordered w-full pl-10 text-2xl font-bold @error('amount') input-error @enderror"
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
                                    class="select select-bordered w-full @error('category_id') select-error @enderror"
                                    required>
                                <option value="">Sélectionnez une catégorie</option>
                                @foreach($categories as $category)
                                    @if($category->type === $transaction->type)
                                        <option value="{{ $category->id }}" 
                                                {{ old('category_id', $transaction->category_id) == $category->id ? 'selected' : '' }}
                                                data-icon="{{ $category->icon }}"
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
                                   id="description"
                                   value="{{ old('description', $transaction->description) }}"
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
                                       value="{{ old('date', $transaction->date->format('Y-m-d')) }}"
                                       class="input input-bordered w-full @error('date') input-error @enderror"
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
                                <select name="payment_method" id="payment_method" class="select select-bordered w-full">
                                    <option value="">Sélectionnez</option>
                                    <option value="cash" {{ old('payment_method', $transaction->payment_method) == 'cash' ? 'selected' : '' }}>Espèces</option>
                                    <option value="card" {{ old('payment_method', $transaction->payment_method) == 'card' ? 'selected' : '' }}>Carte bancaire</option>
                                    <option value="transfer" {{ old('payment_method', $transaction->payment_method) == 'transfer' ? 'selected' : '' }}>Virement</option>
                                    <option value="mobile_money" {{ old('payment_method', $transaction->payment_method) == 'mobile_money' ? 'selected' : '' }}>Mobile Money</option>
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
                                   id="location"
                                   value="{{ old('location', $transaction->location) }}"
                                   placeholder="Ex: Carrefour, Uber, Entreprise..."
                                   class="input input-bordered w-full" />
                        </div>
                        
                        <!-- Section récurrente -->
                        <div class="collapse collapse-arrow border border-base-300 rounded-box mb-6">
                            <input type="checkbox" {{ old('is_recurring', $transaction->is_recurring) ? 'checked' : '' }} />
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
                                                   {{ old('is_recurring', $transaction->is_recurring) ? 'checked' : '' }} />
                                            <span class="label-text">Cette transaction se répète</span>
                                        </label>
                                    </div>
                                    
                                    <div id="recurring-options" class="{{ old('is_recurring', $transaction->is_recurring) ? '' : 'hidden' }}">
                                        <div class="form-control">
                                            <label class="label">
                                                <span class="label-text">Fréquence</span>
                                            </label>
                                            <select name="recurring_frequency" class="select select-bordered w-full">
                                                <option value="">Sélectionnez</option>
                                                <option value="daily" {{ old('recurring_frequency', $transaction->recurring_frequency) == 'daily' ? 'selected' : '' }}>Quotidienne</option>
                                                <option value="weekly" {{ old('recurring_frequency', $transaction->recurring_frequency) == 'weekly' ? 'selected' : '' }}>Hebdomadaire</option>
                                                <option value="monthly" {{ old('recurring_frequency', $transaction->recurring_frequency) == 'monthly' ? 'selected' : '' }}>Mensuelle</option>
                                                <option value="yearly" {{ old('recurring_frequency', $transaction->recurring_frequency) == 'yearly' ? 'selected' : '' }}>Annuelle</option>
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
                            <div class="flex space-x-2">
                                <a href="{{ route('transactions.index') }}" class="btn btn-ghost">
                                    <i class="fas fa-times mr-2"></i>Annuler
                                </a>
                                <button type="button" 
                                        onclick="document.getElementById('delete-modal').showModal()" 
                                        class="btn btn-outline btn-error">
                                    <i class="fas fa-trash mr-2"></i>Supprimer
                                </button>
                            </div>
                            <div class="flex space-x-2">
                                <button type="button" onclick="resetForm()" class="btn btn-outline">
                                    <i class="fas fa-redo mr-2"></i>Réinitialiser
                                </button>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save mr-2"></i>Mettre à jour
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Aperçu -->
            <div class="card bg-base-100 shadow-lg mt-6">
                <div class="card-body">
                    <h3 class="card-title mb-4">Aperçu des modifications</h3>
                    <div id="transaction-preview" class="space-y-3">
                        <div class="flex justify-between items-center p-4 bg-base-200 rounded-lg">
                            <div class="flex items-center">
                                <div id="preview-icon" class="w-12 h-12 rounded-lg flex items-center justify-center mr-4" 
                                     style="background-color: {{ $transaction->category->color }}20">
                                    <i id="preview-icon-symbol" class="fas fa-{{ $transaction->category->icon }} text-xl" 
                                       style="color: {{ $transaction->category->color }}"></i>
                                </div>
                                <div>
                                    <div id="preview-title" class="font-bold">
                                        {{ $transaction->description ?: 'Sans description' }}
                                    </div>
                                    <div id="preview-category" class="text-sm opacity-70">
                                        <i class="fas fa-{{ $transaction->category->icon }} mr-1"></i>
                                        {{ $transaction->category->name }}
                                    </div>
                                </div>
                            </div>
                            <div id="preview-amount" class="text-2xl font-bold {{ $transaction->type === 'expense' ? 'text-error' : 'text-success' }}">
                                {{ $transaction->type === 'expense' ? '-' : '+' }}{{ number_format($transaction->amount, 2, ',', ' ') }} €
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-2 gap-4 text-sm">
                            <div>
                                <div class="font-semibold">Date</div>
                                <div id="preview-date">{{ $transaction->date->format('d/m/Y') }}</div>
                            </div>
                            <div>
                                <div class="font-semibold">Mode de paiement</div>
                                <div id="preview-method">
                                    @if($transaction->payment_method)
                                        @php
                                            $methods = [
                                                'cash' => 'Espèces',
                                                'card' => 'Carte bancaire',
                                                'transfer' => 'Virement',
                                                'mobile_money' => 'Mobile Money'
                                            ];
                                        @endphp
                                        {{ $methods[$transaction->payment_method] ?? $transaction->payment_method }}
                                    @else
                                        Non spécifié
                                    @endif
                                </div>
                            </div>
                        </div>
                        
                        @if($transaction->location)
                            <div class="text-sm">
                                <div class="font-semibold">Lieu</div>
                                <div id="preview-location">{{ $transaction->location }}</div>
                            </div>
                        @endif
                        
                        @if($transaction->is_recurring)
                            <div class="text-sm">
                                <div class="font-semibold">Récurrence</div>
                                <div id="preview-recurring" class="flex items-center">
                                    <i class="fas fa-repeat mr-2 text-base-content/50"></i>
                                    {{ $transaction->recurring_frequency }}
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de suppression -->
    <dialog id="delete-modal" class="modal">
        <div class="modal-box">
            <h3 class="font-bold text-lg">Confirmer la suppression</h3>
            <p class="py-4">
                Êtes-vous sûr de vouloir supprimer cette transaction ?<br>
                <strong>{{ $transaction->description ?: 'Transaction sans description' }}</strong><br>
                <span class="text-error">{{ number_format($transaction->amount, 2, ',', ' ') }} €</span> - {{ $transaction->date->format('d/m/Y') }}
            </p>
            <div class="modal-action">
                <form method="dialog">
                    <button class="btn">Annuler</button>
                </form>
                <form action="{{ route('transactions.destroy', $transaction) }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-error">
                        <i class="fas fa-trash mr-2"></i>Supprimer définitivement
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
        // Mettre à jour l'aperçu en temps réel
        function updatePreview() {
            const type = '{{ $transaction->type }}';
            const amount = document.getElementById('amount').value || '{{ $transaction->amount }}';
            const categorySelect = document.getElementById('category-select');
            const selectedCategory = categorySelect.options[categorySelect.selectedIndex];
            const dateInput = document.getElementById('date');
            const descriptionInput = document.getElementById('description');
            const methodSelect = document.getElementById('payment_method');
            const locationInput = document.getElementById('location');
            
            // Mettre à jour le titre
            if (descriptionInput.value) {
                document.getElementById('preview-title').textContent = descriptionInput.value;
            } else {
                document.getElementById('preview-title').textContent = 'Sans description';
            }
            
            // Mettre à jour le montant
            const formattedAmount = parseFloat(amount).toLocaleString('fr-FR', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
            
            const amountElement = document.getElementById('preview-amount');
            amountElement.textContent = `${type === 'expense' ? '-' : '+'}${formattedAmount} €`;
            amountElement.className = `text-2xl font-bold ${type === 'expense' ? 'text-error' : 'text-success'}`;
            
            // Mettre à jour la catégorie
            if (selectedCategory && selectedCategory.value !== '') {
                document.getElementById('preview-category').innerHTML = `
                    <i class="fas fa-${selectedCategory.dataset.icon} mr-1"></i>
                    ${selectedCategory.text.split('<')[0].trim()}
                `;
                
                // Mettre à jour l'icône et la couleur
                const iconContainer = document.getElementById('preview-icon');
                const iconSymbol = document.getElementById('preview-icon-symbol');
                
                const color = selectedCategory.style.color || (type === 'expense' ? '#EF4444' : '#10B981');
                iconContainer.style.backgroundColor = `${color}20`;
                iconSymbol.className = `fas ${selectedCategory.dataset.icon || 'fa-receipt'} text-xl`;
                iconSymbol.style.color = color;
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
            
            // Mettre à jour le lieu
            if (locationInput && locationInput.value) {
                let locationElement = document.getElementById('preview-location');
                let locationContainer = document.getElementById('preview-location')?.parentElement;
                
                if (!locationElement) {
                    locationContainer = document.createElement('div');
                    locationContainer.className = 'text-sm';
                    locationContainer.innerHTML = `
                        <div class="font-semibold">Lieu</div>
                        <div id="preview-location"></div>
                    `;
                    document.getElementById('transaction-preview').appendChild(locationContainer);
                    locationElement = document.getElementById('preview-location');
                }
                
                locationElement.textContent = locationInput.value;
            } else if (document.getElementById('preview-location')?.parentElement) {
                document.getElementById('preview-location').parentElement.remove();
            }
        }
        
        // Gérer la section récurrente
        document.getElementById('is_recurring')?.addEventListener('change', function() {
            const options = document.getElementById('recurring-options');
            options.classList.toggle('hidden', !this.checked);
        });
        
        // Réinitialiser le formulaire
        function resetForm() {
            if (confirm('Voulez-vous vraiment réinitialiser le formulaire ? Les modifications seront perdues.')) {
                // Réinitialiser les valeurs originales
                document.getElementById('amount').value = '{{ $transaction->amount }}';
                document.getElementById('category-select').value = '{{ $transaction->category_id }}';
                document.getElementById('description').value = '{{ $transaction->description }}';
                document.getElementById('date').value = '{{ $transaction->date->format("Y-m-d") }}';
                document.getElementById('payment_method').value = '{{ $transaction->payment_method }}';
                document.getElementById('location').value = '{{ $transaction->location }}';
                document.getElementById('is_recurring').checked = {{ $transaction->is_recurring ? 'true' : 'false' }};
                document.querySelector('select[name="recurring_frequency"]').value = '{{ $transaction->recurring_frequency }}';
                
                // Mettre à jour l'aperçu
                updatePreview();
                
                // Mettre à jour la section récurrente
                const recurringCheckbox = document.getElementById('is_recurring');
                if (recurringCheckbox) {
                    recurringCheckbox.dispatchEvent(new Event('change'));
                }
            }
        }
        
        // Initialiser
        document.addEventListener('DOMContentLoaded', function() {
            // Mettre à jour l'aperçu initial
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
            document.getElementById('payment_method')?.addEventListener('change', updatePreview);
            
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