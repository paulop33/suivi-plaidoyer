# Fonctionnalité de Statut des Engagements

## Vue d'ensemble

La classe `Commitment` a été étendue pour inclure un système de statut obligatoire qui permet de suivre si un engagement est accepté ou refusé. Chaque engagement doit avoir un statut défini.

## Nouveaux éléments

### Énumération CommitmentStatus

Une nouvelle énumération `App\Enum\CommitmentStatus` a été créée avec deux valeurs :

- `ACCEPTED` ('accepted') - Engagement accepté
- `REFUSED` ('refused') - Engagement refusé

**Important** : Le statut est obligatoire - chaque engagement doit avoir soit le statut ACCEPTED soit REFUSED.

### Propriétés et méthodes ajoutées à Commitment

#### Nouvelle propriété
```php
#[ORM\Column(type: Types::STRING, enumType: CommitmentStatus::class, nullable: false)]
private CommitmentStatus $status;
```

#### Nouvelles méthodes
- `getStatus(): CommitmentStatus` - Récupère le statut (obligatoire)
- `setStatus(CommitmentStatus $status): static` - Définit le statut (obligatoire)
- `isAccepted(): bool` - Vérifie si l'engagement est accepté
- `isRefused(): bool` - Vérifie si l'engagement est refusé

## Utilisation

### Définir le statut d'un engagement

```php
use App\Entity\Commitment;
use App\Enum\CommitmentStatus;

$commitment = new Commitment();

// Accepter un engagement
$commitment->setStatus(CommitmentStatus::ACCEPTED);

// Refuser un engagement
$commitment->setStatus(CommitmentStatus::REFUSED);

// Remettre à null (équivalent à ignoré)
$commitment->setStatus(null);
```

### Vérifier le statut d'un engagement

```php
// Vérifications booléennes
if ($commitment->isAccepted()) {
    echo "L'engagement a été accepté";
}

if ($commitment->isRefused()) {
    echo "L'engagement a été refusé";
}



// Vérification directe du statut
switch ($commitment->getStatus()) {
    case CommitmentStatus::ACCEPTED:
        echo "Accepté";
        break;
    case CommitmentStatus::REFUSED:
        echo "Refusé";
        break;
}
```

### Interface d'administration

Le champ statut est maintenant disponible dans l'interface d'administration EasyAdmin avec :
- Un menu déroulant pour sélectionner le statut
- Des badges colorés pour l'affichage (vert pour accepté, rouge pour refusé)

## Base de données

### Migration
La migration `Version20250928204438` ajoute la colonne `status` à la table `commitment` :

```sql
ALTER TABLE commitment ADD status VARCHAR(255) DEFAULT NULL;
```

### Valeurs possibles
- `'accepted'` - Engagement accepté
- `'refused'` - Engagement refusé

**Note** : Le statut est obligatoire, NULL n'est plus accepté.

## Tests

Des tests unitaires ont été ajoutés pour vérifier :
- L'obligation d'avoir un statut défini
- La définition et récupération des statuts
- Les méthodes de vérification booléennes
- L'interface fluide des setters
- Les fonctionnalités de l'énumération

## Compatibilité

- **Migration requise** : Les engagements existants avec statut `null` devront être mis à jour avec un statut valide
- **Type-safe** : Utilisation d'une énumération PHP 8.1+ pour éviter les erreurs de valeurs
- **Extensible** : Facile d'ajouter de nouveaux statuts si nécessaire

## Fichiers modifiés

- `src/Entity/Commitment.php` - Ajout du champ status et des méthodes
- `src/Enum/CommitmentStatus.php` - Nouvelle énumération
- `src/Controller/Admin/CommitmentCrudController.php` - Interface d'administration
- `migrations/Version20250928204438.php` - Migration de base de données
- `tests/Entity/CommitmentTest.php` - Tests de l'entité
- `tests/Enum/CommitmentStatusTest.php` - Tests de l'énumération
