# Référence des commandes Symfony

Ce document liste toutes les commandes personnalisées disponibles dans l'application de suivi de plaidoyer.

## Table des matières

1. [Commandes de gestion des données](#commandes-de-gestion-des-données)
2. [Commandes de test](#commandes-de-test)
3. [Commandes utiles](#commandes-utiles)

---

## Commandes de gestion des données

### `app:import-candidate-list`

Importe les listes candidates depuis un fichier CSV.

**Syntaxe** :
```bash
php bin/console app:import-candidate-list [filename] [options]
```

**Arguments** :
- `filename` : Nom du fichier CSV dans le répertoire `data/` (défaut : `candidateList.csv`)

**Options** :
- `--dry-run` : Simuler l'import sans persister les données
- `--clear` : Supprimer toutes les listes candidates existantes avant l'import

**Exemples** :
```bash
# Import standard
php bin/console app:import-candidate-list

# Import avec suppression des données existantes
php bin/console app:import-candidate-list --clear

# Simulation d'import
php bin/console app:import-candidate-list --dry-run

# Import d'un fichier spécifique
php bin/console app:import-candidate-list custom_list.csv
```

**Format du fichier CSV** :

Le fichier CSV doit contenir les colonnes suivantes :
- `Libellé commune` : Nom de la commune
- `Prénom candidat` : Prénom du candidat
- `Nom candidat` : Nom du candidat
- `Libellé Etendu Liste` : Nom de la liste
- `Email candidat` (optionnel) : Email du candidat
- `Téléphone candidat` (optionnel) : Téléphone du candidat

**Voir aussi** : [src/Command/ImportCandidateListCommand.php](../src/Command/ImportCandidateListCommand.php)

---

### `app:fill-random-commitments`

Remplit aléatoirement les engagements des listes candidates sur les propositions.

**Syntaxe** :
```bash
php bin/console app:fill-random-commitments [options]
```

**Options** :
- `--clear` : Supprimer tous les engagements existants avant de remplir
- `-p, --percentage=PERCENTAGE` : Pourcentage de propositions à engager par liste (0-100, défaut : 80)
- `-a, --acceptance-rate=ACCEPTANCE-RATE` : Pourcentage d'acceptation (0-100, défaut : 60)
- `--dry-run` : Simuler sans persister les données
- `-f, --force` : Forcer l'exécution sans confirmation

**Exemples** :

```bash
# Simulation (dry-run)
php bin/console app:fill-random-commitments --dry-run

# Remplissage standard (80% engagement, 60% acceptation)
php bin/console app:fill-random-commitments --force

# Scénario : Forte opposition (90% engagement, 20% acceptation)
php bin/console app:fill-random-commitments --clear --percentage=90 --acceptance-rate=20 --force

# Scénario : Forte adhésion (100% engagement, 85% acceptation)
php bin/console app:fill-random-commitments --clear --percentage=100 --acceptance-rate=85 --force

# Scénario : Tout refusé (100% engagement, 0% acceptation)
php bin/console app:fill-random-commitments --clear --percentage=100 --acceptance-rate=0 --force

# Scénario : Tout accepté (100% engagement, 100% acceptation)
php bin/console app:fill-random-commitments --clear --percentage=100 --acceptance-rate=100 --force

# Engagement partiel (30% engagement, 50% acceptation)
php bin/console app:fill-random-commitments --clear --percentage=30 --acceptance-rate=50 --force
```

**Cas d'usage** :
- Tests de développement avec des données réalistes
- Génération de données de démonstration
- Simulation de différents scénarios d'engagement
- Tests de performance avec un maximum d'engagements
- Vérification des calculs statistiques

**Documentation complète** : [COMMAND_FILL_RANDOM_COMMITMENTS.md](COMMAND_FILL_RANDOM_COMMITMENTS.md)

**Voir aussi** : [src/Command/FillRandomCommitmentsCommand.php](../src/Command/FillRandomCommitmentsCommand.php)

---

## Commandes de test

### Exécuter tous les tests

```bash
php bin/phpunit
```

### Exécuter un test spécifique

```bash
# Test des engagements 100% refusés
php bin/phpunit tests/Entity/CandidateListAllRefusedTest.php --testdox

# Test de la commande de remplissage aléatoire
php bin/phpunit tests/Command/FillRandomCommitmentsCommandTest.php --testdox
```

### Exécuter les tests avec couverture

```bash
php bin/phpunit --coverage-html coverage/
```

---

## Commandes utiles

### Gestion de la base de données

```bash
# Créer la base de données
php bin/console doctrine:database:create

# Créer le schéma
php bin/console doctrine:schema:create

# Mettre à jour le schéma
php bin/console doctrine:schema:update --force

# Supprimer et recréer le schéma
php bin/console doctrine:schema:drop --force
php bin/console doctrine:schema:create

# Charger les fixtures
php bin/console doctrine:fixtures:load
```

### Workflow complet de mise en place

Pour mettre en place l'application avec des données de test complètes :

```bash
# 1. Créer la base de données
php bin/console doctrine:database:create

# 2. Créer le schéma
php bin/console doctrine:schema:create

# 3. Charger les fixtures (villes, propositions, catégories, utilisateurs)
php bin/console doctrine:fixtures:load --no-interaction

# 4. Importer les listes candidates
php bin/console app:import-candidate-list

# 5. Remplir les engagements aléatoirement
php bin/console app:fill-random-commitments --percentage=70 --acceptance-rate=65 --force
```

### Réinitialisation complète

Pour réinitialiser complètement l'application :

```bash
# Supprimer et recréer la base de données
php bin/console doctrine:database:drop --force
php bin/console doctrine:database:create
php bin/console doctrine:schema:create

# Recharger les données
php bin/console doctrine:fixtures:load --no-interaction
php bin/console app:import-candidate-list
php bin/console app:fill-random-commitments --percentage=80 --acceptance-rate=60 --force
```

### Cache

```bash
# Vider le cache
php bin/console cache:clear

# Réchauffer le cache
php bin/console cache:warmup
```

### Debug

```bash
# Lister toutes les routes
php bin/console debug:router

# Afficher les informations d'une route
php bin/console debug:router app_proposition_show

# Lister tous les services
php bin/console debug:container

# Afficher les informations de sécurité
php bin/console debug:security
```

---

## Environnements

### Environnement de développement (dev)

```bash
php bin/console [commande] --env=dev
```

### Environnement de test (test)

```bash
php bin/console [commande] --env=test
```

### Environnement de production (prod)

```bash
php bin/console [commande] --env=prod --no-debug
```

---

## Scénarios de test recommandés

### Scénario 1 : Données équilibrées

```bash
php bin/console app:fill-random-commitments --clear --percentage=70 --acceptance-rate=60 --force
```

**Résultat** : Environ 70% des propositions engagées, avec 60% d'acceptation.

### Scénario 2 : Opposition forte

```bash
php bin/console app:fill-random-commitments --clear --percentage=90 --acceptance-rate=20 --force
```

**Résultat** : Beaucoup d'engagements mais majoritairement des refus.

### Scénario 3 : Adhésion forte

```bash
php bin/console app:fill-random-commitments --clear --percentage=95 --acceptance-rate=85 --force
```

**Résultat** : Beaucoup d'engagements avec une forte acceptation.

### Scénario 4 : Engagement faible

```bash
php bin/console app:fill-random-commitments --clear --percentage=25 --acceptance-rate=50 --force
```

**Résultat** : Peu d'engagements, équilibrés entre acceptation et refus.

### Scénario 5 : Test extrême (tout accepté)

```bash
php bin/console app:fill-random-commitments --clear --percentage=100 --acceptance-rate=100 --force
```

**Résultat** : Toutes les listes acceptent toutes les propositions.

### Scénario 6 : Test extrême (tout refusé)

```bash
php bin/console app:fill-random-commitments --clear --percentage=100 --acceptance-rate=0 --force
```

**Résultat** : Toutes les listes refusent toutes les propositions.

---

## Dépannage

### Erreur : "Database does not exist"

**Solution** :
```bash
php bin/console doctrine:database:create
php bin/console doctrine:schema:create
```

### Erreur : "No candidate lists found"

**Solution** :
```bash
php bin/console app:import-candidate-list
```

### Erreur : "No propositions found"

**Solution** :
```bash
php bin/console doctrine:fixtures:load
```

### Les tests échouent avec des erreurs de contrainte unique

**Solution** :
```bash
# Réinitialiser la base de données de test
php bin/console doctrine:schema:drop --force --env=test
php bin/console doctrine:schema:create --env=test
```

---

## Voir aussi

- [Documentation technique](TECHNICAL_REFERENCE.md)
- [Guide de démarrage rapide](README.md)
- [Test des engagements 100% refusés](TEST_ALL_REFUSED_COMMITMENTS.md)
- [Commande de remplissage aléatoire](COMMAND_FILL_RANDOM_COMMITMENTS.md)

