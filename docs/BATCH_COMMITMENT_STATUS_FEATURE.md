# Fonctionnalité de Gestion des Statuts dans Batch Commitment

## Vue d'ensemble

La fonctionnalité de gestion en lot des engagements a été étendue pour permettre aux administrateurs de définir le statut de chaque engagement (accepté, refusé, ignoré) directement depuis l'interface de gestion en masse.

## Nouvelles fonctionnalités

### 1. Statuts d'engagement

Pour chaque proposition, trois options sont disponibles :

- **Accepter** (`accepted`) : La liste candidate accepte cette proposition
  - Badge vert avec icône ✓
  - Crée un engagement avec statut `ACCEPTED`

- **Refuser** (`refused`) : La liste candidate refuse cette proposition  
  - Badge rouge avec icône ✗
  - Crée un engagement avec statut `REFUSED`

- **Ignorer** (`ignored`) : La liste candidate ignore cette proposition
  - Badge gris avec icône −
  - Crée un engagement avec statut `IGNORED` ou ne crée pas d'engagement

### 2. Interface utilisateur améliorée et simplifiée

#### Sélection de statut prioritaire
- **Plus de checkbox** : Le statut est maintenant le premier élément d'interaction
- **Ouverture automatique** : Sélectionner "Accepter" ou "Refuser" ouvre automatiquement la zone de commentaire
- **Compteur en temps réel** : Affichage du nombre de caractères saisis (X/1000 caractères)
- **Lien vers la proposition** : Bouton "Voir" pour accéder à la page publique de la proposition

#### Affichage des statuts existants
- Les engagements existants affichent leur statut actuel avec des badges colorés
- Les nouveaux engagements sont marqués comme "Nouveau"

#### Zone d'options par proposition
- Bouton "Options" pour chaque proposition
- Zone dépliable contenant :
  - Boutons radio pour choisir le statut
  - Zone de commentaire spécifique à la proposition

#### Logique de sélection
- Les propositions cochées sans statut explicite sont considérées comme "acceptées"
- Les propositions avec un statut défini sont traitées même si elles ne sont pas cochées
- Permet de refuser ou ignorer des propositions sans les cocher

### 3. Traitement backend

#### Logique de traitement
```php
// Traiter toutes les propositions qui ont un statut défini ou qui sont sélectionnées
$allPropositionsToProcess = array_unique(array_merge($selectedPropositions, array_keys($propositionStatuses)));

// Si la proposition est sélectionnée mais n'a pas de statut défini, on considère qu'elle est acceptée
if (in_array($propositionId, $selectedPropositions) && !isset($propositionStatuses[$propositionId])) {
    $propositionStatus = 'accepted';
}
```

#### Gestion des engagements
- **Création** : Nouveaux engagements créés seulement si le statut n'est pas "ignored"
- **Mise à jour** : Engagements existants mis à jour avec le nouveau statut et commentaire
- **Suppression** : Engagements non traités sont supprimés

## Utilisation

### 1. Accès à la fonctionnalité
1. Aller dans Admin → Listes
2. Sélectionner une liste candidate
3. Cliquer sur "Gérer les engagements"

### 2. Définir les statuts
1. Pour chaque proposition, sélectionner directement le statut souhaité : Accepter / Refuser / Ignorer
2. La zone de commentaire s'ouvre automatiquement pour les statuts "Accepter" et "Refuser"
3. Saisir un commentaire si nécessaire (compteur de caractères affiché en temps réel)
4. Utiliser le bouton "Voir" pour consulter la proposition sur le site public
5. Cliquer sur "Enregistrer les statuts"

### 3. Cas d'usage

#### Acceptation avec commentaire
- Sélectionner "Accepter" pour une proposition
- La zone de commentaire s'ouvre automatiquement
- Ajouter un commentaire expliquant l'acceptation

#### Refus explicite
- Sélectionner "Refuser" pour une proposition
- La zone de commentaire s'ouvre automatiquement
- Ajouter un commentaire expliquant le refus

#### Gestion mixte
- Accepter certaines propositions (sélectionner "Accepter")
- Refuser d'autres (sélectionner "Refuser" avec commentaire)
- Ignorer le reste (sélectionner "Ignorer" ou ne rien sélectionner)

## Aspects techniques

### Base de données
- Utilise le champ `status` de l'entité `Commitment`
- Valeurs possibles : `'accepted'`, `'refused'`, `'ignored'`, `NULL`

### Validation
- Statuts validés côté serveur avec l'énumération `CommitmentStatus`
- Commentaires limités à 1000 caractères
- Gestion des erreurs avec messages flash

### Performance
- Traitement en lot avec transaction
- Requêtes optimisées pour éviter N+1
- Mise à jour seulement si changements détectés

## Rétrocompatibilité

- Les engagements existants sans statut sont traités comme "ignorés"
- L'ancienne logique de sélection par checkbox reste fonctionnelle
- Interface progressive : les options avancées sont masquées par défaut

## Tests

### Tests unitaires
- `CommitmentTest` : Tests des méthodes de statut
- `CommitmentStatusTest` : Tests de l'énumération

### Tests d'intégration
- `CandidateListCrudControllerBatchCommitmentTest` : Tests du contrôleur
- Scénarios de création, mise à jour et suppression d'engagements

## Fichiers modifiés

### Templates
- `templates/admin/batch_commitment.html.twig` : Interface utilisateur étendue

### Contrôleurs
- `src/Controller/Admin/CandidateListCrudController.php` : Logique de traitement des statuts

### Documentation
- `docs/BATCH_COMMITMENT_GUIDE.md` : Guide utilisateur mis à jour
- `docs/BATCH_COMMITMENT_STATUS_FEATURE.md` : Documentation technique

### Tests
- `tests/Controller/Admin/CandidateListCrudControllerBatchCommitmentTest.php` : Tests fonctionnels

## Évolutions futures possibles

- Historique des changements de statut
- Notifications automatiques lors de refus
- Rapports par statut d'engagement
- API REST pour gestion programmatique
- Workflow d'approbation multi-niveaux
