<dialog id="withdraw-funds-modal" class="modal">
    <div class="modal-box">
        <h3 class="font-bold text-lg mb-4">
            <i class="fas fa-minus-circle mr-2 text-error"></i>
            Retirer des fonds
        </h3>
        
        <form id="withdraw-funds-form">
            @csrf
            
            <div class="form-control mb-4">
                <label class="label">
                    <span class="label-text font-semibold">Montant à retirer (FDJ) *</span>
                    <span class="label-text-alt" id="max-amount-label">
                        Maximum disponible: <span id="max-amount">0</span> FDJ
                    </span>
                </label>
                <div class="relative">
                    <input type="number" 
                           name="amount" 
                           id="withdraw-amount"
                           step="0.01"
                           min="1"
                           required
                           placeholder="0.00"
                           class="input input-bordered w-full pl-10" />
                    <span class="absolute left-3 top-1/2 transform -translate-y-1/2">FDJ</span>
                </div>
                <div class="text-xs text-error mt-1 hidden" id="amount-error">
                    Le montant ne peut pas dépasser le solde disponible
                </div>
            </div>
            
            <div class="form-control mb-4">
                <label class="label">
                    <span class="label-text font-semibold">Date du retrait</span>
                </label>
                <input type="date" 
                       name="date" 
                       id="withdraw-date"
                       value="{{ date('Y-m-d') }}"
                       class="input input-bordered w-full" />
            </div>
            
            <div class="form-control mb-6">
                <label class="label">
                    <span class="label-text font-semibold">Motif du retrait *</span>
                    <span class="label-text-alt">Optionnel mais recommandé</span>
                </label>
                <select name="reason" class="select select-bordered w-full mb-2" id="reason-select">
                    <option value="">Sélectionnez un motif</option>
                    <option value="urgence">Urgence personnelle</option>
                    <option value="achat">Achat imprévu</option>
                    <option value="transfert">Transfert vers un autre compte</option>
                    <option value="dépense">Dépense nécessaire</option>
                    <option value="autre">Autre motif</option>
                </select>
                
                <input type="text" 
                       name="description" 
                       id="withdraw-description"
                       placeholder="Décrivez la raison du retrait..."
                       class="input input-bordered w-full mt-2" />
            </div>
            
            <!-- Conseils -->
            <div class="alert alert-warning mb-6">
                <i class="fas fa-exclamation-triangle"></i>
                <div>
                    <span class="font-bold">Conseil : </span>
                    <p class="text-sm">Ne retirez que ce dont vous avez vraiment besoin pour maintenir votre progression.</p>
                </div>
            </div>
            
            <!-- Boutons d'action rapide -->
            <div class="grid grid-cols-2 gap-2 mb-6" id="quick-withdraw-buttons">
                <!-- Les boutons seront générés dynamiquement par JavaScript -->
            </div>
            
            <!-- Impact sur la progression -->
            <div class="bg-base-200 rounded-lg p-4 mb-6 hidden" id="impact-section">
                <h4 class="font-bold mb-2">Impact sur votre objectif</h4>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span>Progression actuelle :</span>
                        <span id="current-progress">0%</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Progression après retrait :</span>
                        <span id="new-progress" class="font-bold">0%</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Objectif atteint dans :</span>
                        <span id="new-timeline">--</span>
                    </div>
                </div>
            </div>
            
            <div class="modal-action">
                <button type="button" onclick="closeWithdrawModal()" 
                        class="btn btn-ghost">
                    Annuler
                </button>
                <button type="submit" class="btn btn-error" id="submit-withdraw">
                    <i class="fas fa-check mr-2"></i>Confirmer le retrait
                </button>
            </div>
        </form>
    </div>
    
    <!-- Fermer en cliquant en dehors -->
    <form method="dialog" class="modal-backdrop">
        <button>close</button>
    </form>
</dialog>

@push('scripts')
<script>
    // Variables globales
    let currentGoalData = null;
    let maxWithdrawAmount = 0;
    
    // Ouvrir le modal avec les données de l'objectif
    function openWithdrawModal(goalId, goalName, currentAmount, targetAmount, progress) {
        currentGoalData = {
            id: goalId,
            name: goalName,
            current: parseFloat(currentAmount),
            target: parseFloat(targetAmount),
            progress: parseFloat(progress)
        };
        
        maxWithdrawAmount = currentGoalData.current;
        
        // Mettre à jour les informations dans le modal
        document.getElementById('max-amount').textContent = maxWithdrawAmount.toLocaleString('fr-FR');
        
        // Générer les boutons d'action rapide
        generateQuickWithdrawButtons();
        
        // Mettre à jour la section d'impact
        updateImpactSection(0);
        
        // Réinitialiser le formulaire
        document.getElementById('withdraw-amount').value = '';
        document.getElementById('withdraw-description').value = '';
        document.getElementById('reason-select').value = '';
        
        // Afficher le modal
        document.getElementById('withdraw-funds-modal').showModal();
    }
    
    // Générer les boutons de retrait rapide
    function generateQuickWithdrawButtons() {
        const container = document.getElementById('quick-withdraw-buttons');
        container.innerHTML = '';
        
        // Montants suggérés basés sur le solde disponible
        const suggestedAmounts = [
            { label: '10%', value: maxWithdrawAmount * 0.1 },
            { label: '25%', value: maxWithdrawAmount * 0.25 },
            { label: '50%', value: maxWithdrawAmount * 0.5 },
            { label: '75%', value: maxWithdrawAmount * 0.75 }
        ];
        
        // Filtrer les montants significatifs (au moins 1000 FDJ)
        const validAmounts = suggestedAmounts.filter(amt => amt.value >= 1000);
        
        // Ajouter aussi quelques montants fixes
        const fixedAmounts = [1000, 5000, 10000, 25000];
        
        // Combiner et limiter à 4 boutons
        const allAmounts = [...validAmounts, ...fixedAmounts.map(amt => ({ 
            label: amt.toLocaleString('fr-FR') + ' FDJ', 
            value: amt 
        }))].slice(0, 4);
        
        // Créer les boutons
        allAmounts.forEach(amount => {
            const button = document.createElement('button');
            button.type = 'button';
            button.className = 'btn btn-outline btn-error btn-sm';
            button.textContent = amount.label;
            button.onclick = () => setWithdrawAmount(amount.value);
            container.appendChild(button);
        });
    }
    
    // Définir le montant du retrait
    function setWithdrawAmount(amount) {
        const amountInput = document.getElementById('withdraw-amount');
        const roundedAmount = Math.round(amount * 100) / 100; // Arrondir à 2 décimales
        
        // S'assurer que le montant ne dépasse pas le maximum
        const safeAmount = Math.min(roundedAmount, maxWithdrawAmount);
        
        amountInput.value = safeAmount;
        validateWithdrawAmount(safeAmount);
        updateImpactSection(safeAmount);
    }
    
    // Valider le montant saisi
    function validateWithdrawAmount(amount) {
        const errorElement = document.getElementById('amount-error');
        const submitButton = document.getElementById('submit-withdraw');
        
        if (amount > maxWithdrawAmount) {
            errorElement.classList.remove('hidden');
            submitButton.disabled = true;
            return false;
        } else {
            errorElement.classList.add('hidden');
            submitButton.disabled = false;
            return true;
        }
    }
    
    // Mettre à jour la section d'impact
    function updateImpactSection(amount) {
        const impactSection = document.getElementById('impact-section');
        
        if (amount > 0 && currentGoalData) {
            impactSection.classList.remove('hidden');
            
            const newAmount = currentGoalData.current - amount;
            const newProgress = (newAmount / currentGoalData.target) * 100;
            
            document.getElementById('current-progress').textContent = 
                currentGoalData.progress.toFixed(1) + '%';
            document.getElementById('new-progress').textContent = 
                newProgress.toFixed(1) + '%';
            
            // Calculer l'impact sur le délai (simplifié)
            if (newProgress < 100) {
                document.getElementById('new-timeline').textContent = 
                    'Objectif retardé';
            } else {
                document.getElementById('new-timeline').textContent = 
                    'Objectif atteint !';
            }
            
            // Changer la couleur selon l'impact
            const newProgressElement = document.getElementById('new-progress');
            if (newProgress < 50) {
                newProgressElement.className = 'font-bold text-error';
            } else if (newProgress < 80) {
                newProgressElement.className = 'font-bold text-warning';
            } else {
                newProgressElement.className = 'font-bold text-success';
            }
        } else {
            impactSection.classList.add('hidden');
        }
    }
    
    // Écouter les changements de montant
    document.getElementById('withdraw-amount').addEventListener('input', function(e) {
        const amount = parseFloat(e.target.value) || 0;
        validateWithdrawAmount(amount);
        updateImpactSection(amount);
    });
    
    // Gérer la sélection du motif
    document.getElementById('reason-select').addEventListener('change', function(e) {
        const descriptionInput = document.getElementById('withdraw-description');
        const selectedReason = e.target.value;
        
        // Pré-remplir la description selon le motif
        const reasonDescriptions = {
            'urgence': 'Urgence personnelle nécessitant des fonds immédiats',
            'achat': 'Achat important non prévu initialement',
            'transfert': 'Transfert vers un autre compte ou objectif',
            'dépense': 'Dépense nécessaire pour couvrir des besoins essentiels',
            'autre': 'Autre raison non listée'
        };
        
        if (selectedReason && !descriptionInput.value) {
            descriptionInput.value = reasonDescriptions[selectedReason] || '';
        }
    });
    
    // Fermer le modal
    function closeWithdrawModal() {
        document.getElementById('withdraw-funds-modal').close();
        currentGoalData = null;
    }
    
    // Soumettre le formulaire
    document.getElementById('withdraw-funds-form').addEventListener('submit', function(e) {
        e.preventDefault();
        
        if (!currentGoalData) return;
        
        const formData = new FormData(this);
        const amount = parseFloat(formData.get('amount'));
        
        // Validation finale
        if (!validateWithdrawAmount(amount)) {
            showToast('error', 'Montant invalide');
            return;
        }
        
        if (amount <= 0) {
            showToast('error', 'Veuillez entrer un montant valide');
            return;
        }
        
        // Confirmation supplémentaire pour les gros retraits
        if (amount > currentGoalData.current * 0.5) {
            if (!confirm(`Attention : Vous allez retirer plus de 50% de votre épargne (${amount.toLocaleString('fr-FR')} FDJ).\n\nÊtes-vous vraiment sûr ?`)) {
                return;
            }
        }
        
        const submitBtn = document.getElementById('submit-withdraw');
        const originalText = submitBtn.innerHTML;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Traitement...';
        submitBtn.disabled = true;
        
        // Envoyer la requête AJAX
        axios.post(`/savings/${currentGoalData.id}/withdraw-funds`, {
            amount: amount,
            date: formData.get('date'),
            description: formData.get('description') || `Retrait: ${formData.get('reason') || 'Sans motif spécifié'}`,
            reason: formData.get('reason')
        })
        .then(response => {
            if (response.data.success) {
                showToast('success', response.data.message);
                closeWithdrawModal();
                
                // Rafraîchir la page après un délai
                setTimeout(() => {
                    window.location.reload();
                }, 1500);
            }
        })
        .catch(error => {
            let message = 'Une erreur est survenue lors du retrait';
            if (error.response?.data?.message) {
                message = error.response.data.message;
            } else if (error.response?.data?.errors) {
                message = Object.values(error.response.data.errors).flat().join(', ');
            }
            
            showToast('error', message);
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
        });
    });
    
    // Initialiser les événements quand le DOM est chargé
    document.addEventListener('DOMContentLoaded', function() {
        // Aujourd'hui par défaut pour la date
        document.getElementById('withdraw-date').value = new Date().toISOString().split('T')[0];
        
        // Validation en temps réel
        document.getElementById('withdraw-amount').addEventListener('blur', function() {
            const amount = parseFloat(this.value) || 0;
            if (amount > 0) {
                this.value = amount.toFixed(2);
            }
        });
    });
</script>
@endpush

@push('styles')
<style>
    #amount-error {
        transition: all 0.3s ease;
    }
    
    #impact-section {
        transition: all 0.3s ease;
    }
    
    /* Animation pour les boutons de retrait rapide */
    #quick-withdraw-buttons button {
        transition: all 0.2s ease;
    }
    
    #quick-withdraw-buttons button:hover {
        transform: translateY(-1px);
        box-shadow: 0 2px 4px rgba(239, 68, 68, 0.2);
    }
    
    /* Style pour le modal d'erreur */
    .modal-box {
        max-height: 80vh;
        overflow-y: auto;
    }
    
    /* Responsive */
    @media (max-width: 640px) {
        #quick-withdraw-buttons {
            grid-template-columns: repeat(2, 1fr);
        }
    }
</style>
@endpush