# Implémentation des boutons "Tout accepter" et "Tout refuser"

## Modifications apportées

### 1. Interface utilisateur (HTML)

**Fichier modifié :** `templates/admin/batch_commitment.html.twig`

**Ajout des boutons :**
- Bouton "Tout accepter" (vert) avec icône check-double
- Bouton "Tout refuser" (rouge) avec icône times-circle
- Positionnés dans un btn-group à droite du titre

```html
<div class="col-md-4 text-end">
    <div class="btn-group" role="group" aria-label="Actions rapides">
        <button type="button" class="btn btn-success btn-sm" id="acceptAllBtn">
            <i class="fas fa-check-double me-1"></i>Tout accepter
        </button>
        <button type="button" class="btn btn-danger btn-sm" id="refuseAllBtn">
            <i class="fas fa-times-circle me-1"></i>Tout refuser
        </button>
    </div>
</div>
```

### 2. Fonctionnalité JavaScript

**Fonctionnalités implémentées :**

#### Bouton "Tout accepter"
- Demande confirmation avant action
- Sélectionne tous les boutons radio "accepted"
- Déclenche les événements change pour afficher les zones de commentaire
- Met à jour le compteur de statuts

#### Bouton "Tout refuser"
- Demande confirmation avant action
- Sélectionne tous les boutons radio "refused"
- Déclenche les événements change pour afficher les zones de commentaire
- Met à jour le compteur de statuts

**Code JavaScript ajouté :**

```javascript
// Gestion des boutons "Tout accepter" et "Tout refuser"
const acceptAllBtn = document.getElementById('acceptAllBtn');
const refuseAllBtn = document.getElementById('refuseAllBtn');

acceptAllBtn.addEventListener('click', function() {
    if (confirm('Êtes-vous sûr de vouloir accepter toutes les propositions ?')) {
        const acceptedRadios = document.querySelectorAll('input[type="radio"][value="accepted"]');
        acceptedRadios.forEach(radio => {
            radio.checked = true;
            const event = new Event('change', { bubbles: true });
            radio.dispatchEvent(event);
        });
        updateStatusCounter();
    }
});

refuseAllBtn.addEventListener('click', function() {
    if (confirm('Êtes-vous sûr de vouloir refuser toutes les propositions ?')) {
        const refusedRadios = document.querySelectorAll('input[type="radio"][value="refused"]');
        refusedRadios.forEach(radio => {
            radio.checked = true;
            const event = new Event('change', { bubbles: true });
            radio.dispatchEvent(event);
        });
        updateStatusCounter();
    }
});
```

## Fonctionnalités

### ✅ Avantages
1. **Gain de temps** : Permet de définir rapidement le statut de toutes les propositions
2. **Confirmation** : Demande confirmation avant d'effectuer l'action
3. **Intégration** : S'intègre parfaitement avec le système existant
4. **Mise à jour automatique** : Met à jour le compteur de statuts en temps réel
5. **Zones de commentaire** : Affiche automatiquement les zones de commentaire pour les propositions sélectionnées

### 🎯 Cas d'usage
- **Acceptation en masse** : Quand une liste candidate accepte la plupart des propositions
- **Refus en masse** : Quand une liste candidate refuse la plupart des propositions
- **Point de départ** : Définir un statut par défaut puis ajuster individuellement

### 🔄 Workflow
1. L'utilisateur clique sur "Tout accepter" ou "Tout refuser"
2. Une boîte de confirmation apparaît
3. Si confirmé, tous les boutons radio correspondants sont sélectionnés
4. Les zones de commentaire s'affichent automatiquement
5. Le compteur de statuts se met à jour
6. L'utilisateur peut ensuite ajuster individuellement si nécessaire

## Tests recommandés

1. **Test fonctionnel** : Vérifier que les boutons sélectionnent bien tous les statuts
2. **Test de confirmation** : Vérifier que la confirmation fonctionne
3. **Test d'intégration** : Vérifier que les zones de commentaire s'affichent
4. **Test de compteur** : Vérifier que le compteur se met à jour correctement
5. **Test de soumission** : Vérifier que les données sont bien envoyées au serveur
