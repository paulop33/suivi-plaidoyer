# Fonctionnalité : Attentes communes et spécifiques

## Vue d'ensemble

Cette fonctionnalité permet de définir des attentes différenciées pour les propositions :
- **Attente commune** : Ce qui est attendu de toutes les mairies
- **Attentes spécifiques** : Ce qui est attendu pour chaque groupe de spécificités (intra-rocade, extra-rocade, centre urbain, périurbain, etc.)

## Concepts clés

### 1. Spécificité (Specificity)

Une spécificité est une caractéristique géographique ou urbaine qui permet de regrouper des villes :
- Intra-rocade / Extra-rocade
- Centre urbain / Périurbain
- Rive gauche / Rive droite

**Une ville peut avoir plusieurs spécificités.**

Exemple : Bordeaux est "Intra-rocade", "Centre urbain" et "Rive gauche"

### 2. Proposition

Chaque proposition peut avoir :
- **Une attente commune** (champ TEXT, optionnel) : Ce qui est attendu de toutes les mairies
- **Des attentes spécifiques** (relation OneToMany avec SpecificExpectation) : Des attentes différentes selon les spécificités

### 3. Attente spécifique (SpecificExpectation)

Une attente spécifique lie :
- Une **proposition**
- Une **spécificité**
- Un **texte d'attente**

## Modèle de données

### Entités

#### Proposition
```php
class Proposition
{
    private ?string $commonExpectation = null;  // Attente commune (TEXT)
    private Collection $specificExpectations;   // Attentes spécifiques (OneToMany)
}
```

#### SpecificExpectation
```php
class SpecificExpectation
{
    private ?Proposition $proposition = null;   // ManyToOne
    private ?Specificity $specificity = null;   // ManyToOne
    private ?string $expectation = null;        // TEXT
}
```

#### Specificity
```php
class Specificity
{
    private Collection $cities;                 // ManyToMany avec City
    private Collection $specificExpectations;   // OneToMany
}
```

### Schéma de base de données

```sql
-- Table des attentes spécifiques
CREATE TABLE specific_expectation (
    id SERIAL PRIMARY KEY,
    proposition_id INT NOT NULL REFERENCES proposition(id),
    specificity_id INT NOT NULL REFERENCES specificity(id),
    expectation TEXT NOT NULL
);

-- Colonne dans la table proposition
ALTER TABLE proposition ADD COLUMN common_expectation TEXT DEFAULT NULL;
```

## Cas d'usage

### Cas 1 : Attente commune uniquement

**Proposition** : "Mettre en place le Savoir Rouler À Vélo (SRAV)"

- `commonExpectation` = "Mettre en place le SRAV dans toutes les écoles de la commune"
- `specificExpectations` = [] (vide)

**Résultat** : Toutes les villes ont la même attente.

### Cas 2 : Attente commune + attentes spécifiques

**Proposition** : "Développer les pistes cyclables"

- `commonExpectation` = "Développer les pistes cyclables sur l'ensemble du territoire"
- `specificExpectations` :
  - Intra-rocade : "Créer un réseau cyclable continu et sécurisé avec des pistes séparées"
  - Extra-rocade : "Développer des voies vertes et des liaisons intercommunales"

**Résultat** :
- Les villes intra-rocade voient l'attente spécifique "Créer un réseau cyclable continu..."
- Les villes extra-rocade voient l'attente spécifique "Développer des voies vertes..."
- Les villes sans spécificité correspondante voient l'attente commune

### Cas 3 : Uniquement des attentes spécifiques

**Proposition** : "Aménager les espaces publics"

- `commonExpectation` = null (vide)
- `specificExpectations` :
  - Centre urbain : "Créer des zones piétonnes permanentes"
  - Périurbain : "Aménager les centres-bourgs avec des zones 30"

**Résultat** :
- Les villes "Centre urbain" voient "Créer des zones piétonnes permanentes"
- Les villes "Périurbain" voient "Aménager les centres-bourgs avec des zones 30"
- Les autres villes ne voient aucune attente pour cette proposition

## Logique métier

### Méthode `getExpectationFor(City $city)`

Cette méthode retourne l'attente applicable pour une ville donnée :

```php
public function getExpectationFor(City $city): ?string
{
    // 1. Si une attente commune existe, la retourner
    if ($this->commonExpectation !== null) {
        return $this->commonExpectation;
    }

    // 2. Sinon, chercher une attente spécifique correspondant aux spécificités de la ville
    foreach ($this->specificExpectations as $specificExpectation) {
        if ($city->getSpecificities()->contains($specificExpectation->getSpecificity())) {
            return $specificExpectation->getExpectation();
        }
    }

    // 3. Aucune attente trouvée
    return null;
}
```

**Priorité** : Attente commune > Attente spécifique > null

### Méthode `hasExpectationFor(City $city)`

Vérifie si une proposition a une attente pour une ville :

```php
public function hasExpectationFor(City $city): bool
{
    return $this->getExpectationFor($city) !== null;
}
```

## Interface d'administration

### Gestion des propositions

Dans le formulaire d'édition d'une proposition :

1. **Champ "Attente commune"** (TextareaField)
   - Texte libre
   - Optionnel
   - Si rempli, s'applique à toutes les villes

2. **Champ "Attentes spécifiques"** (CollectionField)
   - Liste des attentes spécifiques
   - Chaque entrée contient :
     - Spécificité (sélection)
     - Texte d'attente (textarea)

### Gestion des attentes spécifiques

Un CRUD dédié permet de gérer les attentes spécifiques :
- Menu : "Attentes spécifiques"
- Champs :
  - Proposition (sélection)
  - Spécificité (sélection)
  - Attente (textarea)

## Exemples de code

### Créer une proposition avec attente commune

```php
$proposition = new Proposition();
$proposition->setTitle('Développer le vélo');
$proposition->setCommonExpectation('Mettre en place des infrastructures cyclables');
$manager->persist($proposition);
```

### Créer une attente spécifique

```php
$specificExpectation = new SpecificExpectation();
$specificExpectation->setProposition($proposition);
$specificExpectation->setSpecificity($intraRocade);
$specificExpectation->setExpectation('Créer un réseau cyclable continu');
$manager->persist($specificExpectation);
```

### Récupérer l'attente pour une ville

```php
$city = $cityRepository->findOneBySlug('bordeaux');
$proposition = $propositionRepository->find(1);

$expectation = $proposition->getExpectationFor($city);
if ($expectation !== null) {
    echo "Attente pour {$city->getName()} : {$expectation}";
} else {
    echo "Aucune attente pour cette ville";
}
```

### Afficher toutes les attentes d'une proposition

```php
$proposition = $propositionRepository->find(1);

// Attente commune
if ($proposition->getCommonExpectation()) {
    echo "Attente commune : " . $proposition->getCommonExpectation() . "\n";
}

// Attentes spécifiques
foreach ($proposition->getSpecificExpectations() as $specificExpectation) {
    echo "Attente pour " . $specificExpectation->getSpecificity()->getName() . " : ";
    echo $specificExpectation->getExpectation() . "\n";
}
```

## Données de test

Les fixtures créent automatiquement :

1. **Proposition 1** : Attente commune uniquement
   - Attente commune : "Mettre en place le SRAV dans toutes les écoles"

2. **Proposition 2** : Attente commune + 2 attentes spécifiques
   - Attente commune : "Développer les pistes cyclables sur l'ensemble du territoire"
   - Intra-rocade : "Créer un réseau cyclable continu et sécurisé"
   - Extra-rocade : "Développer des voies vertes et des liaisons intercommunales"

3. **Proposition 3** : Uniquement des attentes spécifiques
   - Centre urbain : "Créer des zones piétonnes permanentes"
   - Périurbain : "Aménager les centres-bourgs avec des zones 30"

## Migration

La migration `Version20250930114244` effectue les changements suivants :

1. Crée la table `specific_expectation`
2. Ajoute la colonne `common_expectation` à la table `proposition`
3. Supprime la colonne `is_common_expectation` (ancien système)
4. Supprime la table `proposition_specificity` (ancien système)

## Tests

### Vérifier les données

```bash
# Voir les propositions avec leurs attentes
php bin/console dbal:run-sql "
SELECT p.id, p.title, p.common_expectation, COUNT(se.id) as nb_specific 
FROM proposition p 
LEFT JOIN specific_expectation se ON p.id = se.proposition_id 
GROUP BY p.id, p.title, p.common_expectation 
LIMIT 5;
"

# Voir les attentes spécifiques
php bin/console dbal:run-sql "
SELECT se.id, p.title, s.name, se.expectation 
FROM specific_expectation se 
JOIN proposition p ON se.proposition_id = p.id 
JOIN specificity s ON se.specificity_id = s.id;
"
```

### Tester la logique métier

```php
// Test 1 : Attente commune
$proposition = $propositionRepository->find(149);
$bordeaux = $cityRepository->findOneBySlug('bordeaux');
$expectation = $proposition->getExpectationFor($bordeaux);
// Résultat attendu : "Mettre en place le SRAV dans toutes les écoles"

// Test 2 : Attente spécifique (ville intra-rocade)
$proposition = $propositionRepository->find(150);
$bordeaux = $cityRepository->findOneBySlug('bordeaux');
$expectation = $proposition->getExpectationFor($bordeaux);
// Résultat attendu : "Créer un réseau cyclable continu et sécurisé..."

// Test 3 : Attente spécifique (ville extra-rocade)
$proposition = $propositionRepository->find(150);
$pessac = $cityRepository->findOneBySlug('pessac');
$expectation = $proposition->getExpectationFor($pessac);
// Résultat attendu : "Développer des voies vertes et des liaisons intercommunales"
```

## Avantages de cette approche

1. **Flexibilité** : Possibilité d'avoir une attente commune, des attentes spécifiques, ou les deux
2. **Clarté** : Séparation nette entre attente commune et attentes spécifiques
3. **Évolutivité** : Facile d'ajouter de nouvelles spécificités et attentes
4. **Maintenabilité** : Structure de données claire et logique métier simple
5. **Réutilisabilité** : Les spécificités peuvent être utilisées pour d'autres fonctionnalités

## Évolutions futures possibles

1. **Priorité des attentes** : Permettre de définir une priorité entre attente commune et spécifique
2. **Attentes multiples** : Permettre plusieurs attentes spécifiques pour une même spécificité
3. **Héritage d'attentes** : Définir des hiérarchies de spécificités avec héritage
4. **Validation** : Vérifier qu'au moins une attente (commune ou spécifique) existe
5. **Interface publique** : Afficher les attentes sur le site public avec filtrage par ville
6. **Export** : Générer des rapports par spécificité avec les attentes correspondantes

