<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between">
            <div>
                <h2 class="text-2xl font-bold text-base-content">
                    <i class="fas fa-plus-circle mr-3"></i>Nouveau budget
                </h2>
                <div class="breadcrumbs text-sm mt-1">
                    <ul>
                        <li><a href="{{ route('dashboard') }}"><i class="fas fa-home"></i> Dashboard</a></li>
                        <li><a href="{{ route('budgets.index') }}">Budgets</a></li>
                        <li>Nouveau</li>
                    </ul>
                </div>
            </div>
            <div class="mt-2 md:mt-0">
                <a href="{{ route('budgets.index') }}" class="btn btn-ghost">
                    <i class="fas fa-arrow-left mr-2"></i>Retour
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-2xl mx-auto">
            <!-- Formulaire -->
            <div class="card bg-base-100 shadow-lg">
                <div class="card-body">
                    <form method="POST" action="{{ route('budgets.store') }}" id="budget-form">
                        @csrf
                        
                        <!-- Catégorie -->
                        <div class="form-control mb-6">
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
                                    <option value="{{ $category->id }}" 
                                            {{ old('category_id', $category_id ?? '') == $category->id ? 'selected' : '' }}
                                            data-color="{{ $category->color }}"
                                            data-icon="{{ $category->icon }}">
                                        <div class="flex items-center">
                                            <div class="w-6 h-6 rounded mr-2" style="background-color: {{ $category->color }}"></div>
                                            <span>
                                                {{ $category->name }}
                                                @if($category->is_default)
                                                    <span class="text-xs opacity-70">(par défaut)</span>
                                                @endif
                                            </span>
                                        </div>
                                    </option>
                                @endforeach
                            </select>
                            @error('category_id')
                                <label class="label">
                                    <span class="label-text-alt text-error">{{ $message }}</span>
                                </label>
                            @enderror
                        </div>
                        
                        <!-- Montant -->
                        <div class="form-control mb-6">
                            <label class="label">
                                <span class="label-text font-semibold">Montant du budget *</span>
                            </label>
                            <div class="relative">
                                <input type="number" 
                                       name="amount" 
                                       id="amount"
                                       step="0.01"
                                       min="0.01"
                                       value="{{ old('amount') }}"
                                       placeholder="0.00"
                                       class="input input-bordered w-full text-2xl font-bold pl-10 @error('amount') input-error @enderror"
                                       required
                                       autofocus />
                                <span class="absolute left-3 top-1/2 transform -translate-y-1/2 text-2xl font-bold">FDJ</span>
                            </div>
                            @error('amount')
                                <label class="label">
                                    <span class="label-text-alt text-error">{{ $message }}</span>
                                </label>
                            @enderror
                        </div>
                        
                        <!-- Période -->
                        <div class="form-control mb-6">
                            <label class="label">
                                <span class="label-text font-semibold">Période *</span>
                            </label>
                            <div class="grid grid-cols-3 gap-4">
                                <label class="cursor-pointer">
                                    <input type="radio" 
                                           name="period" 
                                           value="monthly" 
                                           class="radio radio-primary" 
                                           {{ old('period', 'monthly') === 'monthly' ? 'checked' : '' }} />
                                    <div class="ml-2 text-center">
                                        <div class="font-medium">Mensuel</div>
                                        <div class="text-xs opacity-70">30 jours</div>
                                    </div>
                                </label>
                                <label class="cursor-pointer">
                                    <input type="radio" 
                                           name="period" 
                                           value="weekly" 
                                           class="radio radio-primary" 
                                           {{ old('period') === 'weekly' ? 'checked' : '' }} />
                                    <div class="ml-2 text-center">
                                        <div class="font-medium">Hebdomadaire</div>
                                        <div class="text-xs opacity-70">7 jours</div>
                                    </div>
                                </label>
                                <label class="cursor-pointer">
                                    <input type="radio" 
                                           name="period" 
                                           value="yearly" 
                                           class="radio radio-primary" 
                                           {{ old('period') === 'yearly' ? 'checked' : '' }} />
                                    <div class="ml-2 text-center">
                                        <div class="font-medium">Annuel</div>
                                        <div class="text-xs opacity-70">365 jours</div>
                                    </div>
                                </label>
                            </div>
                            @error('period')
                                <label class="label">
                                    <span class="label-text-alt text-error">{{ $message }}</span>
                                </label>
                            @enderror
                        </div>
                        
                        <!-- Dates -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                            <!-- Date de début -->
                            <div class="form-control">
                                <label class="label">
                                    <span class="label-text font-semibold">Date de début *</span>
                                </label>
                                <input type="date" 
                                       name="start_date" 
                                       id="start_date"
                                       value="{{ old('start_date', date('Y-m-d')) }}"
                                       class="input input-bordered w-full @error('start_date') input-error @enderror"
                                       required />
                                @error('start_date')
                                    <label class="label">
                                        <span class="label-text-alt text-error">{{ $message }}</span>
                                    </label>
                                @enderror
                            </div>
                            
                            <!-- Date de fin (optionnelle) -->
                            <div class="form-control">
                                <label class="label">
                                    <span class="label-text font-semibold">Date de fin</span>
                                    <span class="label-text-alt">Optionnel</span>
                                </label>
                                <input type="date" 
                                       name="end_date" 
                                       id="end_date"
                                       value="{{ old('end_date') }}"
                                       class="input input-bordered w-full @error('end_date') input-error @enderror" />
                                <div class="text-xs text-base-content/50 mt-1">
                                    Calculée automatiquement selon la période si vide
                                </div>
                                @error('end_date')
                                    <label class="label">
                                        <span class="label-text-alt text-error">{{ $message }}</span>
                                    </label>
                                @enderror
                            </div>
                        </div>
                        
                        <!-- Seuil d'alerte -->
                        <div class="form-control mb-8">
                            <label class="label">
                                <span class="label-text font-semibold">Seuil d'alerte</span>
                                <span class="label-text-alt">Optionnel</span>
                            </label>
                            <div class="space-y-4">
                                <div class="flex items-center space-x-4">
                                    <input type="range" 
                                           name="threshold_percentage" 
                                           id="threshold_percentage"
                                           min="1" 
                                           max="100" 
                                           value="{{ old('threshold_percentage', 80) }}"
                                           class="range range-primary flex-1" 
                                           oninput="updateThresholdValue(this.value)" />
                                    <div class="w-20 text-center">
                                        <span id="threshold-value" class="text-2xl font-bold">80</span>
                                        <span class="text-lg">%</span>
                                    </div>
                                </div>
                                <div class="text-sm text-base-content/50">
                                    Recevez une alerte lorsque <span id="threshold-example">80%</span> du budget est dépensé
                                </div>
                            </div>
                            @error('threshold_percentage')
                                <label class="label">
                                    <span class="label-text-alt text-error">{{ $message }}</span>
                                </label>
                            @enderror
                        </div>
                        
                        <!-- Notifications -->
                        <div class="form-control mb-8">
                            <label class="cursor-pointer label justify-start">
                                <input type="checkbox" 
                                       name="notifications_enabled" 
                                       id="notifications_enabled"
                                       class="checkbox checkbox-primary mr-3"
                                       {{ old('notifications_enabled', true) ? 'checked' : '' }} />
                                <span class="label-text">
                                    <span class="font-semibold">Activer les notifications</span>
                                    <div class="text-sm text-base-content/70 mt-1">
                                        Recevez des alertes lorsque le seuil est atteint ou dépassé
                                    </div>
                                </span>
                            </label>
                        </div>
                        
                        <!-- Boutons d'action -->
                        <div class="flex justify-between items-center pt-6 border-t border-base-300">
                            <a href="{{ route('budgets.index') }}" class="btn btn-ghost">
                                <i class="fas fa-times mr-2"></i>Annuler
                            </a>
                            <div class="flex space-x-2">
                                <button type="button" onclick="resetForm()" class="btn btn-outline">
                                    <i class="fas fa-redo mr-2"></i>Réinitialiser
                                </button>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save mr-2"></i>Créer le budget
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Aperçu -->
            <div class="card bg-base-100 shadow-lg mt-6">
                <div class="card-body">
                    <h3 class="card-title mb-4">Aperçu du budget</h3>
                    <div id="budget-preview" class="space-y-4">
                        <!-- Carte d'aperçu -->
                        <div class="border border-base-300 rounded-lg p-6">
                            <div class="flex items-center justify-between mb-4">
                                <div class="flex items-center">
                                    <div id="preview-icon" 
                                         class="w-16 h-16 rounded-xl flex items-center justify-center mr-4 bg-base-200">
                                        <i id="preview-icon-symbol" class="fas fa-receipt text-2xl text-base-content/50"></i>
                                    </div>
                                    <div>
                                        <div id="preview-category" class="text-2xl font-bold">Sélectionnez une catégorie</div>
                                        <div id="preview-period" class="text-lg text-base-content/70">Période</div>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <div class="text-sm text-base-content/70">Budget</div>
                                    <div id="preview-amount" class="text-3xl font-bold text-success">0,00 FDJ</div>
                                </div>
                            </div>
                            
                            <!-- Barre de progression -->
                            <div class="mb-4">
                                <div class="flex justify-between text-sm mb-2">
                                    <div>
                                        <span class="font-medium">Seuil d'alerte</span>
                                        <span id="preview-threshold-badge" class="badge badge-warning badge-sm ml-2">80%</span>
                                    </div>
                                    <div id="preview-threshold" class="font-bold text-warning">80%</div>
                                </div>
                                <div class="w-full bg-base-300 rounded-full h-3">
                                    <div id="preview-threshold-bar" class="h-3 rounded-full bg-warning" style="width: 80%"></div>
                                </div>
                                <div class="flex justify-between text-xs mt-1 text-base-content/50">
                                    <span>0%</span>
                                    <span>Seuil</span>
                                    <span>100%</span>
                                </div>
                            </div>
                            
                            <!-- Informations -->
                            <div class="grid grid-cols-2 gap-4 text-sm">
                                <div>
                                    <div class="font-semibold">Début</div>
                                    <div id="preview-start-date">{{ date('d/m/Y') }}</div>
                                </div>
                                <div>
                                    <div class="font-semibold">Fin</div>
                                    <div id="preview-end-date">Calcul automatique</div>
                                </div>
                            </div>
                            
                            <!-- Notifications -->
                            <div class="mt-4 pt-4 border-t border-base-300">
                                <div class="flex items-center">
                                    <i id="preview-notification-icon" class="fas fa-bell text-success mr-2"></i>
                                    <span id="preview-notification-text" class="text-sm">Notifications activées</span>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Estimation -->
                        <div class="border border-base-300 rounded-lg p-4">
                            <h4 class="font-bold mb-3">Estimation</h4>
                            <div class="space-y-2 text-sm">
                                <div class="flex justify-between">
                                    <span>Par jour:</span>
                                    <span id="preview-daily" class="font-bold">0,00 FDJ</span>
                                </div>
                                <div class="flex justify-between">
                                    <span>Par semaine:</span>
                                    <span id="preview-weekly" class="font-bold">0,00 FDJ</span>
                                </div>
                                <div class="flex justify-between">
                                    <span>Par mois:</span>
                                    <span id="preview-monthly" class="font-bold">0,00 FDJ</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

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
        
        #budget-preview {
            transition: all 0.3s ease;
        }
        
        #category-select option {
            padding: 8px;
        }
        
        .range::-webkit-slider-thumb {
            @apply w-6 h-6;
        }
    </style>
    @endpush

    @push('scripts')
    <script>
        // Mettre à jour la valeur du seuil
        function updateThresholdValue(value) {
            document.getElementById('threshold-value').textContent = value;
            document.getElementById('threshold-example').textContent = value + '%';
            updatePreview();
        }
        
        // Calculer la date de fin basée sur la période
        function calculateEndDate(startDate, period) {
            if (!startDate || !period) return null;
            
            const date = new Date(startDate);
            let endDate = new Date(date);
            
            switch(period) {
                case 'weekly':
                    endDate.setDate(date.getDate() + 6); // 7 jours - 1
                    break;
                case 'monthly':
                    endDate.setMonth(date.getMonth() + 1);
                    endDate.setDate(date.getDate() - 1);
                    break;
                case 'yearly':
                    endDate.setFullYear(date.getFullYear() + 1);
                    endDate.setDate(date.getDate() - 1);
                    break;
                default:
                    return null;
            }
            
            return endDate.toISOString().split('T')[0];
        }
        
        // Formater une date
        function formatDate(dateString) {
            if (!dateString) return 'Calcul automatique';
            
            const date = new Date(dateString);
            return date.toLocaleDateString('fr-FR', {
                day: '2-digit',
                month: '2-digit',
                year: 'numeric'
            });
        }
        
        // Mettre à jour l'aperçu en temps réel
        function updatePreview() {
            const categorySelect = document.getElementById('category-select');
            const selectedCategory = categorySelect.options[categorySelect.selectedIndex];
            const amount = document.getElementById('amount').value || '0';
            const period = document.querySelector('input[name="period"]:checked')?.value || 'monthly';
            const startDate = document.getElementById('start_date').value;
            const endDate = document.getElementById('end_date').value;
            const threshold = document.getElementById('threshold_percentage').value || 80;
            const notifications = document.getElementById('notifications_enabled').checked;
            
            // Calculer la date de fin si non spécifiée
            let calculatedEndDate = endDate;
            if (!endDate && startDate && period) {
                calculatedEndDate = calculateEndDate(startDate, period);
            }
            
            // Mettre à jour la catégorie
            if (selectedCategory && selectedCategory.value !== '') {
                const categoryName = selectedCategory.text.split('(')[0].trim();
                document.getElementById('preview-category').textContent = categoryName;
                
                // Mettre à jour l'icône et la couleur
                const iconContainer = document.getElementById('preview-icon');
                const iconSymbol = document.getElementById('preview-icon-symbol');
                const color = selectedCategory.dataset.color || '#3B82F6';
                const icon = selectedCategory.dataset.icon || 'receipt';
                
                iconContainer.style.backgroundColor = `${color}20`;
                iconSymbol.className = `fas fa-${icon} text-2xl`;
                iconSymbol.style.color = color;
            } else {
                document.getElementById('preview-category').textContent = 'Sélectionnez une catégorie';
                document.getElementById('preview-icon').style.backgroundColor = '';
                document.getElementById('preview-icon-symbol').className = 'fas fa-receipt text-2xl text-base-content/50';
            }
            
            // Mettre à jour le montant
            const formattedAmount = parseFloat(amount).toLocaleString('fr-FR', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
            document.getElementById('preview-amount').textContent = `${formattedAmount} FDJ`;
            
            // Mettre à jour la période
            const periodNames = {
                'monthly': 'Mensuel',
                'weekly': 'Hebdomadaire', 
                'yearly': 'Annuel'
            };
            document.getElementById('preview-period').textContent = periodNames[period] || period;
            
            // Mettre à jour les dates
            document.getElementById('preview-start-date').textContent = formatDate(startDate);
            document.getElementById('preview-end-date').textContent = formatDate(calculatedEndDate || endDate);
            
            // Mettre à jour le seuil
            document.getElementById('preview-threshold').textContent = `${threshold}%`;
            document.getElementById('preview-threshold-badge').textContent = `${threshold}%`;
            document.getElementById('preview-threshold-bar').style.width = `${threshold}%`;
            
            // Mettre à jour les notifications
            const notificationIcon = document.getElementById('preview-notification-icon');
            const notificationText = document.getElementById('preview-notification-text');
            
            if (notifications) {
                notificationIcon.className = 'fas fa-bell text-success mr-2';
                notificationText.textContent = 'Notifications activées';
                notificationText.className = 'text-sm text-success';
            } else {
                notificationIcon.className = 'fas fa-bell-slash text-base-content/50 mr-2';
                notificationText.textContent = 'Notifications désactivées';
                notificationText.className = 'text-sm text-base-content/50';
            }
            
            // Calculer les estimations
            const amountNum = parseFloat(amount) || 0;
            let days = 30; // Par défaut mensuel
            
            if (calculatedEndDate && startDate) {
                const start = new Date(startDate);
                const end = new Date(calculatedEndDate);
                days = Math.max(1, Math.ceil((end - start) / (1000 * 60 * 60 * 24)) + 1);
            } else {
                // Estimations par défaut selon la période
                switch(period) {
                    case 'weekly': days = 7; break;
                    case 'yearly': days = 365; break;
                    default: days = 30; break;
                }
            }
            
            const daily = amountNum / days;
            const weekly = daily * 7;
            const monthly = daily * 30;
            
            document.getElementById('preview-daily').textContent = 
                daily.toLocaleString('fr-FR', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' FDJ';
            document.getElementById('preview-weekly').textContent = 
                weekly.toLocaleString('fr-FR', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' FDJ';
            document.getElementById('preview-monthly').textContent = 
                monthly.toLocaleString('fr-FR', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' FDJ';
        }
        
        // Réinitialiser le formulaire
        function resetForm() {
            if (confirm('Voulez-vous vraiment réinitialiser le formulaire ?')) {
                document.getElementById('budget-form').reset();
                document.getElementById('threshold_percentage').value = 80;
                document.getElementById('start_date').value = new Date().toISOString().split('T')[0];
                updateThresholdValue(80);
                updatePreview();
            }
        }
        
        // Initialiser
        document.addEventListener('DOMContentLoaded', function() {
            // Initialiser l'aperçu
            updatePreview();
            
            // Écouter les changements
            const inputs = ['category-select', 'amount', 'start_date', 'end_date'];
            inputs.forEach(id => {
                const element = document.getElementById(id);
                if (element) {
                    element.addEventListener('change', updatePreview);
                    element.addEventListener('input', updatePreview);
                }
            });
            
            // Écouter les changements de période
            document.querySelectorAll('input[name="period"]').forEach(radio => {
                radio.addEventListener('change', function() {
                    updatePreview();
                    // Recalculer la date de fin si elle n'est pas spécifiée
                    const startDate = document.getElementById('start_date').value;
                    const endDate = document.getElementById('end_date').value;
                    if (startDate && !endDate) {
                        const calculatedEndDate = calculateEndDate(startDate, this.value);
                        document.getElementById('preview-end-date').textContent = formatDate(calculatedEndDate);
                    }
                });
            });
            
            // Écouter les changements de notifications
            document.getElementById('notifications_enabled').addEventListener('change', updatePreview);
            
            // Focus sur le montant
            document.getElementById('amount')?.focus();
        });
    </script>
    @endpush
</x-app-layout>