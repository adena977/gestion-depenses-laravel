<dialog id="add-funds-modal" class="modal">
    <div class="modal-box">
        <h3 class="font-bold text-lg mb-4">
            <i class="fas fa-plus-circle mr-2 text-success"></i>
            Ajouter des fonds
        </h3>
        
        <form id="add-funds-form">
            @csrf
            
            <div class="form-control mb-4">
                <label class="label">
                    <span class="label-text font-semibold">Montant (FDJ) *</span>
                </label>
                <div class="relative">
                    <input type="number" 
                           name="amount" 
                           step="0.01"
                           min="1"
                           required
                           placeholder="0.00"
                           class="input input-bordered w-full pl-10" />
                    <span class="absolute left-3 top-1/2 transform -translate-y-1/2">FDJ</span>
                </div>
            </div>
            
            <div class="form-control mb-4">
                <label class="label">
                    <span class="label-text font-semibold">Date</span>
                </label>
                <input type="date" 
                       name="date" 
                       value="{{ date('Y-m-d') }}"
                       class="input input-bordered w-full" />
            </div>
            
            <div class="form-control mb-6">
                <label class="label">
                    <span class="label-text font-semibold">Description</span>
                    <span class="label-text-alt">Optionnel</span>
                </label>
                <input type="text" 
                       name="description" 
                       placeholder="Ex: Épargne mensuelle, Bonus..."
                       class="input input-bordered w-full" />
            </div>
            
            <!-- Boutons d'action rapide -->
            <div class="grid grid-cols-2 gap-2 mb-6">
                <button type="button" onclick="setAmount(1000)" class="btn btn-outline btn-sm">
                    1,000 FDJ
                </button>
                <button type="button" onclick="setAmount(5000)" class="btn btn-outline btn-sm">
                    5,000 FDJ
                </button>
                <button type="button" onclick="setAmount(10000)" class="btn btn-outline btn-sm">
                    10,000 FDJ
                </button>
                <button type="button" onclick="setAmount(50000)" class="btn btn-outline btn-sm">
                    50,000 FDJ
                </button>
            </div>
            
            <div class="modal-action">
                <button type="button" onclick="document.getElementById('add-funds-modal').close()" 
                        class="btn btn-ghost">
                    Annuler
                </button>
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-check mr-2"></i>Confirmer
                </button>
            </div>
        </form>
    </div>
    
    <!-- Fermer en cliquant en dehors -->
    <form method="dialog" class="modal-backdrop">
        <button>close</button>
    </form>
</dialog>

<script>
    function setAmount(amount) {
        document.querySelector('#add-funds-form input[name="amount"]').value = amount;
    }
</script>