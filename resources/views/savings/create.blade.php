<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between">
            <h2 class="text-2xl font-bold text-base-content">
                <i class="fas fa-plus-circle mr-3"></i>Nouvel objectif d'épargne
            </h2>
            <div class="mt-2 md:mt-0">
                <a href="{{ route('savings.index') }}" class="btn btn-ghost">
                    <i class="fas fa-arrow-left mr-2"></i>Retour
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-2xl mx-auto">
            <div class="card bg-base-100 shadow-lg">
                <div class="card-body">
                    <form method="POST" action="{{ route('savings.store') }}">
                        @csrf
                        
                        <div class="space-y-6">
                            <!-- Nom -->
                            <div class="form-control">
                                <label class="label">
                                    <span class="label-text font-semibold">Nom de l'objectif *</span>
                                </label>
                                <input type="text" 
                                       name="name" 
                                       value="{{ old('name') }}"
                                       placeholder="Ex: Voyage, Achat voiture..."
                                       class="input input-bordered w-full"
                                       required />
                                @error('name')
                                    <label class="label">
                                        <span class="label-text-alt text-error">{{ $message }}</span>
                                    </label>
                                @enderror
                            </div>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Montant cible -->
                                <div class="form-control">
                                    <label class="label">
                                        <span class="label-text font-semibold">Montant cible (FDJ) *</span>
                                    </label>
                                    <div class="relative">
                                        <input type="number" 
                                               name="target_amount" 
                                               value="{{ old('target_amount') }}"
                                               step="0.01"
                                               min="1"
                                               placeholder="0.00"
                                               class="input input-bordered w-full pl-10"
                                               required />
                                        <span class="absolute left-3 top-1/2 transform -translate-y-1/2">FDJ</span>
                                    </div>
                                    @error('target_amount')
                                        <label class="label">
                                            <span class="label-text-alt text-error">{{ $message }}</span>
                                        </label>
                                    @enderror
                                </div>
                                
                                <!-- Montant actuel -->
                                <div class="form-control">
                                    <label class="label">
                                        <span class="label-text font-semibold">Montant déjà économisé (FDJ)</span>
                                    </label>
                                    <div class="relative">
                                        <input type="number" 
                                               name="current_amount" 
                                               value="{{ old('current_amount', 0) }}"
                                               step="0.01"
                                               min="0"
                                               placeholder="0.00"
                                               class="input input-bordered w-full pl-10" />
                                        <span class="absolute left-3 top-1/2 transform -translate-y-1/2">FDJ</span>
                                    </div>
                                </div>
                                
                                <!-- Date limite -->
                                <div class="form-control">
                                    <label class="label">
                                        <span class="label-text font-semibold">Date limite</span>
                                    </label>
                                    <input type="date" 
                                           name="deadline" 
                                           value="{{ old('deadline') }}"
                                           min="{{ date('Y-m-d') }}"
                                           class="input input-bordered w-full" />
                                </div>
                                
                                <!-- Couleur -->
                                <div class="form-control">
                                    <label class="label">
                                        <span class="label-text font-semibold">Couleur</span>
                                    </label>
                                    <input type="color" 
                                           name="color" 
                                           value="{{ old('color', '#10B981') }}"
                                           class="w-full h-10 cursor-pointer" />
                                </div>
                            </div>
                            
                            <!-- Description -->
                            <div class="form-control">
                                <label class="label">
                                    <span class="label-text font-semibold">Description</span>
                                </label>
                                <textarea name="description" 
                                          rows="3"
                                          placeholder="Décrivez votre objectif..."
                                          class="textarea textarea-bordered w-full">{{ old('description') }}</textarea>
                            </div>
                            
                            <!-- Boutons -->
                            <div class="flex justify-end space-x-3 pt-6 border-t border-base-300">
                                <a href="{{ route('savings.index') }}" class="btn btn-ghost">
                                    Annuler
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save mr-2"></i>Créer l'objectif
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>