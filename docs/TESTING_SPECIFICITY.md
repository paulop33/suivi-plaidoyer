# Guide de test - Fonctionnalité Spécificités

## Tests manuels à effectuer

### 1. Vérifier les spécificités créées

```bash
# Lister toutes les spécificités
php bin/console dbal:run-sql "SELECT * FROM specificity ORDER BY name;"
```

**Résultat attendu** : 6 spécificités (Intra-rocade, Extra-rocade, Centre urbain, Périurbain, Rive gauche, Rive droite)

### 2. Vérifier les associations ville-spécificité

```bash
# Compter les associations
php bin/console dbal:run-sql "SELECT COUNT(*) FROM city_specificity;"

# Voir quelques exemples
php bin/console dbal:run-sql "
SELECT c.name, s.name as specificity 
FROM city c 
JOIN city_specificity cs ON c.id = cs.city_id 
JOIN specificity s ON s.id = cs.specificity_id 
WHERE c.name IN ('Bordeaux', 'Cenon', 'Pessac', 'Artigues-près-Bordeaux')
ORDER BY c.name, s.name;
"
```

**Résultat attendu** :
- Bordeaux : Intra-rocade, Centre urbain, Rive gauche
- Cenon : Intra-rocade, Centre urbain, Rive droite
- Pessac : Extra-rocade, Périurbain, Rive gauche
- Artigues-près-Bordeaux : Extra-rocade, Périurbain, Rive droite

### 3. Vérifier les propositions

```bash
# Vérifier que toutes les propositions sont des attentes communes
php bin/console dbal:run-sql "
SELECT COUNT(*) as total, 
       SUM(CASE WHEN is_common_expectation = true THEN 1 ELSE 0 END) as common,
       SUM(CASE WHEN is_common_expectation = false THEN 1 ELSE 0 END) as specific
FROM proposition;
"
```

**Résultat attendu** : Toutes les propositions doivent être des attentes communes (common = total)

### 4. Tests dans l'interface d'administration

#### 4.1. Accéder à l'interface
1. Démarrer le serveur : `symfony server:start` ou `php -S localhost:8000 -t public`
2. Aller sur : `http://localhost:8000/admin`
3. Se connecter avec un compte admin

#### 4.2. Tester le CRUD Spécificités
1. Cliquer sur "Spécificités" dans le menu
2. Vérifier que les 6 spécificités sont listées
3. Cliquer sur une spécificité pour voir les détails
4. Vérifier que le nombre de villes associées est affiché
5. Créer une nouvelle spécificité :
   - Nom : "Test Spécificité"
   - Description : "Ceci est un test"
   - Sauvegarder
6. Vérifier que le slug a été généré automatiquement
7. Supprimer la spécificité de test

#### 4.3. Tester l'édition de ville
1. Cliquer sur "Villes" dans le menu
2. Éditer "Bordeaux"
3. Vérifier que les spécificités sont affichées (Intra-rocade, Centre urbain, Rive gauche)
4. Essayer d'ajouter/retirer une spécificité
5. Sauvegarder et vérifier que les changements sont persistés

#### 4.4. Tester l'édition de proposition
1. Cliquer sur "Propositions" dans le menu
2. Éditer une proposition existante
3. Vérifier que la case "Attente commune" est cochée par défaut
4. Décocher "Attente commune"
5. Vérifier que le champ "Spécificités" apparaît
6. Sélectionner une ou plusieurs spécificités
7. Sauvegarder
8. Recharger la page et vérifier que les changements sont persistés
9. Recocher "Attente commune" et sauvegarder

### 5. Tests programmatiques

Créer un fichier de test temporaire `tests/SpecificityTest.php` :

```php
<?php

namespace App\Tests;

use App\Entity\City;
use App\Entity\Proposition;
use App\Entity\Specificity;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class SpecificityTest extends KernelTestCase
{
    private $entityManager;
    private $cityRepository;
    private $propositionRepository;
    private $specificityRepository;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();
        $this->entityManager = $kernel->getContainer()
            ->get('doctrine')
            ->getManager();
        
        $this->cityRepository = $this->entityManager->getRepository(City::class);
        $this->propositionRepository = $this->entityManager->getRepository(Proposition::class);
        $this->specificityRepository = $this->entityManager->getRepository(Specificity::class);
    }

    public function testCityHasSpecificities(): void
    {
        $bordeaux = $this->cityRepository->findOneBySlug('bordeaux');
        
        $this->assertNotNull($bordeaux);
        $this->assertGreaterThan(0, $bordeaux->getSpecificities()->count());
        
        // Bordeaux devrait avoir "Intra-rocade"
        $hasIntraRocade = false;
        foreach ($bordeaux->getSpecificities() as $specificity) {
            if ($specificity->getSlug() === 'intra-rocade') {
                $hasIntraRocade = true;
                break;
            }
        }
        $this->assertTrue($hasIntraRocade);
    }

    public function testCommonPropositionAppliesTo AllCities(): void
    {
        $proposition = $this->propositionRepository->findOneBy(['isCommonExpectation' => true]);
        $this->assertNotNull($proposition);
        
        $cities = $this->cityRepository->findAll();
        foreach ($cities as $city) {
            $this->assertTrue(
                $proposition->appliesTo($city),
                "Common proposition should apply to {$city->getName()}"
            );
        }
    }

    public function testSpecificPropositionAppliesOnlyToMatchingCities(): void
    {
        // Créer une proposition spécifique pour test
        $proposition = new Proposition();
        $proposition->setTitle('Test Proposition');
        $proposition->setIsCommonExpectation(false);
        
        $intraRocade = $this->specificityRepository->findOneBySlug('intra-rocade');
        $proposition->addSpecificity($intraRocade);
        
        // Bordeaux (intra-rocade) devrait correspondre
        $bordeaux = $this->cityRepository->findOneBySlug('bordeaux');
        $this->assertTrue($proposition->appliesTo($bordeaux));
        
        // Pessac (extra-rocade) ne devrait pas correspondre
        $pessac = $this->cityRepository->findOneBySlug('pessac');
        $this->assertFalse($proposition->appliesTo($pessac));
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->entityManager->close();
        $this->entityManager = null;
    }
}
```

Exécuter les tests :

```bash
php bin/phpunit tests/SpecificityTest.php
```

### 6. Tests de performance

```bash
# Vérifier que les requêtes sont optimisées
php bin/console debug:container --env=dev --parameter=kernel.debug

# Activer le profiler Symfony et vérifier le nombre de requêtes SQL
# lors de l'affichage d'une liste de villes avec leurs spécificités
```

### 7. Tests de régression

Vérifier que les fonctionnalités existantes fonctionnent toujours :

1. **Créer un engagement**
   - Aller dans "Listes"
   - Créer ou éditer une liste
   - Gérer les engagements
   - Vérifier que tout fonctionne

2. **Afficher les statistiques**
   - Aller sur le dashboard
   - Vérifier que les graphiques s'affichent correctement

3. **Filtrer les données**
   - Utiliser les filtres dans les différentes sections
   - Vérifier qu'il n'y a pas d'erreurs

## Checklist de validation

- [ ] Les 6 spécificités sont créées
- [ ] Les 28 villes ont des spécificités associées
- [ ] Toutes les propositions sont des attentes communes par défaut
- [ ] Le CRUD Spécificités fonctionne dans l'admin
- [ ] On peut éditer les spécificités d'une ville
- [ ] On peut créer une proposition spécifique
- [ ] La méthode `appliesTo()` fonctionne correctement
- [ ] Le schéma de base de données est valide
- [ ] Les fixtures se chargent sans erreur
- [ ] Les fonctionnalités existantes fonctionnent toujours
- [ ] Pas d'erreurs dans les logs

## Commandes utiles

```bash
# Vérifier le schéma
php bin/console doctrine:schema:validate

# Voir les migrations en attente
php bin/console doctrine:migrations:status

# Recharger les fixtures
php bin/console doctrine:fixtures:load --no-interaction

# Vider le cache
php bin/console cache:clear

# Voir les routes
php bin/console debug:router | grep specificity

# Voir les entités
php bin/console doctrine:mapping:info
```

## Résolution de problèmes

### Erreur "Table specificity does not exist"
```bash
php bin/console doctrine:migrations:migrate
```

### Erreur "Column is_common_expectation does not exist"
```bash
php bin/console doctrine:migrations:migrate
```

### Les spécificités ne s'affichent pas
```bash
php bin/console doctrine:fixtures:load --no-interaction
php bin/console cache:clear
```

### Erreur de mapping Doctrine
```bash
php bin/console doctrine:schema:validate
# Corriger les erreurs indiquées
```

