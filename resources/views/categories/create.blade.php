<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between">
            <div>
                <h2 class="text-2xl font-bold text-base-content">
                    <i class="fas fa-plus-circle mr-3"></i>Nouvelle catégorie
                </h2>
                <div class="breadcrumbs text-sm mt-1">
                    <ul>
                        <li><a href="{{ route('dashboard') }}"><i class="fas fa-home"></i> Dashboard</a></li>
                        <li><a href="{{ route('categories.index') }}">Catégories</a></li>
                        <li>Nouvelle</li>
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
            <!-- Type de catégorie -->
            <div class="card bg-base-100 shadow-lg mb-6">
                <div class="card-body">
                    <h3 class="card-title mb-4">Type de catégorie</h3>
                    <div class="grid grid-cols-2 gap-4">
                        <button type="button" 
                                id="btn-expense" 
                                class="btn btn-outline btn-error btn-lg {{ old('type', request('type', 'expense')) === 'expense' ? 'btn-active' : '' }}"
                                onclick="setCategoryType('expense')">
                            <i class="fas fa-arrow-down text-2xl mr-3"></i>
                            <div class="text-left">
                                <div class="font-bold">Dépense</div>
                                <div class="text-xs">Pour les sorties d'argent</div>
                            </div>
                        </button>
                        
                        <button type="button" 
                                id="btn-income" 
                                class="btn btn-outline btn-success btn-lg {{ old('type', request('type')) === 'income' ? 'btn-active' : '' }}"
                                onclick="setCategoryType('income')">
                            <i class="fas fa-arrow-up text-2xl mr-3"></i>
                            <div class="text-left">
                                <div class="font-bold">Revenu</div>
                                <div class="text-xs">Pour les entrées d'argent</div>
                            </div>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Formulaire -->
            <div class="card bg-base-100 shadow-lg">
                <div class="card-body">
                    <form method="POST" action="{{ route('categories.store') }}" id="category-form">
                        @csrf
                        
                        <!-- Champ caché pour le type -->
                        <input type="hidden" name="type" id="category-type" value="{{ old('type', request('type', 'expense')) }}">
                        
                        <!-- Nom -->
                        <div class="form-control mb-6">
                            <label class="label">
                                <span class="label-text font-semibold">Nom de la catégorie *</span>
                            </label>
                            <input type="text" 
                                   name="name" 
                                   id="name"
                                   value="{{ old('name') }}"
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
                                             style="background-color: {{ old('color', '#3B82F6') }}"></div>
                                        <input type="color" 
                                               name="color" 
                                               id="color"
                                               value="{{ old('color', '#3B82F6') }}"
                                               class="w-24 h-12 cursor-pointer"
                                               title="Choisir une couleur" />
                                    </div>
                                    <input type="text" 
                                           name="color_text" 
                                           id="color-text"
                                           value="{{ old('color', '#3B82F6') }}"
                                           placeholder="#3B82F6"
                                           class="input input-bordered w-full"
                                           pattern="^#[0-9A-Fa-f]{6}$" />
                                    <div class="text-xs text-base-content/50">
                                        Entrez une valeur hexadécimale (#RRGGBB) ou utilisez le sélecteur
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
                                             style="background-color: {{ old('color', '#3B82F6') }}20">
                                            <i id="icon-preview-symbol" 
                                               class="fas fa-{{ old('icon', 'receipt') }} text-xl"
                                               style="color: {{ old('color', '#3B82F6') }}"></i>
                                        </div>
                                        <input type="text" 
                                               name="icon" 
                                               id="icon"
                                               value="{{ old('icon', 'receipt') }}"
                                               placeholder="Nom de l'icône (sans 'fa-')"
                                               class="input input-bordered w-full" />
                                    </div>
                                    <div class="text-xs text-base-content/50">
                                        Entrez le nom de l'icône FontAwesome (ex: "shopping-cart", "car", "home")
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
                                    $currentType = old('type', request('type', 'expense'));
                                @endphp
                                
                                @foreach($suggestedIcons[$currentType] as $icon)
                                    <button type="button" 
                                            class="btn btn-ghost btn-square aspect-square"
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
                                            class="btn btn-ghost btn-square aspect-square p-0"
                                            onclick="selectColor('{{ $color }}')"
                                            title="{{ $color }}">
                                        <div class="w-full h-full rounded" style="background-color: {{ $color }}"></div>
                                    </button>
                                @endforeach
                            </div>
                        </div>
                        
                        <!-- Description (optionnelle) -->
                        <div class="form-control mb-8">
                            <label class="label">
                                <span class="label-text font-semibold">Description</span>
                                <span class="label-text-alt">Optionnel</span>
                            </label>
                            <textarea name="description" 
                                      id="description"
                                      rows="3"
                                      placeholder="Description facultative de la catégorie..."
                                      class="textarea textarea-bordered w-full">{{ old('description') }}</textarea>
                        </div>
                        
                        <!-- Boutons d'action -->
                        <div class="flex justify-between items-center pt-6 border-t border-base-300">
                            <a href="{{ route('categories.index') }}" class="btn btn-ghost">
                                <i class="fas fa-times mr-2"></i>Annuler
                            </a>
                            <div class="flex space-x-2">
                                <button type="button" onclick="resetForm()" class="btn btn-outline">
                                    <i class="fas fa-redo mr-2"></i>Réinitialiser
                                </button>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save mr-2"></i>Créer la catégorie
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Aperçu -->
            <div class="card bg-base-100 shadow-lg mt-6">
                <div class="card-body">
                    <h3 class="card-title mb-4">Aperçu de la catégorie</h3>
                    <div id="category-preview" class="space-y-4">
                        <!-- Carte d'aperçu -->
                        <div class="border border-base-300 rounded-lg p-6">
                            <div class="flex items-center justify-between mb-4">
                                <div class="flex items-center">
                                    <div id="preview-icon-container" 
                                         class="w-16 h-16 rounded-xl flex items-center justify-center mr-4"
                                         style="background-color: {{ old('color', '#3B82F6') }}20">
                                        <i id="preview-icon-display" 
                                           class="fas fa-{{ old('icon', 'receipt') }} text-2xl"
                                           style="color: {{ old('color', '#3B82F6') }}"></i>
                                    </div>
                                    <div>
                                        <div id="preview-name" class="text-2xl font-bold">
                                            {{ old('name', 'Nom de la catégorie') }}
                                        </div>
                                        <div id="preview-type" class="text-lg {{ old('type', request('type', 'expense')) === 'expense' ? 'text-error' : 'text-success' }}">
                                            {{ old('type', request('type', 'expense')) === 'expense' ? 'Dépense' : 'Revenu' }}
                                        </div>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <div class="text-sm text-base-content/70">Nouvelle catégorie</div>
                                    <div class="text-xs text-base-content/50">0 transaction</div>
                                </div>
                            </div>
                            
                            <!-- Description -->
                            <div id="preview-description" class="mt-4 p-3 bg-base-200 rounded-lg">
                                @if(old('description'))
                                    <p class="text-base-content/70">{{ old('description') }}</p>
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
                                             style="background-color: {{ old('color', '#3B82F6') }}"></div>
                                        <span id="preview-color-value">{{ old('color', '#3B82F6') }}</span>
                                    </div>
                                </div>
                                <div>
                                    <div class="font-semibold">Icône</div>
                                    <div id="preview-icon-name" class="flex items-center">
                                        <i class="fas fa-{{ old('icon', 'receipt') }} mr-2"></i>
                                        {{ old('icon', 'receipt') }}
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Exemple d'utilisation -->
                        <div class="border border-base-300 rounded-lg p-4">
                            <h4 class="font-bold mb-3">Exemple d'utilisation</h4>
                            <div class="flex items-center justify-between p-3 bg-base-200 rounded-lg">
                                <div class="flex items-center">
                                    <div class="w-10 h-10 rounded-lg flex items-center justify-center mr-3" 
                                         style="background-color: {{ old('color', '#3B82F6') }}20">
                                        <i class="fas fa-{{ old('icon', 'receipt') }}" 
                                           style="color: {{ old('color', '#3B82F6') }}"></i>
                                    </div>
                                    <div>
                                        <div class="font-medium">Transaction exemple</div>
                                        <div class="text-xs text-base-content/70">
                                            <span id="preview-example-type">{{ old('type', request('type', 'expense')) === 'expense' ? 'Dépense' : 'Revenu' }}</span> - {{ date('d/m/Y') }}
                                        </div>
                                    </div>
                                </div>
                                <div class="font-bold {{ old('type', request('type', 'expense')) === 'expense' ? 'text-error' : 'text-success' }}">
                                    {{ old('type', request('type', 'expense')) === 'expense' ? '-' : '+' }}50,00 FDJ
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
        .btn-active {
            @apply border-2;
        }
        
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
        // Définir le type de catégorie
        function setCategoryType(type) {
            document.getElementById('category-type').value = type;
            
            // Mettre à jour les boutons
            document.getElementById('btn-expense').classList.toggle('btn-active', type === 'expense');
            document.getElementById('btn-income').classList.toggle('btn-active', type === 'income');
            
            // Mettre à jour l'aperçu
            updatePreview();
            
            // Changer les icônes suggérées selon le type
            updateSuggestedIcons(type);
        }
        
        // Sélectionner une icône
        function selectIcon(icon) {
            document.getElementById('icon').value = icon;
            updatePreview();
        }
        
        // Sélectionner une couleur
        function selectColor(color) {
            document.getElementById('color').value = color;
            document.getElementById('color-text').value = color;
            updatePreview();
        }
        
        // Mettre à jour les icônes suggérées selon le type
        function updateSuggestedIcons(type) {
            // Cette fonction serait plus complexe en réalité
            // Pour l'instant, on met juste à jour l'exemple
            updatePreview();
        }
        
        // Mettre à jour l'aperçu en temps réel
        function updatePreview() {
            const type = document.getElementById('category-type').value;
            const name = document.getElementById('name').value || 'Nom de la catégorie';
            const color = document.getElementById('color').value;
            const icon = document.getElementById('icon').value || 'receipt';
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
            
            // Mettre à jour le type
            const typeElement = document.getElementById('preview-type');
            typeElement.textContent = type === 'expense' ? 'Dépense' : 'Revenu';
            typeElement.className = `text-lg ${type === 'expense' ? 'text-error' : 'text-success'}`;
            
            // Mettre à jour l'exemple
            document.getElementById('preview-example-type').textContent = type === 'expense' ? 'Dépense' : 'Revenu';
            
            // Mettre à jour la description
            const descriptionElement = document.getElementById('preview-description');
            if (description) {
                descriptionElement.innerHTML = `<p class="text-base-content/70">${description}</p>`;
            } else {
                descriptionElement.innerHTML = '<p class="text-base-content/50 italic">Aucune description</p>';
            }
            
            // Mettre à jour l'exemple d'utilisation
            const exampleIcon = document.querySelector('#category-preview .bg-base-200 .rounded-lg i');
            const exampleIconContainer = exampleIcon.parentElement;
            const exampleAmount = document.querySelector('#category-preview .bg-base-200 .font-bold');
            
            if (exampleIcon && exampleIconContainer && exampleAmount) {
                exampleIconContainer.style.backgroundColor = `${color}20`;
                exampleIcon.className = `fas fa-${icon}`;
                exampleIcon.style.color = color;
                exampleAmount.className = `font-bold ${type === 'expense' ? 'text-error' : 'text-success'}`;
                exampleAmount.textContent = `${type === 'expense' ? '-' : '+'}50,00 FDJ`;
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
            if (confirm('Voulez-vous vraiment réinitialiser le formulaire ?')) {
                document.getElementById('category-form').reset();
                document.getElementById('color').value = '#3B82F6';
                document.getElementById('color-text').value = '#3B82F6';
                document.getElementById('icon').value = 'receipt';
                setCategoryType('expense');
                updatePreview();
            }
        }
        
        // Initialiser
        document.addEventListener('DOMContentLoaded', function() {
            // Initialiser le type
            setCategoryType('{{ old('type', request('type', 'expense')) }}');
            
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
            
            // Focus sur le nom
            document.getElementById('name')?.focus();
        });
    </script>
    @endpush
</x-app-layout>