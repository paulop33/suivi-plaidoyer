# Fonctionnalité : Commentaires par Proposition et Commentaire Global de Liste

## Vue d'ensemble

Cette fonctionnalité sépare clairement les commentaires en deux niveaux :
1. **Commentaire global de la liste** : associé à la liste candidate elle-même
2. **Commentaires par proposition** : spécifiques à chaque engagement sur une proposition

## Problème résolu

Auparavant, la distinction entre commentaire global et commentaires spécifiques n'était pas claire. Cette refonte permet de :
- Associer un commentaire global à la liste candidate (indépendant des propositions)
- Ajouter des commentaires spécifiques à chaque engagement
- Supprimer des engagements lors de l'édition (désélection)

## Solution implémentée

### 1. Restructuration des commentaires

#### Entité CandidateList
- **Nouveau champ** : `globalComment` (TEXT, nullable)
- **Méthodes** : `getGlobalComment()` et `setGlobalComment()`
- **Usage** : Commentaire global associé à la liste candidate

#### Entité Commitment
- **Champ utilisé** : `commentCandidateList` (existant, renommé conceptuellement)
- **Usage** : Commentaire spécifique à l'engagement sur une proposition
- **Champ supprimé** : `commentProposition` (supprimé pour éviter la confusion)

#### Migrations
- `Version20250928172725` : Ajout initial de `comment_proposition` (annulé)
- `Version20250928173852` : Ajout de `global_comment` à `candidate_list` et suppression de `comment_proposition` de `commitment`

### 2. Interface utilisateur améliorée

#### Bouton "Commentaire" par proposition
- Petit bouton discret à côté de chaque proposition
- Icône : `fa-comment-plus` (devient `fa-comment-minus` quand ouvert)
- Classes CSS : `btn btn-outline-info btn-sm toggle-comment-btn`

#### Zone de commentaire
- Textarea qui apparaît/disparaît via JavaScript
- Limite de 1000 caractères avec compteur en temps réel
- Pré-remplie avec le commentaire existant si disponible
- Style : fond gris clair (`#f8f9fa`) pour la distinguer du reste

### 3. Logique de traitement

#### Côté contrôleur
- Récupération du commentaire global via `$request->request->get('global_comment')`
- Récupération des commentaires par proposition via `$request->request->all('proposition_comments')`
- Validation de la longueur (max 1000 caractères par commentaire)
- Sauvegarde différenciée :
  - **Commentaire global** : sauvegardé sur l'entité `CandidateList`
  - **Commentaires par proposition** : sauvegardés dans `commentCandidateList` de chaque `Commitment`
  - **Suppression d'engagements** : les propositions non sélectionnées voient leurs engagements supprimés

#### Côté JavaScript
- Gestion de l'affichage/masquage des zones de commentaire
- Compteur de caractères en temps réel avec codes couleur
- Validation avant soumission du formulaire

## Utilisation

### Accès à la fonctionnalité
1. Aller sur la page de détail d'une liste candidate
2. Cliquer sur "Engager sur toutes les propositions"
3. Pour chaque proposition, cliquer sur le bouton "Commentaire" pour révéler la zone de saisie

### Cas d'usage typiques

#### 1. Commentaire global + commentaires spécifiques
```
Commentaire global de la liste : "Notre liste s'engage sur un programme ambitieux"
Proposition A : "Priorité absolue pour notre liste"
Proposition B : "Sous réserve d'étude budgétaire"
```

#### 2. Commentaires spécifiques uniquement
```
Commentaire global de la liste : (vide)
Proposition A : "Engagement ferme"
Proposition B : "Engagement conditionnel"
```

#### 3. Suppression d'engagements
```
Avant : Engagé sur propositions A, B, C
Après : Sélection de A et C seulement
Résultat : Engagement sur B supprimé automatiquement
```

## Affichage des commentaires existants

### Dans l'interface de batch commitment
- Badge "Commentaire global" si `commentCandidateList` existe
- Badge "Commentaire spécifique" si `commentProposition` existe
- Pré-remplissage automatique des zones de texte

### Dans l'administration des engagements
- Nouveau champ "Commentaire spécifique" dans le CRUD des engagements
- Distinction claire entre commentaire global et commentaire spécifique

## Aspects techniques

### Base de données
```sql
ALTER TABLE commitment ADD comment_proposition TEXT DEFAULT NULL;
```

### Validation
- Commentaire global : max 1000 caractères
- Commentaire par proposition : max 1000 caractères chacun
- Validation côté client (JavaScript) et serveur (PHP)

### Performance
- Pas d'impact sur les requêtes existantes
- Champ nullable pour compatibilité ascendante
- Index non nécessaire (champ de type TEXT)

## Tests

### Tests automatisés
- `testBatchCommitmentWithPropositionComments()` : vérifie la sauvegarde des commentaires
- Vérification de la présence des éléments UI dans le template
- Validation des méthodes de l'entité Commitment

### Tests manuels recommandés
1. Créer un engagement avec commentaire global seul
2. Créer un engagement avec commentaires spécifiques seuls
3. Créer un engagement avec les deux types de commentaires
4. Modifier un engagement existant
5. Vérifier les limites de caractères
6. Tester l'interface JavaScript (affichage/masquage)

## Compatibilité

### Rétrocompatibilité
- ✅ Les engagements existants continuent de fonctionner
- ✅ Le commentaire global reste inchangé
- ✅ Pas de migration de données nécessaire

### Évolutions futures possibles
- Commentaires riches (HTML/Markdown)
- Historique des modifications de commentaires
- Commentaires par catégorie de propositions
- Export des commentaires dans les rapports

## Fichiers modifiés

### Entité
- `src/Entity/Commitment.php` : nouveau champ et méthodes

### Contrôleur
- `src/Controller/Admin/CandidateListCrudController.php` : traitement des commentaires
- `src/Controller/Admin/CommitmentCrudController.php` : affichage du nouveau champ

### Template
- `templates/admin/batch_commitment.html.twig` : interface utilisateur complète

### Migration
- `migrations/Version20250928172725.php` : ajout de la colonne

### Tests
- `tests/Controller/Admin/CandidateListCrudControllerTest.php` : tests mis à jour
