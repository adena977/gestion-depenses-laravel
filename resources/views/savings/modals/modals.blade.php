<!-- Modal d'ajout de fonds -->
<dialog id="add-contribution-modal" class="modal">
    <div class="modal-box">
        <h3 class="font-bold text-lg mb-4">Ajouter des fonds</h3>
        <form id="add-contribution-form" method="POST">
            @csrf
            <input type="hidden" name="goal_id" id="add-goal-id">
            
            <div class="form-control">
                <label class="label">
                    <span class="label-text">Montant à ajouter</span>
                </label>
                <div class="relative">
                    <input type="number" 
                           name="amount" 
                           id="add-amount"
                           step="1"
                           min="1"
                           required
                           class="input input-bordered w-full pr-12"
                           placeholder="0">
                    <span class="absolute right-3 top-1/2 transform -translate-y-1/2">FDJ</span>
                </div>
            </div>
            
            <div class="form-control mt-4">
                <label class="label">
                    <span class="label-text">Description (optionnel)</span>
                </label>
                <input type="text" 
                       name="description" 
                       class="input input-bordered w-full"
                       placeholder="Ex: Économies du mois, Cadeau...">
            </div>
            
            <div class="modal-action">
                <button type="button" onclick="document.getElementById('add-contribution-modal').close()" class="btn">
                    Annuler
                </button>
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-plus mr-2"></i>Ajouter
                </button>
            </div>
        </form>
    </div>
    <form method="dialog" class="modal-backdrop">
        <button>close</button>
    </form>
</dialog>

<!-- Modal de retrait -->
<dialog id="withdraw-modal" class="modal">
    <div class="modal-box">
        <h3 class="font-bold text-lg mb-4">Effectuer un retrait</h3>
        <form id="withdraw-form" method="POST">
            @csrf
            <input type="hidden" name="goal_id" id="withdraw-goal-id">
            
            <div class="form-control">
                <label class="label">
                    <span class="label-text">Montant à retirer</span>
                </label>
                <div class="relative">
                    <input type="number" 
                           name="amount" 
                           id="withdraw-amount"
                           step="1"
                           min="1"
                           required
                           class="input input-bordered w-full pr-12"
                           placeholder="0">
                    <span class="absolute right-3 top-1/2 transform -translate-y-1/2">FDJ</span>
                </div>
                <div class="text-sm mt-2 text-gray-500">
                    Solde disponible: <span id="available-balance">0</span> FDJ
                </div>
            </div>
            
            <div class="form-control mt-4">
                <label class="label">
                    <span class="label-text">Raison (optionnel)</span>
                </label>
                <input type="text" 
                       name="description" 
                       class="input input-bordered w-full"
                       placeholder="Ex: Urgence, Achat important...">
            </div>
            
            <div class="alert alert-warning mt-4">
                <i class="fas fa-exclamation-triangle"></i>
                <span class="text-sm">Le retrait sera déduit du montant actuellement économisé.</span>
            </div>
            
            <div class="modal-action">
                <button type="button" onclick="document.getElementById('withdraw-modal').close()" class="btn">
                    Annuler
                </button>
                <button type="submit" class="btn btn-warning">
                    <i class="fas fa-minus mr-2"></i>Retirer
                </button>
            </div>
        </form>
    </div>
    <form method="dialog" class="modal-backdrop">
        <button>close</button>
    </form>
</dialog>

<script>
function openAddContributionModal(goalId, goalName, currentAmount) {
    document.getElementById('add-goal-id').value = goalId;
    document.getElementById('add-contribution-form').action = `/savings/${goalId}/add`;
    document.getElementById('add-amount').focus();
    document.getElementById('add-contribution-modal').showModal();
}

function openWithdrawModal(goalId, goalName, currentAmount) {
    document.getElementById('withdraw-goal-id').value = goalId;
    document.getElementById('withdraw-form').action = `/savings/${goalId}/withdraw`;
    document.getElementById('available-balance').textContent = currentAmount.toLocaleString('fr-FR');
    document.getElementById('withdraw-amount').max = currentAmount;
    document.getElementById('withdraw-amount').focus();
    document.getElementById('withdraw-modal').showModal();
}

// Gérer la soumission des modals
document.getElementById('add-contribution-form')?.addEventListener('submit', function(e) {
    e.preventDefault();
    this.submit();
});

document.getElementById('withdraw-form')?.addEventListener('submit', function(e) {
    e.preventDefault();
    this.submit();
});
</script>