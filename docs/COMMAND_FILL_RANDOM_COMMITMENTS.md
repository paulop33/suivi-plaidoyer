# Commande : Remplissage aléatoire des engagements

## Description

La commande `app:fill-random-commitments` permet de remplir automatiquement et aléatoirement les engagements des listes candidates sur les propositions. Cette commande est particulièrement utile pour :

- **Tester l'application** avec des données réalistes
- **Générer des données de démonstration** pour des présentations
- **Simuler différents scénarios** d'engagement (forte acceptation, forte opposition, etc.)

## Utilisation

### Syntaxe de base

```bash
php bin/console app:fill-random-commitments [options]
```

### Options disponibles

| Option | Raccourci | Description | Valeur par défaut |
|--------|-----------|-------------|-------------------|
| `--clear` | - | Supprimer tous les engagements existants avant de remplir | Non |
| `--percentage` | `-p` | Pourcentage de propositions à engager par liste (0-100) | 80 |
| `--acceptance-rate` | `-a` | Pourcentage d'acceptation (0-100) | 60 |
| `--dry-run` | - | Simuler sans persister les données | Non |
| `--force` | `-f` | Forcer l'exécution sans confirmation | Non |

## Exemples d'utilisation

### 1. Simulation (dry-run)

Tester la commande sans modifier la base de données :

```bash
php bin/console app:fill-random-commitments --dry-run
```

**Résultat** : Affiche ce qui serait créé sans rien enregistrer.

### 2. Remplissage standard

Créer des engagements avec les paramètres par défaut (80% des propositions, 60% d'acceptation) :

```bash
php bin/console app:fill-random-commitments --force
```

**Résultat** : 
- Chaque liste candidate s'engage sur environ 80% des propositions
- Environ 60% des engagements sont acceptés, 40% refusés

### 3. Scénario : Forte opposition

Simuler un scénario où les listes refusent majoritairement les propositions :

```bash
php bin/console app:fill-random-commitments --clear --percentage=90 --acceptance-rate=20 --force
```

**Résultat** :
- Supprime tous les engagements existants
- Chaque liste s'engage sur 90% des propositions
- Seulement 20% d'acceptation (80% de refus)

### 4. Scénario : Forte adhésion

Simuler un scénario où les listes acceptent majoritairement les propositions :

```bash
php bin/console app:fill-random-commitments --clear --percentage=100 --acceptance-rate=85 --force
```

**Résultat** :
- Supprime tous les engagements existants
- Chaque liste s'engage sur 100% des propositions
- 85% d'acceptation (15% de refus)

### 5. Scénario : Engagement partiel

Simuler un scénario où les listes ne s'engagent que sur quelques propositions :

```bash
php bin/console app:fill-random-commitments --clear --percentage=30 --acceptance-rate=50 --force
```

**Résultat** :
- Supprime tous les engagements existants
- Chaque liste s'engage sur seulement 30% des propositions
- 50% d'acceptation, 50% de refus

### 6. Mode interactif (avec confirmation)

Exécuter la commande avec une demande de confirmation :

```bash
php bin/console app:fill-random-commitments --clear --percentage=70 --acceptance-rate=65
```

**Résultat** : La commande demande confirmation avant d'exécuter les modifications.

## Fonctionnement détaillé

### Algorithme

1. **Récupération des données** : La commande récupère toutes les listes candidates et toutes les propositions
2. **Pour chaque liste candidate** :
   - Mélange aléatoirement l'ordre des propositions
   - Sélectionne un nombre de propositions selon le `--percentage`
   - Pour chaque proposition sélectionnée :
     - Vérifie si un engagement existe déjà (si oui, ignore)
     - Détermine aléatoirement le statut selon le `--acceptance-rate`
     - Crée l'engagement avec un commentaire aléatoire approprié
3. **Enregistrement** : Tous les engagements sont enregistrés en une seule transaction

### Commentaires générés

La commande génère automatiquement des commentaires réalistes pour chaque engagement :

**Commentaires d'acceptation** :
- "Nous soutenons pleinement cette proposition."
- "Cette mesure correspond à nos priorités."
- "Nous nous engageons à mettre en œuvre cette proposition."
- "Proposition en accord avec notre programme."
- "Nous approuvons cette initiative."
- "Cette proposition répond aux besoins de nos citoyens."
- "Nous sommes favorables à cette mesure."

**Commentaires de refus** :
- "Cette proposition ne correspond pas à nos priorités actuelles."
- "Nous ne pouvons pas nous engager sur cette mesure pour des raisons budgétaires."
- "Cette proposition nécessite une étude plus approfondie."
- "Nous avons des réserves sur la faisabilité de cette mesure."
- "Cette proposition n'est pas compatible avec notre programme."
- "Nous préférons explorer d'autres alternatives."
- "Les contraintes techniques ne nous permettent pas d'accepter cette proposition."

## Statistiques affichées

À la fin de l'exécution, la commande affiche un tableau récapitulatif :

```
 --------------------------------- -------------- 
  Statistique                       Valeur       
 --------------------------------- -------------- 
  Engagements créés                 3210          
  Engagements ignorés (existants)   0             
  Acceptés                          1621 (50.5%)  
  Refusés                           1589 (49.5%)  
 --------------------------------- -------------- 
```

## Cas d'usage

### 1. Tests de développement

Générer rapidement des données pour tester les fonctionnalités :

```bash
php bin/console app:fill-random-commitments --clear --force
```

### 2. Démonstration client

Préparer une démo avec des données variées :

```bash
php bin/console app:fill-random-commitments --clear --percentage=75 --acceptance-rate=70 --force
```

### 3. Tests de performance

Tester les performances avec un maximum d'engagements :

```bash
php bin/console app:fill-random-commitments --clear --percentage=100 --acceptance-rate=50 --force
```

### 4. Tests de statistiques

Vérifier les calculs statistiques avec différents scénarios :

```bash
# Scénario 1 : Tout accepté
php bin/console app:fill-random-commitments --clear --percentage=100 --acceptance-rate=100 --force

# Scénario 2 : Tout refusé
php bin/console app:fill-random-commitments --clear --percentage=100 --acceptance-rate=0 --force

# Scénario 3 : Équilibré
php bin/console app:fill-random-commitments --clear --percentage=100 --acceptance-rate=50 --force
```

## Prérequis

Avant d'utiliser cette commande, assurez-vous que :

1. **Les fixtures sont chargées** :
   ```bash
   php bin/console doctrine:fixtures:load
   ```

2. **Les listes candidates sont importées** :
   ```bash
   php bin/console app:import-candidate-list
   ```

3. **La base de données contient** :
   - Au moins une liste candidate
   - Au moins une proposition

## Avertissements

⚠️ **Attention** : L'option `--clear` supprime **TOUS** les engagements existants de manière irréversible.

⚠️ **Recommandation** : Utilisez toujours `--dry-run` d'abord pour vérifier le résultat avant d'exécuter réellement la commande.

⚠️ **Production** : Cette commande est destinée aux environnements de développement et de test. Ne l'utilisez pas en production sans sauvegarde préalable.

## Dépannage

### Erreur : "Aucune liste candidate trouvée"

**Solution** : Importez d'abord les listes candidates :
```bash
php bin/console app:import-candidate-list
```

### Erreur : "Aucune proposition trouvée"

**Solution** : Chargez les fixtures :
```bash
php bin/console doctrine:fixtures:load
```

### Les pourcentages ne correspondent pas exactement

**Explication** : Les pourcentages sont approximatifs car :
- Le nombre de propositions peut ne pas être divisible exactement
- L'aléatoire peut créer de légères variations
- Les engagements existants sont ignorés

## Code source

Le code source de cette commande se trouve dans :
```
src/Command/FillRandomCommitmentsCommand.php
```

## Voir aussi

- [Test des engagements 100% refusés](TEST_ALL_REFUSED_COMMITMENTS.md)
- [Import des listes candidates](../src/Command/ImportCandidateListCommand.php)
- [Documentation technique](TECHNICAL_REFERENCE.md)

