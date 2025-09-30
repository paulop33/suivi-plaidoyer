# Fonctionnalité de Spécificité et Attentes Communes

## Vue d'ensemble

Le système a été étendu pour gérer deux nouveaux concepts :

1. **Spécificités (Specificity)** : Permet de regrouper des villes ayant des caractéristiques communes (intra-rocade, extra-rocade, ville, campagne, etc.)
2. **Attentes communes vs spécifiques** : Les propositions peuvent être soit des attentes communes pour toutes les mairies, soit des attentes spécifiques liées à certaines spécificités

## Entité Specificity

### Description

L'entité `Specificity` représente une caractéristique géographique ou urbaine qui peut être partagée par plusieurs villes.

### Propriétés

- `id` : Identifiant unique
- `name` : Nom de la spécificité (ex: "Intra-rocade", "Extra-rocade", "Centre urbain")
- `slug` : Version URL-friendly du nom (généré automatiquement)
- `description` : Description détaillée de la spécificité
- `cities` : Collection des villes ayant cette spécificité (relation ManyToMany)
- `propositions` : Collection des propositions liées à cette spécificité (relation ManyToMany)

### Exemples de spécificités

Les fixtures créent automatiquement les spécificités suivantes :

- **Intra-rocade** : Communes situées à l'intérieur de la rocade bordelaise
- **Extra-rocade** : Communes situées à l'extérieur de la rocade bordelaise
- **Centre urbain** : Zones à forte densité urbaine
- **Périurbain** : Zones périphériques avec caractère mixte urbain/rural
- **Rive gauche** : Communes situées sur la rive gauche de la Garonne
- **Rive droite** : Communes situées sur la rive droite de la Garonne

## Relation City - Specificity

### Description

Une ville peut avoir plusieurs spécificités. Par exemple, Bordeaux a les spécificités :
- Intra-rocade
- Centre urbain
- Rive gauche

### Méthodes disponibles

```php
// Récupérer les spécificités d'une ville
$city->getSpecificities(): Collection

// Ajouter une spécificité à une ville
$city->addSpecificity(Specificity $specificity): static

// Retirer une spécificité d'une ville
$city->removeSpecificity(Specificity $specificity): static
```

## Attentes communes vs spécifiques

### Propriété isCommonExpectation

Chaque proposition possède maintenant une propriété booléenne `isCommonExpectation` :

- **true** (par défaut) : La proposition est une attente commune qui s'applique à toutes les mairies
- **false** : La proposition est spécifique et ne s'applique qu'aux villes ayant certaines spécificités

### Relation Proposition - Specificity

Lorsqu'une proposition n'est pas une attente commune (`isCommonExpectation = false`), elle peut être liée à une ou plusieurs spécificités.

### Méthodes disponibles

```php
// Vérifier si c'est une attente commune
$proposition->isCommonExpectation(): bool

// Définir si c'est une attente commune
$proposition->setIsCommonExpectation(bool $isCommonExpectation): static

// Récupérer les spécificités liées
$proposition->getSpecificities(): Collection

// Ajouter une spécificité
$proposition->addSpecificity(Specificity $specificity): static

// Retirer une spécificité
$proposition->removeSpecificity(Specificity $specificity): static

// Vérifier si une proposition s'applique à une ville
$proposition->appliesTo(City $city): bool
```

### Méthode appliesTo()

La méthode `appliesTo(City $city)` détermine si une proposition s'applique à une ville donnée :

```php
public function appliesTo(City $city): bool
{
    // Si c'est une attente commune, elle s'applique à toutes les villes
    if ($this->isCommonExpectation) {
        return true;
    }

    // Sinon, vérifier si la ville a au moins une spécificité en commun
    foreach ($this->specificities as $specificity) {
        if ($city->getSpecificities()->contains($specificity)) {
            return true;
        }
    }

    return false;
}
```

## Utilisation dans l'interface d'administration

### Gestion des spécificités

Un nouveau menu "Spécificités" est disponible dans l'interface d'administration EasyAdmin :

- Créer, modifier, supprimer des spécificités
- Voir les villes et propositions associées à chaque spécificité

### Gestion des villes

Dans le formulaire d'édition d'une ville :

- Champ "Spécificités" : Sélection multiple des spécificités de la ville
- Les spécificités sont affichées dans la liste des villes

### Gestion des propositions

Dans le formulaire d'édition d'une proposition :

- Case à cocher "Attente commune" : Indique si la proposition s'applique à toutes les mairies
- Champ "Spécificités" : Visible uniquement si "Attente commune" est décoché
  - Permet de sélectionner les spécificités auxquelles la proposition s'applique

## Base de données

### Tables créées

#### Table `specificity`
```sql
CREATE TABLE specificity (
    id SERIAL NOT NULL,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    description VARCHAR(255) DEFAULT NULL,
    PRIMARY KEY(id)
);
```

#### Table de liaison `city_specificity`
```sql
CREATE TABLE city_specificity (
    city_id INT NOT NULL,
    specificity_id INT NOT NULL,
    PRIMARY KEY(city_id, specificity_id),
    FOREIGN KEY (city_id) REFERENCES city (id) ON DELETE CASCADE,
    FOREIGN KEY (specificity_id) REFERENCES specificity (id) ON DELETE CASCADE
);
```

#### Table de liaison `proposition_specificity`
```sql
CREATE TABLE proposition_specificity (
    proposition_id INT NOT NULL,
    specificity_id INT NOT NULL,
    PRIMARY KEY(proposition_id, specificity_id),
    FOREIGN KEY (proposition_id) REFERENCES proposition (id) ON DELETE CASCADE,
    FOREIGN KEY (specificity_id) REFERENCES specificity (id) ON DELETE CASCADE
);
```

### Colonne ajoutée

#### Table `proposition`
```sql
ALTER TABLE proposition ADD is_common_expectation BOOLEAN DEFAULT true NOT NULL;
```

## Exemples d'utilisation

### Créer une proposition spécifique aux zones intra-rocade

```php
$proposition = new Proposition();
$proposition->setTitle('Développer les zones piétonnes en centre-ville');
$proposition->setIsCommonExpectation(false);

// Récupérer la spécificité "Intra-rocade"
$intraRocade = $specificityRepository->findOneBySlug('intra-rocade');
$proposition->addSpecificity($intraRocade);

$entityManager->persist($proposition);
$entityManager->flush();
```

### Vérifier si une proposition s'applique à une ville

```php
$city = $cityRepository->findOneBySlug('bordeaux');
$proposition = $propositionRepository->find(1);

if ($proposition->appliesTo($city)) {
    echo "Cette proposition s'applique à " . $city->getName();
} else {
    echo "Cette proposition ne s'applique pas à " . $city->getName();
}
```

### Filtrer les propositions applicables à une ville

```php
$city = $cityRepository->findOneBySlug('bordeaux');
$allPropositions = $propositionRepository->findAll();

$applicablePropositions = array_filter($allPropositions, function($proposition) use ($city) {
    return $proposition->appliesTo($city);
});
```

### Récupérer toutes les villes d'une spécificité

```php
$specificity = $specificityRepository->findOneBySlug('intra-rocade');
$cities = $specificity->getCities();

foreach ($cities as $city) {
    echo $city->getName() . "\n";
}
```

## Migration

La migration `Version20250930112648` crée toutes les tables et colonnes nécessaires.

Pour appliquer la migration :

```bash
php bin/console doctrine:migrations:migrate
```

Pour charger les données de test (spécificités et associations) :

```bash
php bin/console doctrine:fixtures:load
```

## Notes importantes

1. **Par défaut**, toutes les propositions sont des attentes communes (`isCommonExpectation = true`)
2. Une ville peut avoir **plusieurs spécificités**
3. Une proposition spécifique peut être liée à **plusieurs spécificités**
4. Une proposition s'applique à une ville si :
   - C'est une attente commune, OU
   - La ville a au moins une spécificité en commun avec la proposition
5. Les spécificités sont **optionnelles** : une ville peut ne pas avoir de spécificité
6. Le slug est **généré automatiquement** à partir du nom de la spécificité

## Évolutions futures possibles

- Ajouter un filtre dans l'interface publique pour afficher les propositions par spécificité
- Créer des statistiques par spécificité
- Permettre de créer des rapports comparatifs entre différentes spécificités
- Ajouter des règles métier plus complexes pour l'applicabilité des propositions

