# Affichage des attentes sur le site public

## Vue d'ensemble

Les attentes (communes et spécifiques) sont maintenant affichées sur la page de détail de chaque proposition.

## Page concernée

**URL** : `/propositions/{id}`

**Exemple** : `https://127.0.0.1:8000/propositions/150`

## Sections affichées

### 1. Description de la proposition

Si la proposition a une description, elle est affichée dans une carte avec :
- Icône : `fas fa-info-circle`
- Couleur : Bleu (primary)
- Style : Carte avec ombre légère sur fond gris clair

### 2. Attentes

Cette section s'affiche uniquement si la proposition a au moins une attente (commune ou spécifique).

#### A. Attente commune

Si `proposition.commonExpectation` n'est pas null :

- **Affichage** : Carte verte avec bordure
- **Titre** : "Attente commune pour toutes les mairies"
- **Icône** : `fas fa-check-circle`
- **Contenu** : Texte de l'attente commune
- **Style** : 
  - En-tête : Fond vert (`bg-success`)
  - Corps : Texte en grande taille (`fs-5`)

#### B. Attentes spécifiques

Si la proposition a des `specificExpectations` :

- **Affichage** : Carte bleue avec bordure
- **Titre** : "Attentes spécifiques par type de territoire"
- **Icône** : `fas fa-map-marker-alt`
- **Contenu** : Liste des attentes par spécificité
- **Style** :
  - En-tête : Fond bleu info (`bg-info`)
  - Corps : Grille responsive (2 colonnes sur desktop)
  - Chaque attente : Bordure gauche bleue avec nom de la spécificité en gras

### 3. Engagements par commune

Section existante qui affiche les listes engagées par commune.

## Logique d'affichage

### Cas 1 : Attente commune uniquement

```twig
{% if hasCommonExpectation %}
    <!-- Carte verte avec l'attente commune -->
{% endif %}
```

**Exemple** : Proposition 149 - "Mettre en place le SRAV dans toutes les écoles"

### Cas 2 : Attente commune + attentes spécifiques

```twig
{% if hasCommonExpectation %}
    <!-- Carte verte avec l'attente commune -->
{% endif %}

{% if hasSpecificExpectations %}
    <!-- Carte bleue avec les attentes spécifiques -->
{% endif %}
```

**Exemple** : Proposition 150 - Attente commune + 2 attentes spécifiques (Intra-rocade, Extra-rocade)

### Cas 3 : Uniquement des attentes spécifiques

```twig
{% if hasSpecificExpectations %}
    <!-- Carte bleue avec les attentes spécifiques -->
{% endif %}
```

**Exemple** : Proposition 151 - 2 attentes spécifiques (Centre urbain, Périurbain)

### Cas 4 : Aucune attente

La section "Ce que nous attendons des mairies" n'est pas affichée.

## Modifications apportées

### Contrôleur : `src/Controller/PropositionController.php`

```php
// Préparer les attentes (commune et spécifiques)
$hasCommonExpectation = $proposition->getCommonExpectation() !== null;
$specificExpectations = $proposition->getSpecificExpectations();
$hasSpecificExpectations = count($specificExpectations) > 0;

return $this->render('public/proposition_show.html.twig', [
    // ...
    'hasCommonExpectation' => $hasCommonExpectation,
    'hasSpecificExpectations' => $hasSpecificExpectations,
]);
```

### Repository : `src/Repository/PropositionRepository.php`

Ajout du chargement des attentes spécifiques dans la requête :

```php
public function findOneWithCommitments(int $id): ?Proposition
{
    return $this->createQueryBuilder('p')
        // ...
        ->leftJoin('p.specificExpectations', 'se')
        ->leftJoin('se.specificity', 's')
        ->addSelect('cm', 'cl', 'c', 'cat', 'se', 's')
        // ...
}
```

### Template : `templates/public/proposition_show.html.twig`

Ajout d'une nouvelle section entre la description et les engagements :

```twig
<!-- Expectations Section -->
{% if hasCommonExpectation or hasSpecificExpectations %}
    <section class="py-4">
        <div class="container">
            <h4 class="fw-bold mb-4">
                <i class="fas fa-bullseye text-success me-2"></i>
                Ce que nous attendons des mairies
            </h4>

            {% if hasCommonExpectation %}
                <!-- Carte verte pour l'attente commune -->
            {% endif %}

            {% if hasSpecificExpectations %}
                <!-- Carte bleue pour les attentes spécifiques -->
            {% endif %}
        </div>
    </section>
{% endif %}
```

## Design et UX

### Couleurs

- **Attente commune** : Vert (`success`) - Symbolise l'universalité
- **Attentes spécifiques** : Bleu info (`info`) - Symbolise la spécificité/personnalisation

### Icônes

- **Attente commune** : `fas fa-check-circle` - Symbolise la validation/accord
- **Attentes spécifiques** : `fas fa-map-marker-alt` - Symbolise la localisation/territoire
- **Titre de section** : `fas fa-bullseye` - Symbolise l'objectif/cible

### Responsive

- **Desktop** : Les attentes spécifiques s'affichent sur 2 colonnes
- **Mobile** : Les attentes spécifiques s'affichent sur 1 colonne

### Typographie

- **Titres** : `fw-bold` (gras)
- **Attentes** : `fs-5` (grande taille) pour la lisibilité
- **Spécificités** : `fw-bold text-info` pour les distinguer

## Exemples de rendu

### Exemple 1 : Proposition avec attente commune

```
┌─────────────────────────────────────────────────────┐
│ ✓ Attente commune pour toutes les mairies          │
├─────────────────────────────────────────────────────┤
│ Mettre en place le Savoir Rouler À Vélo (SRAV)     │
│ dans toutes les écoles de la commune                │
└─────────────────────────────────────────────────────┘
```

### Exemple 2 : Proposition avec attente commune + spécifiques

```
┌─────────────────────────────────────────────────────┐
│ ✓ Attente commune pour toutes les mairies          │
├─────────────────────────────────────────────────────┤
│ Développer les pistes cyclables sur l'ensemble      │
│ du territoire                                        │
└─────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────┐
│ 📍 Attentes spécifiques par type de territoire      │
├─────────────────────────────────────────────────────┤
│ ┃ Intra-rocade                                      │
│ ┃ Créer un réseau cyclable continu et sécurisé     │
│                                                      │
│ ┃ Extra-rocade                                      │
│ ┃ Développer des voies vertes intercommunales      │
└─────────────────────────────────────────────────────┘
```

### Exemple 3 : Proposition avec uniquement des attentes spécifiques

```
┌─────────────────────────────────────────────────────┐
│ 📍 Attentes spécifiques par type de territoire      │
├─────────────────────────────────────────────────────┤
│ ┃ Centre urbain                                     │
│ ┃ Créer des zones piétonnes permanentes            │
│                                                      │
│ ┃ Périurbain                                        │
│ ┃ Aménager les centres-bourgs avec des zones 30    │
└─────────────────────────────────────────────────────┘
```

## Tests

### Tester l'affichage

1. **Proposition avec attente commune** :
   ```
   https://127.0.0.1:8000/propositions/149
   ```
   Devrait afficher une carte verte avec l'attente commune.

2. **Proposition avec attente commune + spécifiques** :
   ```
   https://127.0.0.1:8000/propositions/150
   ```
   Devrait afficher une carte verte + une carte bleue avec 2 attentes.

3. **Proposition avec uniquement des attentes spécifiques** :
   ```
   https://127.0.0.1:8000/propositions/151
   ```
   Devrait afficher uniquement une carte bleue avec 2 attentes.

4. **Proposition sans attente** :
   ```
   https://127.0.0.1:8000/propositions/152
   ```
   Ne devrait pas afficher la section "Ce que nous attendons des mairies".

### Vérifier le responsive

1. Ouvrir la page sur desktop (> 768px)
2. Vérifier que les attentes spécifiques s'affichent sur 2 colonnes
3. Réduire la fenêtre (< 768px)
4. Vérifier que les attentes spécifiques s'affichent sur 1 colonne

## Évolutions futures possibles

1. **Filtrage par ville** : Afficher uniquement l'attente applicable à une ville sélectionnée
2. **Icônes par spécificité** : Ajouter des icônes spécifiques pour chaque type de territoire
3. **Carte interactive** : Afficher une carte montrant les zones concernées par chaque attente
4. **Comparaison** : Permettre de comparer les attentes entre différentes spécificités
5. **Export** : Permettre d'exporter les attentes en PDF
6. **Partage** : Boutons de partage sur les réseaux sociaux pour chaque attente

## Accessibilité

- ✅ Utilisation de balises sémantiques (`<section>`, `<h4>`, etc.)
- ✅ Icônes accompagnées de texte
- ✅ Contraste suffisant entre texte et fond
- ✅ Structure hiérarchique claire
- ✅ Responsive design

## Performance

- ✅ Chargement optimisé avec `leftJoin` et `addSelect`
- ✅ Pas de requêtes N+1
- ✅ Données chargées en une seule requête

