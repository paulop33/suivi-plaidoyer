# Mise à jour : Statut d'engagement obligatoire

## Résumé des changements

Le système de statut des engagements a été modifié pour **forcer les candidats à se positionner**. Le statut est maintenant **obligatoire** et ne peut plus être `null`.

## Modifications apportées

### 1. Entité Commitment

**Avant :**
```php
#[ORM\Column(type: Types::STRING, enumType: CommitmentStatus::class, nullable: true)]
private ?CommitmentStatus $status = null;

public function getStatus(): ?CommitmentStatus
public function setStatus(?CommitmentStatus $status): static
public function hasNoStatus(): bool
```

**Après :**
```php
#[ORM\Column(type: Types::STRING, enumType: CommitmentStatus::class, nullable: false)]
private CommitmentStatus $status;

public function getStatus(): CommitmentStatus
public function setStatus(CommitmentStatus $status): static
// hasNoStatus() supprimée
```

### 2. Énumération CommitmentStatus

- Suppression de la valeur `IGNORED`
- Seules les valeurs `ACCEPTED` et `REFUSED` sont disponibles
- Ajout de la méthode `fromValue()` pour conversion sécurisée

### 3. Interface utilisateur

**Template `batch_commitment.html.twig` :**
- Suppression de l'option "Aucun statut"
- Seules les options "Accepter" et "Refuser" sont disponibles
- Zone de commentaire s'ouvre automatiquement pour les deux statuts

### 4. Contrôleur

**`CandidateListCrudController.php` :**
- Suppression de la gestion du statut `IGNORED`
- Seuls les engagements avec statut `ACCEPTED` ou `REFUSED` sont créés
- Validation que le statut n'est jamais `null`

### 5. Interface d'administration

**`CommitmentCrudController.php` :**
- Suppression de l'option "Aucun statut" dans le CRUD
- Badges colorés uniquement pour `accepted` et `refused`

## Migration de base de données

Une nouvelle migration `Version20250928211759` a été créée :

```sql
ALTER TABLE commitment ALTER status SET NOT NULL;
```

⚠️ **Important** : Avant d'exécuter cette migration, assurez-vous que tous les engagements existants avec `status = NULL` ont été mis à jour avec un statut valide.

## Tests mis à jour

- Suppression des tests liés au statut `null` ou `ignored`
- Ajout de tests pour vérifier l'obligation du statut
- Tests de l'énumération mis à jour pour les deux valeurs uniquement

## Impact sur l'utilisation

### Pour les utilisateurs :
- **Obligation de choisir** : Chaque engagement doit être soit accepté soit refusé
- **Interface simplifiée** : Plus d'option "ignorer" qui pouvait créer de l'ambiguïté
- **Processus plus clair** : Force une prise de position explicite

### Pour les développeurs :
- **Type safety** : Plus de gestion des valeurs `null`
- **Code simplifié** : Moins de conditions à gérer
- **Logique métier claire** : Statut obligatoire = position obligatoire

## Fichiers modifiés

### Code source :
- `src/Entity/Commitment.php`
- `src/Enum/CommitmentStatus.php`
- `src/Controller/Admin/CandidateListCrudController.php`
- `src/Controller/Admin/CommitmentCrudController.php`

### Templates :
- `templates/admin/batch_commitment.html.twig`

### Tests :
- `tests/Entity/CommitmentTest.php`
- `tests/Enum/CommitmentStatusTest.php`

### Documentation :
- `docs/COMMITMENT_STATUS_FEATURE.md` (mis à jour)
- `docs/COMMITMENT_STATUS_MANDATORY_UPDATE.md` (nouveau)

### Migration :
- `migrations/Version20250928211759.php` (nouveau)

## Validation

✅ **Tests unitaires** : 10/10 passent  
✅ **Syntaxe PHP** : Aucune erreur  
✅ **Syntaxe Twig** : Template valide  
✅ **Migration** : Générée automatiquement  

## Prochaines étapes

1. **Exécuter la migration** : `php bin/console doctrine:migrations:migrate`
2. **Tester l'interface** : Vérifier le comportement dans l'admin
3. **Former les utilisateurs** : Expliquer le nouveau processus obligatoire

## Avantages de cette approche

- **Clarté métier** : Chaque engagement a une position claire
- **Données cohérentes** : Plus d'ambiguïté sur les statuts
- **Interface intuitive** : Choix binaire simple
- **Reporting précis** : Statistiques fiables sur les acceptations/refus
