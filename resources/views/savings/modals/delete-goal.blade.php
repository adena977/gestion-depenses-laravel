<dialog id="delete-goal-modal" class="modal">
    <div class="modal-box">
        <h3 class="font-bold text-lg mb-4">
            <i class="fas fa-exclamation-triangle mr-2 text-error"></i>
            Confirmer la suppression
        </h3>
        
        <div class="alert alert-error mb-6">
            <i class="fas fa-exclamation-circle"></i>
            <div>
                <span class="font-bold">Attention !</span>
                <p class="text-sm">Cette action est irréversible. Toutes les données de cet objectif seront définitivement supprimées.</p>
            </div>
        </div>
        
        <div class="mb-6">
            <p>Êtes-vous sûr de vouloir supprimer l'objectif :</p>
            <p class="font-bold text-xl mt-2" id="goal-to-delete">{{ $savingsGoal->name ?? '' }}</p>
        </div>
        
        <div class="modal-action">
            <button onclick="document.getElementById('delete-goal-modal').close()" 
                    class="btn btn-ghost">
                Annuler
            </button>
            <button onclick="confirmDeleteGoal()" class="btn btn-error">
                <i class="fas fa-trash mr-2"></i>Supprimer définitivement
            </button>
        </div>
    </div>
    
    <!-- Fermer en cliquant en dehors -->
    <form method="dialog" class="modal-backdrop">
        <button>close</button>
    </form>
</dialog>