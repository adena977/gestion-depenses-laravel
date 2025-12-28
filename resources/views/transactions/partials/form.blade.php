@csrf

@if(isset($transaction))
    @method('PUT')
@endif

<!-- Type de transaction (caché pour edit) -->
@if(!isset($transaction))
<div class="form-control mb-6">
    <label class="label">
        <span class="label-text font-semibold">Type de transaction *</span>
    </label>
    <div class="grid grid-cols-2 gap-4">
        <label class="cursor-pointer">
            <input type="radio" 
                   name="type" 
                   value="expense" 
                   class="radio radio-error" 
                   {{ (isset($transaction) && $transaction->type === 'expense') || old('type', 'expense') === 'expense' ? 'checked' : '' }} />
            <span class="ml-2 font-medium">Dépense</span>
        </label>
        <label class="cursor-pointer">
            <input type="radio" 
                   name="type" 
                   value="income" 
                   class="radio radio-success" 
                   {{ (isset($transaction) && $transaction->type === 'income') || old('type') === 'income' ? 'checked' : '' }} />
            <span class="ml-2 font-medium">Revenu</span>
        </label>
    </div>
</div>
@else
<input type="hidden" name="type" value="{{ $transaction->type }}">
@endif

<!-- Montant -->
<div class="form-control mb-4">
    <label class="label">
        <span class="label-text font-semibold">Montant *</span>
    </label>
    <div class="relative">
        <span class="absolute left-3 top-1/2 transform -translate-y-1/2 text-2xl font-bold 
            {{ (isset($transaction) && $transaction->type === 'expense') || old('type', 'expense') === 'expense' ? 'text-error' : 'text-success' }}">
            {{ (isset($transaction) && $transaction->type === 'expense') || old('type', 'expense') === 'expense' ? '-' : '+' }}
        </span>
        <input type="number" 
               name="amount" 
               value="{{ old('amount', $transaction->amount ?? '') }}"
               step="0.01"
               min="0.01"
               placeholder="0.00"
               class="input input-bordered w-full pl-10 text-2xl font-bold @error('amount') input-error @enderror"
               required />
        <span class="absolute right-3 top-1/2 transform -translate-y-1/2 text-xl">€</span>
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
    </label>
    <select name="category_id" class="select select-bordered w-full @error('category_id') select-error @enderror" required>
        <option value="">Sélectionnez une catégorie</option>
        @foreach($categories as $category)
            @if(!isset($transaction) || $category->type === $transaction->type)
                <option value="{{ $category->id }}" 
                    {{ old('category_id', $transaction->category_id ?? '') == $category->id ? 'selected' : '' }}
                    style="color: {{ $category->color }}">
                    <i class="fas fa-{{ $category->icon }} mr-2"></i>
                    {{ $category->name }}
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
    </label>
    <input type="text" 
           name="description" 
           value="{{ old('description', $transaction->description ?? '') }}"
           placeholder="Description de la transaction..."
           class="input input-bordered w-full" />
</div>

<!-- Date -->
<div class="form-control mb-4">
    <label class="label">
        <span class="label-text font-semibold">Date *</span>
    </label>
    <input type="date" 
           name="date" 
           value="{{ old('date', isset($transaction) ? $transaction->date->format('Y-m-d') : date('Y-m-d')) }}"
           class="input input-bordered w-full @error('date') input-error @enderror"
           required />
    @error('date')
        <label class="label">
            <span class="label-text-alt text-error">{{ $message }}</span>
        </label>
    @enderror
</div>

<!-- Mode de paiement -->
<div class="form-control mb-4">
    <label class="label">
        <span class="label-text font-semibold">Mode de paiement</span>
    </label>
    <select name="payment_method" class="select select-bordered w-full">
        <option value="">Sélectionnez</option>
        <option value="cash" {{ old('payment_method', $transaction->payment_method ?? '') == 'cash' ? 'selected' : '' }}>Espèces</option>
        <option value="card" {{ old('payment_method', $transaction->payment_method ?? '') == 'card' ? 'selected' : '' }}>Carte bancaire</option>
        <option value="transfer" {{ old('payment_method', $transaction->payment_method ?? '') == 'transfer' ? 'selected' : '' }}>Virement</option>
        <option value="mobile_money" {{ old('payment_method', $transaction->payment_method ?? '') == 'mobile_money' ? 'selected' : '' }}>Mobile Money</option>
    </select>
</div>

<!-- Lieu -->
<div class="form-control mb-6">
    <label class="label">
        <span class="label-text font-semibold">Lieu</span>
    </label>
    <input type="text" 
           name="location" 
           value="{{ old('location', $transaction->location ?? '') }}"
           placeholder="Lieu de la transaction..."
           class="input input-bordered w-full" />
</div>

<!-- Boutons -->
<div class="form-control pt-6 border-t border-base-300">
    <div class="flex justify-between">
        <a href="{{ route('transactions.index') }}" class="btn btn-ghost">Annuler</a>
        <button type="submit" class="btn btn-primary">
            <i class="fas fa-save mr-2"></i>
            {{ isset($transaction) ? 'Mettre à jour' : 'Créer' }}
        </button>
    </div>
</div>