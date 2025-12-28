<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between">
            <div>
                <h2 class="text-2xl font-bold text-base-content">
                    <i class="fas fa-edit mr-3"></i>Modifier le budget
                </h2>
                <div class="breadcrumbs text-sm mt-1">
                    <ul>
                        <li><a href="{{ route('dashboard') }}"><i class="fas fa-home"></i> Dashboard</a></li>
                        <li><a href="{{ route('budgets.index') }}">Budgets</a></li>
                        <li><a href="{{ route('budgets.show', $budget) }}">{{ $budget->category->name }}</a></li>
                        <li>Modifier</li>
                    </ul>
                </div>
            </div>
            <div class="mt-2 md:mt-0">
                <a href="{{ route('budgets.show', $budget) }}" class="btn btn-ghost">
                    <i class="fas fa-arrow-left mr-2"></i>Retour
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-2xl mx-auto">
            <!-- Informations actuelles -->
            <div class="card bg-base-100 shadow-lg mb-6">
                <div class="card-body">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="card-title">Budget actuel</h3>
                        <span class="badge {{ $budget->status_color === 'error' ? 'badge-error' : ($budget->status_color === 'warning' ? 'badge-warning' : 'badge-success') }}">
                            {{ round($budget->progress_percentage, 1) }}%
                        </span>
                    </div>
                    
                    <div class="flex items-center justify-between p-4 bg-base-200 rounded-lg">
                        <div class="flex items-center">
                            <div class="w-12 h-12 rounded-lg flex items-center justify-center mr-4" 
                                 style="background-color: {{ $budget->category->color }}20">
                                <i class="fas fa-{{ $budget->category->icon }} text-xl" 
                                   style="color: {{ $budget->category->color }}"></i>
                            </div>
                            <div>
                                <div class="font-bold">{{ $budget->category->name }}</div>
                                <div class="text-sm text-base-content/70">
                                    {{ $budget->formatted_period }} • 
                                    {{ $budget->start_date->format('d/m/Y') }} → {{ $budget->end_date->format('d/m/Y') }}
                                </div>
                            </div>
                        </div>
                        <div class="text-right">
                            <div class="text-sm text-base-content/70">Budget</div>
                            <div class="font-bold text-success">{{ number_format($budget->amount, 2, ',', ' ') }} FDJ</div>
                        </div>
                    </div>
                    
                    <!-- Progression -->
                    <div class="mt-4">
                        <div class="flex justify-between text-sm mb-2">
                            <div>
                                <span class="font-medium">Progression</span>
                                <span class="text-base-content/70 ml-2">
                                    ({{ number_format($budget->spent_amount, 2, ',', ' ') }} FDJ dépensés)
                                </span>
                            </div>
                            <div class="font-bold text-{{ $budget->status_color }}">
                                {{ round($budget->progress_percentage, 1) }}%
                            </div>
                        </div>
                        
                        <div class="w-full bg-base-300 rounded-full h-2">
                            <div class="h-2 rounded-full bg-{{ $budget->status_color }}" 
                                 style="width: {{ min($budget->progress_percentage, 100) }}%"></div>
                        </div>
                        
                        <div class="flex justify-between text-xs mt-1 text-base-content/50">
                            <span>0%</span>
                            <span>Seuil: {{ $budget->threshold_percentage }}%</span>
                            <span>100%</span>
                        </div>
                    </div>
                    
                    <!-- Rappel -->
                    @if($budget->spent_amount > 0)
                        <div class="alert alert-info mt-4">
                            <i class="fas fa-info-circle"></i>
                            <div>
                                <span class="font-bold">Attention :</span>
                                <span class="block">
                                    {{ number_format($budget->spent_amount, 2, ',', ' ') }} FDJ ont déjà été dépensés sur ce budget.
                                    Modifier le montant total affectera le calcul de progression.
                                </span>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Formulaire de modification -->
            <div class="card bg-base-100 shadow-lg">
                <div class="card-body">
                    <form method="POST" action="{{ route('budgets.update', $budget) }}" id="budget-form">
                        @csrf
                        @method('PUT')
                        
                        <!-- Catégorie (non modifiable) -->
                        <div class="form-control mb-6">
                            <label class="label">
                                <span class="label-text font-semibold">Catégorie</span>
                            </label>
                            <div class="flex items-center p-3 bg-base-200 rounded-lg">
                                <div class="w-10 h-10 rounded-lg flex items-center justify-center mr-3" 
                                     style="background-color: {{ $budget->category->color }}20">
                                    <i class="fas fa-{{ $budget->category->icon }}" 
                                       style="color: {{ $budget->category->color }}"></i>
                                </div>
                                <div>
                                    <div class="font-bold">{{ $budget->category->name }}</div>
                                    <div class="text-sm opacity-70">
                                        La catégorie ne peut pas être modifiée
                                    </div>
                                </div>
                            </div>
                            <input type="hidden" name="category_id" value="{{ $budget->category_id }}">
                        </div>
                        
                        <!-- Montant -->
                        <div class="form-control mb-6">
                            <label class="label">
                                <span class="label-text font-semibold">Montant du budget *</span>
                                <span class="label-text-alt">
                                    Actuel: {{ number_format($budget->amount, 2, ',', ' ') }} FDJ
                                </span>
                            </label>
                            <div class="relative">
                                <input type="number" 
                                       name="amount" 
                                       id="amount"
                                       step="0.01"
                                       min="0.01"
                                       value="{{ old('amount', $budget->amount) }}"
                                       placeholder="0.00"
                                       class="input input-bordered w-full text-2xl font-bold pl-10 @error('amount') input-error @enderror"
                                       required
                                       autofocus />
                                <span class="absolute left-3 top-1/2 transform -translate-y-1/2 text-2xl font-bold">FDJ</span>
                            </div>
                            <div class="text-xs text-base-content/50 mt-1">
                                Montant total pour la période sélectionnée
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
                                <span class="label-text-alt">
                                    Actuelle: {{ $budget->formatted_period }}
                                </span>
                            </label>
                            <div class="grid grid-cols-3 gap-4">
                                <label class="cursor-pointer">
                                    <input type="radio" 
                                           name="period" 
                                           value="monthly" 
                                           class="radio radio-primary" 
                                           {{ old('period', $budget->period) === 'monthly' ? 'checked' : '' }} />
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
                                           {{ old('period', $budget->period) === 'weekly' ? 'checked' : '' }} />
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
                                           {{ old('period', $budget->period) === 'yearly' ? 'checked' : '' }} />
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
                                    <span class="label-text-alt">
                                        Actuelle: {{ $budget->start_date->format('d/m/Y') }}
                                    </span>
                                </label>
                                <input type="date" 
                                       name="start_date" 
                                       id="start_date"
                                       value="{{ old('start_date', $budget->start_date->format('Y-m-d')) }}"
                                       class="input input-bordered w-full @error('start_date') input-error @enderror"
                                       required />
                                <div class="text-xs text-base-content/50 mt-1">
                                    Début de la période budgétaire
                                </div>
                                @error('start_date')
                                    <label class="label">
                                        <span class="label-text-alt text-error">{{ $message }}</span>
                                    </label>
                                @enderror
                            </div>
                            
                            <!-- Date de fin -->
                            <div class="form-control">
                                <label class="label">
                                    <span class="label-text font-semibold">Date de fin *</span>
                                    <span class="label-text-alt">
                                        Actuelle: {{ $budget->end_date->format('d/m/Y') }}
                                    </span>
                                </label>
                                <input type="date" 
                                       name="end_date" 
                                       id="end_date"
                                       value="{{ old('end_date', $budget->end_date->format('Y-m-d')) }}"
                                       class="input input-bordered w-full @error('end_date') input-error @enderror"
                                       required />
                                <div class="text-xs text-base-content/50 mt-1">
                                    Fin de la période budgétaire
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
                                <span class="label-text-alt">
                                    Actuel: {{ $budget->threshold_percentage }}%
                                </span>
                            </label>
                            <div class="space-y-4">
                                <div class="flex items-center space-x-4">
                                    <input type="range" 
                                           name="threshold_percentage" 
                                           id="threshold_percentage"
                                           min="1" 
                                           max="100" 
                                           value="{{ old('threshold_percentage', $budget->threshold_percentage) }}"
                                           class="range range-primary flex-1" 
                                           oninput="updateThresholdValue(this.value)" />
                                    <div class="w-20 text-center">
                                        <span id="threshold-value" class="text-2xl font-bold">{{ old('threshold_percentage', $budget->threshold_percentage) }}</span>
                                        <span class="text-lg">%</span>
                                    </div>
                                </div>
                                <div class="text-sm text-base-content/50">
                                    Recevez une alerte lorsque <span id="threshold-example">{{ old('threshold_percentage', $budget->threshold_percentage) }}%</span> du budget est dépensé
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
                                       {{ old('notifications_enabled', $budget->notifications_enabled) ? 'checked' : '' }} />
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
                            <div class="flex space-x-2">
                                <a href="{{ route('budgets.show', $budget) }}" class="btn btn-ghost">
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
                    <div id="budget-preview" class="space-y-4">
                        <!-- Carte d'aperçu -->
                        <div class="border border-base-300 rounded-lg p-6">
                            <div class="flex items-center justify-between mb-4">
                                <div class="flex items-center">
                                    <div class="w-16 h-16 rounded-xl flex items-center justify-center mr-4" 
                                         style="background-color: {{ $budget->category->color }}20">
                                        <i class="fas fa-{{ $budget->category->icon }} text-2xl" 
                                           style="color: {{ $budget->category->color }}"></i>
                                    </div>
                                    <div>
                                        <div class="text-2xl font-bold">{{ $budget->category->name }}</div>
                                        <div id="preview-period" class="text-lg text-base-content/70">
                                            {{ $budget->formatted_period }}
                                        </div>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <div class="text-sm text-base-content/70">Nouveau budget</div>
                                    <div id="preview-amount" class="text-3xl font-bold text-success">
                                        {{ number_format($budget->amount, 2, ',', ' ') }} FDJ
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Progression avec nouveau montant -->
                            <div class="mb-4">
                                <div class="flex justify-between text-sm mb-2">
                                    <div>
                                        <span class="font-medium">Progression estimée</span>
                                        <span class="text-base-content/70 ml-2">
                                            ({{ number_format($budget->spent_amount, 2, ',', ' ') }} FDJ déjà dépensés)
                                        </span>
                                    </div>
                                    <div id="preview-progress" class="font-bold">
                                        {{ round($budget->progress_percentage, 1) }}%
                                    </div>
                                </div>
                                
                                <div class="w-full bg-base-300 rounded-full h-3 mb-2">
                                    <div id="preview-progress-bar" 
                                         class="h-3 rounded-full {{ $budget->status_color === 'error' ? 'bg-error' : ($budget->status_color === 'warning' ? 'bg-warning' : 'bg-success') }}"
                                         style="width: {{ min($budget->progress_percentage, 100) }}%">
                                    </div>
                                </div>
                                
                                <!-- Seuil -->
                                <div class="flex justify-between text-sm mb-2">
                                    <div>
                                        <span class="font-medium">Seuil d'alerte</span>
                                        <span id="preview-threshold-badge" class="badge badge-warning badge-sm ml-2">
                                            {{ $budget->threshold_percentage }}%
                                        </span>
                                    </div>
                                    <div id="preview-threshold" class="font-bold text-warning">
                                        {{ $budget->threshold_percentage }}%
                                    </div>
                                </div>
                                
                                <div class="w-full bg-base-300 rounded-full h-2">
                                    <div id="preview-threshold-bar" class="h-2 rounded-full bg-warning" 
                                         style="width: {{ $budget->threshold_percentage }}%"></div>
                                </div>
                            </div>
                            
                            <!-- Informations -->
                            <div class="grid grid-cols-2 gap-4 text-sm">
                                <div>
                                    <div class="font-semibold">Début</div>
                                    <div id="preview-start-date">{{ $budget->start_date->format('d/m/Y') }}</div>
                                </div>
                                <div>
                                    <div class="font-semibold">Fin</div>
                                    <div id="preview-end-date">{{ $budget->end_date->format('d/m/Y') }}</div>
                                </div>
                            </div>
                            
                            <!-- Notifications -->
                            <div class="mt-4 pt-4 border-t border-base-300">
                                <div class="flex items-center">
                                    @if($budget->notifications_enabled)
                                        <i id="preview-notification-icon" class="fas fa-bell text-success mr-2"></i>
                                        <span id="preview-notification-text" class="text-sm text-success">Notifications activées</span>
                                    @else
                                        <i id="preview-notification-icon" class="fas fa-bell-slash text-base-content/50 mr-2"></i>
                                        <span id="preview-notification-text" class="text-sm text-base-content/50">Notifications désactivées</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de suppression -->
    <dialog id="delete-modal" class="modal">
        <div class="modal-box">
            <h3 class="font-bold text-lg">Confirmer la suppression</h3>
            <div class="py-4 space-y-4">
                <p>
                    Êtes-vous sûr de vouloir supprimer le budget pour 
                    <strong>"{{ $budget->category->name }}"</strong> ?
                </p>
                
                @php
                    $transactionCount = $budget->category->transactions()
                        ->where('user_id', auth()->id())
                        ->whereBetween('date', [$budget->start_date, $budget->end_date])
                        ->count();
                @endphp
                
                @if($transactionCount > 0)
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i>
                        <span>
                            Ce budget contient <strong>{{ $transactionCount }} transactions</strong>.
                            Les transactions ne seront pas supprimées, seul le budget le sera.
                        </span>
                    </div>
                @endif
            </div>
            
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
        
        // Mettre à jour l'aperçu en temps réel
        function updatePreview() {
            const amount = document.getElementById('amount').value || '{{ $budget->amount }}';
            const period = document.querySelector('input[name="period"]:checked')?.value || '{{ $budget->period }}';
            const startDate = document.getElementById('start_date').value;
            const endDate = document.getElementById('end_date').value;
            const threshold = document.getElementById('threshold_percentage').value || '{{ $budget->threshold_percentage }}';
            const notifications = document.getElementById('notifications_enabled').checked;
            const spentAmount = {{ $budget->spent_amount }};
            
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
            function formatDate(dateString) {
                if (!dateString) return '';
                const date = new Date(dateString);
                return date.toLocaleDateString('fr-FR', {
                    day: '2-digit',
                    month: '2-digit',
                    year: 'numeric'
                });
            }
            
            document.getElementById('preview-start-date').textContent = formatDate(startDate);
            document.getElementById('preview-end-date').textContent = formatDate(endDate);
            
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
            
            // Calculer la nouvelle progression
            const amountNum = parseFloat(amount) || 0;
            let progressPercentage = 0;
            if (amountNum > 0) {
                progressPercentage = (spentAmount / amountNum) * 100;
            }
            
            // Déterminer la couleur de la progression
            let progressColor = 'success';
            if (progressPercentage >= 100) {
                progressColor = 'error';
            } else if (progressPercentage >= threshold) {
                progressColor = 'warning';
            }
            
            // Mettre à jour la progression
            document.getElementById('preview-progress').textContent = progressPercentage.toFixed(1) + '%';
            document.getElementById('preview-progress').className = `font-bold text-${progressColor}`;
            
            const progressBar = document.getElementById('preview-progress-bar');
            progressBar.style.width = `${Math.min(progressPercentage, 100)}%`;
            progressBar.className = `h-3 rounded-full bg-${progressColor}`;
        }
        
        // Réinitialiser le formulaire
        function resetForm() {
            if (confirm('Voulez-vous vraiment réinitialiser le formulaire ? Les modifications seront perdues.')) {
                document.getElementById('amount').value = '{{ $budget->amount }}';
                document.getElementById('start_date').value = '{{ $budget->start_date->format("Y-m-d") }}';
                document.getElementById('end_date').value = '{{ $budget->end_date->format("Y-m-d") }}';
                document.getElementById('threshold_percentage').value = '{{ $budget->threshold_percentage }}';
                document.getElementById('notifications_enabled').checked = {{ $budget->notifications_enabled ? 'true' : 'false' }};
                
                // Réinitialiser la période
                document.querySelectorAll('input[name="period"]').forEach(radio => {
                    radio.checked = radio.value === '{{ $budget->period }}';
                });
                
                updateThresholdValue('{{ $budget->threshold_percentage }}');
                updatePreview();
            }
        }
        
        // Initialiser
        document.addEventListener('DOMContentLoaded', function() {
            // Initialiser l'aperçu
            updatePreview();
            
            // Écouter les changements
            const inputs = ['amount', 'start_date', 'end_date'];
            inputs.forEach(id => {
                const element = document.getElementById(id);
                if (element) {
                    element.addEventListener('input', updatePreview);
                    element.addEventListener('change', updatePreview);
                }
            });
            
            // Écouter les changements de période
            document.querySelectorAll('input[name="period"]').forEach(radio => {
                radio.addEventListener('change', updatePreview);
            });
            
            // Écouter les changements de notifications
            document.getElementById('notifications_enabled').addEventListener('change', updatePreview);
            
            // Focus sur le montant
            document.getElementById('amount')?.focus();
        });
    </script>
    @endpush
</x-app-layout>