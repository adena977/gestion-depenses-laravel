<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between">
            <div>
                <h2 class="text-2xl font-bold text-base-content">
                    <i class="fas fa-edit mr-3"></i>Modifier la catégorie
                </h2>
                <div class="breadcrumbs text-sm mt-1">
                    <ul>
                        <li><a href="{{ route('dashboard') }}"><i class="fas fa-home"></i> Dashboard</a></li>
                        <li><a href="{{ route('categories.index') }}">Catégories</a></li>
                        <li>Modifier</li>
                    </ul>
                </div>
            </div>
            <div class="mt-2 md:mt-0">
                <a href="{{ route('categories.index') }}" class="btn btn-ghost">
                    <i class="fas fa-arrow-left mr-2"></i>Retour
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-2xl mx-auto">
            <!-- Information actuelle -->
            <div class="card bg-base-100 shadow-lg mb-6">
                <div class="card-body">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="card-title">Catégorie actuelle</h3>
                        <span class="badge {{ $category->type === 'expense' ? 'badge-error' : 'badge-success' }}">
                            {{ $category->type === 'expense' ? 'Dépense' : 'Revenu' }}
                        </span>
                    </div>
                    
                    <div class="flex items-center justify-between p-4 bg-base-200 rounded-lg">
                        <div class="flex items-center">
                            <div class="w-16 h-16 rounded-xl flex items-center justify-center mr-4" 
                                 style="background-color: {{ $category->color }}20">
                                <i class="fas fa-{{ $category->icon }} text-2xl" 
                                   style="color: {{ $category->color }}"></i>
                            </div>
                            <div>
                                <div class="text-2xl font-bold">{{ $category->name }}</div>
                                <div class="text-sm opacity-70">
                                    <i class="fas fa-{{ $category->icon }} mr-1"></i>
                                    {{ $category->type === 'expense' ? 'Dépense' : 'Revenu' }}
                                </div>
                                @if($category->description)
                                    <div class="text-sm mt-2 text-base-content/70">{{ $category->description }}</div>
                                @endif
                            </div>
                        </div>
                    </div>
                    
                    <!-- Statistiques -->
                    <div class="grid grid-cols-2 gap-4 mt-4 text-sm">
                        <div class="text-center p-3 bg-base-300 rounded-lg">
                            <div class="font-bold text-lg">{{ $category->transactions()->count() }}</div>
                            <div class="text-xs opacity-70">Transactions</div>
                        </div>
                        <div class="text-center p-3 bg-base-300 rounded-lg">
                            <div class="font-bold text-lg {{ $category->type === 'expense' ? 'text-error' : 'text-success' }}">
                                {{ number_format($category->transactions()->sum('amount'), 2, ',', ' ') }} FDJ
                            </div>
                            <div class="text-xs opacity-70">Montant total</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Formulaire de modification -->
            <div class="card bg-base-100 shadow-lg">
                <div class="card-body">
                    <form method="POST" action="{{ route('categories.update', $category) }}" id="category-form">
                        @csrf
                        @method('PUT')
                        
                        <!-- Type (affichage seulement) -->
                        <div class="form-control mb-6">
                            <label class="label">
                                <span class="label-text font-semibold">Type de catégorie</span>
                            </label>
                            <div class="flex items-center p-3 bg-base-200 rounded-lg">
                                <div class="w-10 h-10 rounded-full {{ $category->type === 'expense' ? 'bg-error/20' : 'bg-success/20' }} flex items-center justify-center mr-3">
                                    <i class="fas fa-{{ $category->type === 'expense' ? 'arrow-down' : 'arrow-up' }} {{ $category->type === 'expense' ? 'text-error' : 'text-success' }}"></i>
                                </div>
                                <div>
                                    <div class="font-bold">{{ $category->type === 'expense' ? 'Dépense' : 'Revenu' }}</div>
                                    <div class="text-sm opacity-70">
                                        Le type de catégorie ne peut pas être modifié
                                    </div>
                                </div>
                            </div>
                            <input type="hidden" name="type" value="{{ $category->type }}">
                        </div>
                        
                        <!-- Nom -->
                        <div class="form-control mb-6">
                            <label class="label">
                                <span class="label-text font-semibold">Nom de la catégorie *</span>
                            </label>
                            <input type="text" 
                                   name="name" 
                                   id="name"
                                   value="{{ old('name', $category->name) }}"
                                   placeholder="Ex: Nourriture, Transport, Salaire..."
                                   class="input input-bordered w-full text-lg @error('name') input-error @enderror"
                                   required
                                   autofocus />
                            @error('name')
                                <label class="label">
                                    <span class="label-text-alt text-error">{{ $message }}</span>
                                </label>
                            @enderror
                        </div>
                        
                        <!-- Couleur et Icône -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                            <!-- Sélection de couleur -->
                            <div class="form-control">
                                <label class="label">
                                    <span class="label-text font-semibold">Couleur *</span>
                                </label>
                                <div class="space-y-4">
                                    <div class="flex items-center">
                                        <div id="color-preview" 
                                             class="w-12 h-12 rounded-lg mr-4 border border-base-300"
                                             style="background-color: {{ old('color', $category->color) }}"></div>
                                        <input type="color" 
                                               name="color" 
                                               id="color"
                                               value="{{ old('color', $category->color) }}"
                                               class="w-24 h-12 cursor-pointer"
                                               title="Choisir une couleur" />
                                    </div>
                                    <input type="text" 
                                           name="color_text" 
                                           id="color-text"
                                           value="{{ old('color', $category->color) }}"
                                           placeholder="#3B82F6"
                                           class="input input-bordered w-full"
                                           pattern="^#[0-9A-Fa-f]{6}$" />
                                    <div class="text-xs text-base-content/50">
                                        Entrez une valeur hexadécimale (#RRGGBB)
                                    </div>
                                </div>
                                @error('color')
                                    <label class="label">
                                        <span class="label-text-alt text-error">{{ $message }}</span>
                                    </label>
                                @enderror
                            </div>
                            
                            <!-- Sélection d'icône -->
                            <div class="form-control">
                                <label class="label">
                                    <span class="label-text font-semibold">Icône *</span>
                                    <span class="label-text-alt">
                                        <a href="https://fontawesome.com/icons" target="_blank" class="link link-primary">
                                            <i class="fas fa-external-link-alt mr-1"></i>Voir toutes
                                        </a>
                                    </span>
                                </label>
                                <div class="space-y-4">
                                    <div class="flex items-center">
                                        <div id="icon-preview" 
                                             class="w-12 h-12 rounded-lg flex items-center justify-center mr-4 border border-base-300"
                                             style="background-color: {{ old('color', $category->color) }}20">
                                            <i id="icon-preview-symbol" 
                                               class="fas fa-{{ old('icon', $category->icon) }} text-xl"
                                               style="color: {{ old('color', $category->color) }}"></i>
                                        </div>
                                        <input type="text" 
                                               name="icon" 
                                               id="icon"
                                               value="{{ old('icon', $category->icon) }}"
                                               placeholder="Nom de l'icône (sans 'fa-')"
                                               class="input input-bordered w-full" />
                                    </div>
                                    <div class="text-xs text-base-content/50">
                                        Entrez le nom de l'icône FontAwesome
                                    </div>
                                </div>
                                @error('icon')
                                    <label class="label">
                                        <span class="label-text-alt text-error">{{ $message }}</span>
                                    </label>
                                @enderror
                            </div>
                        </div>
                        
                        <!-- Icônes suggérées -->
                        <div class="mb-6">
                            <label class="label">
                                <span class="label-text font-semibold">Icônes suggérées</span>
                            </label>
                            <div class="grid grid-cols-6 md:grid-cols-8 gap-2">
                                @php
                                    $suggestedIcons = [
                                        'expense' => ['shopping-cart', 'car', 'home', 'heart', 'film', 'utensils', 'tshirt', 'gas-pump'],
                                        'income' => ['money-check', 'laptop', 'chart-line', 'gift', 'hand-holding-usd', 'briefcase', 'university', 'piggy-bank']
                                    ];
                                @endphp
                                
                                @foreach($suggestedIcons[$category->type] as $icon)
                                    <button type="button" 
                                            class="btn btn-ghost btn-square aspect-square {{ old('icon', $category->icon) === $icon ? 'bg-base-300' : '' }}"
                                            onclick="selectIcon('{{ $icon }}')"
                                            title="{{ $icon }}">
                                        <i class="fas fa-{{ $icon }}"></i>
                                    </button>
                                @endforeach
                            </div>
                        </div>
                        
                        <!-- Couleurs suggérées -->
                        <div class="mb-8">
                            <label class="label">
                                <span class="label-text font-semibold">Couleurs suggérées</span>
                            </label>
                            <div class="grid grid-cols-6 md:grid-cols-10 gap-2">
                                @php
                                    $suggestedColors = [
                                        '#3B82F6', // Blue
                                        '#10B981', // Green
                                        '#8B5CF6', // Purple
                                        '#F59E0B', // Orange
                                        '#EF4444', // Red
                                        '#EC4899', // Pink
                                        '#6366F1', // Indigo
                                        '#14B8A6', // Teal
                                        '#F97316', // Orange-600
                                        '#6B7280', // Gray
                                    ];
                                @endphp
                                
                                @foreach($suggestedColors as $color)
                                    <button type="button" 
                                            class="btn btn-ghost btn-square aspect-square p-0 {{ old('color', $category->color) === $color ? 'ring-2 ring-primary' : '' }}"
                                            onclick="selectColor('{{ $color }}')"
                                            title="{{ $color }}">
                                        <div class="w-full h-full rounded" style="background-color: {{ $color }}"></div>
                                    </button>
                                @endforeach
                            </div>
                        </div>
                        
                        <!-- Description -->
                        <div class="form-control mb-8">
                            <label class="label">
                                <span class="label-text font-semibold">Description</span>
                                <span class="label-text-alt">Optionnel</span>
                            </label>
                            <textarea name="description" 
                                      id="description"
                                      rows="3"
                                      placeholder="Description facultative de la catégorie..."
                                      class="textarea textarea-bordered w-full">{{ old('description', $category->description) }}</textarea>
                        </div>
                        
                        <!-- Boutons d'action -->
                        <div class="flex justify-between items-center pt-6 border-t border-base-300">
                            <div class="flex space-x-2">
                                <a href="{{ route('categories.index') }}" class="btn btn-ghost">
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
                    <div id="category-preview" class="space-y-4">
                        <!-- Carte d'aperçu -->
                        <div class="border border-base-300 rounded-lg p-6">
                            <div class="flex items-center justify-between mb-4">
                                <div class="flex items-center">
                                    <div id="preview-icon-container" 
                                         class="w-16 h-16 rounded-xl flex items-center justify-center mr-4"
                                         style="background-color: {{ old('color', $category->color) }}20">
                                        <i id="preview-icon-display" 
                                           class="fas fa-{{ old('icon', $category->icon) }} text-2xl"
                                           style="color: {{ old('color', $category->color) }}"></i>
                                    </div>
                                    <div>
                                        <div id="preview-name" class="text-2xl font-bold">
                                            {{ old('name', $category->name) }}
                                        </div>
                                        <div id="preview-type" class="text-lg {{ $category->type === 'expense' ? 'text-error' : 'text-success' }}">
                                            {{ $category->type === 'expense' ? 'Dépense' : 'Revenu' }}
                                        </div>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <div class="text-sm text-base-content/70">
                                        {{ $category->transactions()->count() }} transactions
                                    </div>
                                    <div class="text-xs text-base-content/50">
                                        {{ number_format($category->transactions()->sum('amount'), 2, ',', ' ') }} FDJ
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Description -->
                            <div id="preview-description" class="mt-4 p-3 bg-base-200 rounded-lg">
                                @if(old('description', $category->description))
                                    <p class="text-base-content/70">{{ old('description', $category->description) }}</p>
                                @else
                                    <p class="text-base-content/50 italic">Aucune description</p>
                                @endif
                            </div>
                            
                            <!-- Informations -->
                            <div class="grid grid-cols-2 gap-4 mt-4 text-sm">
                                <div>
                                    <div class="font-semibold">Couleur</div>
                                    <div class="flex items-center">
                                        <div id="preview-color-swatch" 
                                             class="w-4 h-4 rounded mr-2"
                                             style="background-color: {{ old('color', $category->color) }}"></div>
                                        <span id="preview-color-value">{{ old('color', $category->color) }}</span>
                                    </div>
                                </div>
                                <div>
                                    <div class="font-semibold">Icône</div>
                                    <div id="preview-icon-name" class="flex items-center">
                                        <i class="fas fa-{{ old('icon', $category->icon) }} mr-2"></i>
                                        {{ old('icon', $category->icon) }}
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Impact sur les transactions existantes -->
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle"></i>
                            <div>
                                <span class="font-bold">Attention :</span>
                                <span class="block">
                                    Les modifications de couleur et d'icône seront appliquées aux 
                                    <strong>{{ $category->transactions()->count() }} transactions</strong> existantes 
                                    de cette catégorie.
                                </span>
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
            
            @php
                $transactionCount = $category->transactions()->count();
                $budgetCount = $category->budgets()->count();
            @endphp
            
            <div class="py-4 space-y-4">
                <p>
                    Êtes-vous sûr de vouloir supprimer la catégorie <strong>"{{ $category->name }}"</strong> ?
                </p>
                
                @if($transactionCount > 0 || $budgetCount > 0)
                    <div class="alert alert-error">
                        <i class="fas fa-exclamation-circle"></i>
                        <div>
                            <span class="font-bold">Cette catégorie est utilisée :</span>
                            <ul class="mt-2 space-y-1">
                                @if($transactionCount > 0)
                                    <li>• Dans <strong>{{ $transactionCount }} transactions</strong></li>
                                @endif
                                @if($budgetCount > 0)
                                    <li>• Dans <strong>{{ $budgetCount }} budgets</strong></li>
                                @endif
                            </ul>
                        </div>
                    </div>
                    
                    <div class="form-control">
                        <label class="cursor-pointer label justify-start">
                            <input type="radio" 
                                   name="delete_action" 
                                   value="reassign" 
                                   class="radio radio-primary mr-3" 
                                   checked />
                            <span class="label-text">
                                <strong>Réaffecter les transactions</strong> à la catégorie "Autres"
                                <div class="text-xs opacity-70 mt-1">
                                    Les budgets associés seront supprimés
                                </div>
                            </span>
                        </label>
                    </div>
                    
                    <div class="form-control">
                        <label class="cursor-pointer label justify-start">
                            <input type="radio" 
                                   name="delete_action" 
                                   value="delete_all" 
                                   class="radio radio-error mr-3" />
                            <span class="label-text">
                                <strong>Tout supprimer</strong> (transactions et budgets)
                                <div class="text-xs opacity-70 mt-1">
                                    Cette action est irréversible
                                </div>
                            </span>
                        </label>
                    </div>
                @else
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i>
                        <span>Cette catégorie n'est utilisée dans aucune transaction ou budget.</span>
                    </div>
                @endif
            </div>
            
            <div class="modal-action">
                <form method="dialog">
                    <button class="btn">Annuler</button>
                </form>
                <form action="{{ route('categories.destroy', $category) }}" method="POST" id="delete-form">
                    @csrf
                    @method('DELETE')
                    <input type="hidden" name="delete_action" id="delete-action-input" value="reassign">
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
        #color-preview, #icon-preview, #preview-icon-container {
            transition: all 0.3s ease;
        }
        
        #color {
            -webkit-appearance: none;
            -moz-appearance: none;
            appearance: none;
            background-color: transparent;
            border: none;
            cursor: pointer;
        }
        
        #color::-webkit-color-swatch-wrapper {
            padding: 0;
        }
        
        #color::-webkit-color-swatch {
            border-radius: 8px;
            border: 2px solid hsl(var(--bc)/0.2);
        }
        
        #color::-moz-color-swatch {
            border-radius: 8px;
            border: 2px solid hsl(var(--bc)/0.2);
        }
        
        .suggested-color:hover {
            transform: scale(1.1);
        }
        
        .suggested-icon:hover {
            background-color: hsl(var(--b3));
        }
    </style>
    @endpush

    @push('scripts')
    <script>
        // Sélectionner une icône
        function selectIcon(icon) {
            document.getElementById('icon').value = icon;
            updatePreview();
            
            // Mettre en surbrillance l'icône sélectionnée
            document.querySelectorAll('.suggested-icon').forEach(btn => {
                btn.classList.remove('bg-base-300');
                if (btn.querySelector('i').className.includes(`fa-${icon}`)) {
                    btn.classList.add('bg-base-300');
                }
            });
        }
        
        // Sélectionner une couleur
        function selectColor(color) {
            document.getElementById('color').value = color;
            document.getElementById('color-text').value = color;
            updatePreview();
            
            // Mettre en surbrillance la couleur sélectionnée
            document.querySelectorAll('.suggested-color').forEach(btn => {
                btn.classList.remove('ring-2', 'ring-primary');
                if (btn.querySelector('div').style.backgroundColor === color || 
                    rgbToHex(btn.querySelector('div').style.backgroundColor) === color) {
                    btn.classList.add('ring-2', 'ring-primary');
                }
            });
        }
        
        // Convertir RGB en hex
        function rgbToHex(rgb) {
            if (!rgb) return '';
            const result = rgb.match(/\d+/g);
            if (!result) return '';
            return '#' + result.map(x => parseInt(x).toString(16).padStart(2, '0')).join('');
        }
        
        // Mettre à jour l'aperçu en temps réel
        function updatePreview() {
            const name = document.getElementById('name').value || '{{ $category->name }}';
            const color = document.getElementById('color').value;
            const icon = document.getElementById('icon').value || '{{ $category->icon }}';
            const description = document.getElementById('description').value;
            const colorText = document.getElementById('color-text').value;
            
            // Mettre à jour les prévisualisations de couleur
            document.getElementById('color-preview').style.backgroundColor = color;
            document.getElementById('preview-color-swatch').style.backgroundColor = color;
            document.getElementById('preview-color-value').textContent = colorText || color;
            
            // Mettre à jour les prévisualisations d'icône
            document.getElementById('icon-preview').style.backgroundColor = `${color}20`;
            document.getElementById('icon-preview-symbol').className = `fas fa-${icon} text-xl`;
            document.getElementById('icon-preview-symbol').style.color = color;
            
            document.getElementById('preview-icon-container').style.backgroundColor = `${color}20`;
            document.getElementById('preview-icon-display').className = `fas fa-${icon} text-2xl`;
            document.getElementById('preview-icon-display').style.color = color;
            document.getElementById('preview-icon-name').innerHTML = `<i class="fas fa-${icon} mr-2"></i>${icon}`;
            
            // Mettre à jour le nom
            document.getElementById('preview-name').textContent = name;
            
            // Mettre à jour la description
            const descriptionElement = document.getElementById('preview-description');
            if (description) {
                descriptionElement.innerHTML = `<p class="text-base-content/70">${description}</p>`;
            } else {
                descriptionElement.innerHTML = '<p class="text-base-content/50 italic">Aucune description</p>';
            }
        }
        
        // Synchroniser le champ texte de couleur avec le sélecteur
        function syncColorInputs() {
            const colorPicker = document.getElementById('color');
            const colorText = document.getElementById('color-text');
            
            colorPicker.addEventListener('input', function() {
                colorText.value = this.value;
                updatePreview();
            });
            
            colorText.addEventListener('input', function() {
                // Valider le format hexadécimal
                const hexColor = /^#[0-9A-Fa-f]{6}$/;
                if (hexColor.test(this.value)) {
                    colorPicker.value = this.value;
                    updatePreview();
                }
            });
            
            colorText.addEventListener('change', function() {
                // Corriger automatiquement si possible
                let value = this.value.trim();
                if (!value.startsWith('#')) {
                    value = '#' + value;
                }
                
                // Essayer de convertir en hexadécimal valide
                if (/^#[0-9A-Fa-f]{3}$/.test(value)) {
                    // Convertir #RGB en #RRGGBB
                    value = '#' + value[1] + value[1] + value[2] + value[2] + value[3] + value[3];
                }
                
                if (/^#[0-9A-Fa-f]{6}$/.test(value)) {
                    this.value = value;
                    colorPicker.value = value;
                    updatePreview();
                }
            });
        }
        
        // Réinitialiser le formulaire
        function resetForm() {
            if (confirm('Voulez-vous vraiment réinitialiser le formulaire ? Les modifications seront perdues.')) {
                document.getElementById('name').value = '{{ $category->name }}';
                document.getElementById('color').value = '{{ $category->color }}';
                document.getElementById('color-text').value = '{{ $category->color }}';
                document.getElementById('icon').value = '{{ $category->icon }}';
                document.getElementById('description').value = '{{ $category->description }}';
                
                // Mettre en surbrillance les valeurs actuelles
                selectIcon('{{ $category->icon }}');
                selectColor('{{ $category->color }}');
                
                updatePreview();
            }
        }
        
        // Gérer la suppression
        document.addEventListener('DOMContentLoaded', function() {
            // Synchroniser les champs de couleur
            syncColorInputs();
            
            // Mettre à jour l'aperçu initial
            updatePreview();
            
            // Écouter les changements
            const inputs = ['name', 'icon', 'description'];
            inputs.forEach(id => {
                const element = document.getElementById(id);
                if (element) {
                    element.addEventListener('input', updatePreview);
                }
            });
            
            // Gérer l'action de suppression
            const deleteModal = document.getElementById('delete-modal');
            if (deleteModal) {
                deleteModal.addEventListener('show', function() {
                    // Réinitialiser les options de suppression
                    document.querySelector('input[name="delete_action"][value="reassign"]').checked = true;
                    document.getElementById('delete-action-input').value = 'reassign';
                });
                
                // Mettre à jour l'action de suppression
                document.querySelectorAll('input[name="delete_action"]').forEach(radio => {
                    radio.addEventListener('change', function() {
                        document.getElementById('delete-action-input').value = this.value;
                    });
                });
            }
            
            // Focus sur le nom
            document.getElementById('name')?.focus();
        });
    </script>
    @endpush
</x-app-layout>