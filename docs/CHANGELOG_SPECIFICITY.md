# Changelog - Ajout des Spécificités et Attentes Communes

## Date : 30 septembre 2025

## Résumé des changements

Ajout de deux nouvelles fonctionnalités majeures au système de suivi de plaidoyer :

1. **Spécificités (Specificity)** : Permet de regrouper des villes selon des caractéristiques communes
2. **Attentes communes vs spécifiques** : Les propositions peuvent maintenant être soit communes à toutes les mairies, soit spécifiques à certaines spécificités

## Fichiers créés

### Entités
- `src/Entity/Specificity.php` - Nouvelle entité pour gérer les spécificités

### Repositories
- `src/Repository/SpecificityRepository.php` - Repository pour l'entité Specificity

### Contrôleurs
- `src/Controller/Admin/SpecificityCrudController.php` - Contrôleur CRUD pour l'administration des spécificités

### Migrations
- `migrations/Version20250930112648.php` - Migration pour créer les tables et colonnes nécessaires

### Documentation
- `docs/SPECIFICITY_FEATURE.md` - Documentation complète de la fonctionnalité
- `docs/CHANGELOG_SPECIFICITY.md` - Ce fichier

## Fichiers modifiés

### Entités
- `src/Entity/City.php`
  - Ajout de la relation ManyToMany avec Specificity
  - Ajout des méthodes `getSpecificities()`, `addSpecificity()`, `removeSpecificity()`

- `src/Entity/Proposition.php`
  - Ajout de la propriété `isCommonExpectation` (boolean)
  - Ajout de la relation ManyToMany avec Specificity
  - Ajout des méthodes pour gérer les spécificités
  - Ajout de la méthode `appliesTo(City $city)` pour vérifier l'applicabilité

### Contrôleurs
- `src/Controller/Admin/DashboardController.php`
  - Ajout de l'import de l'entité Specificity
  - Ajout du menu "Spécificités" dans l'interface d'administration

- `src/Controller/Admin/CityCrudController.php`
  - Ajout du champ "Spécificités" dans le formulaire
  - Configuration de l'affichage des spécificités

- `src/Controller/Admin/PropositionCrudController.php`
  - Ajout du champ "Attente commune" (checkbox)
  - Ajout du champ "Spécificités" (sélection multiple)
  - Ajout de l'aide contextuelle pour ces champs

### Fixtures
- `src/DataFixtures/AppFixtures.php`
  - Ajout de la méthode `createSpecificities()` pour créer les spécificités de base
  - Ajout de la méthode `associateSpecificitiesToCities()` pour associer les spécificités aux villes
  - Ajout de la méthode `associateSpecificitiesToPropositions()` (préparée pour usage futur)
  - Modification de `createCities()` pour retourner un tableau associatif
  - Modification de `createCategoriesAndPropositions()` pour retourner les propositions

### Migrations
- `migrations/Version20250928204438.php`
  - Correction pour utiliser `IF EXISTS` et `IF NOT EXISTS` pour éviter les erreurs

## Structure de la base de données

### Nouvelles tables

#### `specificity`
```sql
CREATE TABLE specificity (
    id SERIAL NOT NULL,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    description VARCHAR(255) DEFAULT NULL,
    PRIMARY KEY(id)
);
```

#### `city_specificity` (table de liaison)
```sql
CREATE TABLE city_specificity (
    city_id INT NOT NULL,
    specificity_id INT NOT NULL,
    PRIMARY KEY(city_id, specificity_id)
);
```

#### `proposition_specificity` (table de liaison)
```sql
CREATE TABLE proposition_specificity (
    proposition_id INT NOT NULL,
    specificity_id INT NOT NULL,
    PRIMARY KEY(proposition_id, specificity_id)
);
```

### Colonnes ajoutées

#### Table `proposition`
- `is_common_expectation` (BOOLEAN, DEFAULT true, NOT NULL)

## Spécificités créées par défaut

Les fixtures créent automatiquement 6 spécificités :

1. **Intra-rocade** - Communes situées à l'intérieur de la rocade bordelaise
2. **Extra-rocade** - Communes situées à l'extérieur de la rocade bordelaise
3. **Centre urbain** - Zones à forte densité urbaine
4. **Périurbain** - Zones périphériques avec caractère mixte urbain/rural
5. **Rive gauche** - Communes situées sur la rive gauche de la Garonne
6. **Rive droite** - Communes situées sur la rive droite de la Garonne

## Associations villes-spécificités

Les 28 communes de Bordeaux Métropole ont été associées à leurs spécificités respectives :

### Exemples
- **Bordeaux** : Intra-rocade, Centre urbain, Rive gauche
- **Cenon** : Intra-rocade, Centre urbain, Rive droite
- **Pessac** : Extra-rocade, Périurbain, Rive gauche
- **Artigues-près-Bordeaux** : Extra-rocade, Périurbain, Rive droite

## Fonctionnalités principales

### 1. Gestion des spécificités
- Création, modification, suppression de spécificités
- Visualisation des villes et propositions associées
- Interface d'administration dédiée

### 2. Association ville-spécificité
- Une ville peut avoir plusieurs spécificités
- Sélection multiple dans le formulaire d'édition de ville
- Affichage des spécificités dans la liste des villes

### 3. Propositions communes vs spécifiques
- Par défaut, toutes les propositions sont des attentes communes
- Possibilité de marquer une proposition comme spécifique
- Association d'une proposition spécifique à une ou plusieurs spécificités

### 4. Vérification d'applicabilité
- Méthode `appliesTo(City $city)` pour vérifier si une proposition s'applique à une ville
- Une proposition s'applique si :
  - C'est une attente commune, OU
  - La ville a au moins une spécificité en commun avec la proposition

## Instructions de déploiement

### 1. Appliquer les migrations
```bash
php bin/console doctrine:migrations:migrate
```

### 2. Charger les fixtures (optionnel, pour les données de test)
```bash
php bin/console doctrine:fixtures:load
```

### 3. Vider le cache
```bash
php bin/console cache:clear
```

## Tests recommandés

1. **Créer une nouvelle spécificité**
   - Aller dans Admin > Spécificités
   - Créer une nouvelle spécificité
   - Vérifier que le slug est généré automatiquement

2. **Associer des spécificités à une ville**
   - Éditer une ville
   - Sélectionner plusieurs spécificités
   - Sauvegarder et vérifier l'affichage

3. **Créer une proposition spécifique**
   - Créer une nouvelle proposition
   - Décocher "Attente commune"
   - Sélectionner une ou plusieurs spécificités
   - Sauvegarder

4. **Vérifier l'applicabilité**
   - Utiliser la méthode `appliesTo()` dans le code
   - Vérifier qu'une proposition commune s'applique à toutes les villes
   - Vérifier qu'une proposition spécifique ne s'applique qu'aux villes ayant les bonnes spécificités

## Compatibilité

- **Symfony** : 7.x
- **Doctrine ORM** : 3.x
- **PHP** : 8.2+
- **PostgreSQL** : 16+

## Notes importantes

1. Toutes les propositions existantes sont automatiquement marquées comme "attentes communes"
2. Les spécificités sont optionnelles : une ville peut ne pas avoir de spécificité
3. Le système est rétrocompatible : les fonctionnalités existantes ne sont pas affectées
4. Les fixtures créent des associations réalistes pour les 28 communes de Bordeaux Métropole

## Évolutions futures possibles

- Filtrage des propositions par spécificité dans l'interface publique
- Statistiques par spécificité
- Rapports comparatifs entre spécificités
- Règles métier plus complexes pour l'applicabilité
- Export des données par spécificité
- Tableau de bord dédié aux spécificités

## Support

Pour toute question ou problème, consulter :
- `docs/SPECIFICITY_FEATURE.md` - Documentation complète
- `docs/TECHNICAL_REFERENCE.md` - Référence technique générale

## Auteur

Développé le 30 septembre 2025

