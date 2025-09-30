# Guide utilisateur - Spécificités et Attentes Communes

## 🎯 Qu'est-ce qu'une spécificité ?

Une **spécificité** est une caractéristique géographique ou urbaine qui permet de regrouper des villes ayant des points communs. Par exemple :

- **Intra-rocade** : Villes situées à l'intérieur de la rocade bordelaise
- **Extra-rocade** : Villes situées à l'extérieur de la rocade
- **Centre urbain** : Zones à forte densité urbaine
- **Périurbain** : Zones périphériques
- **Rive gauche** / **Rive droite** : Position par rapport à la Garonne

Une ville peut avoir **plusieurs spécificités**. Par exemple, Bordeaux est :
- Intra-rocade
- Centre urbain
- Rive gauche

## 🎯 Qu'est-ce qu'une attente commune ?

Les propositions peuvent maintenant être de deux types :

### 1. Attente commune (par défaut)
Une proposition qui s'applique à **toutes les mairies**, quelle que soit leur spécificité.

**Exemple** : "Poursuivre le développement du ReVE" s'applique à toutes les communes de la métropole.

### 2. Attente spécifique
Une proposition qui ne s'applique qu'aux villes ayant **certaines spécificités**.

**Exemple** : "Développer les zones piétonnes en centre-ville" pourrait être spécifique aux villes "Centre urbain".

## 📋 Comment utiliser les spécificités ?

### Dans l'interface d'administration

#### 1. Gérer les spécificités

1. Connectez-vous à l'interface d'administration : `/admin`
2. Cliquez sur **"Spécificités"** dans le menu
3. Vous pouvez :
   - Voir la liste des spécificités existantes
   - Créer une nouvelle spécificité
   - Modifier une spécificité
   - Supprimer une spécificité
   - Voir combien de villes et propositions sont associées

#### 2. Associer des spécificités à une ville

1. Allez dans **"Villes"**
2. Cliquez sur **"Modifier"** pour une ville
3. Dans le champ **"Spécificités"**, sélectionnez une ou plusieurs spécificités
4. Cliquez sur **"Sauvegarder"**

**Exemple** : Pour Bordeaux, sélectionnez "Intra-rocade", "Centre urbain" et "Rive gauche"

#### 3. Créer une proposition spécifique

1. Allez dans **"Propositions"**
2. Créez une nouvelle proposition ou modifiez une existante
3. Par défaut, la case **"Attente commune"** est cochée
4. Pour créer une proposition spécifique :
   - **Décochez** "Attente commune"
   - Le champ **"Spécificités"** apparaît
   - Sélectionnez les spécificités concernées
5. Cliquez sur **"Sauvegarder"**

## 💡 Exemples d'utilisation

### Exemple 1 : Proposition commune

**Proposition** : "Mettre en place le Savoir Rouler À Vélo (SRAV)"
- ✅ Attente commune : **Cochée**
- 📍 S'applique à : **Toutes les 28 communes**

### Exemple 2 : Proposition spécifique aux zones urbaines

**Proposition** : "Créer des rues piétonnes permanentes"
- ❌ Attente commune : **Décochée**
- 🎯 Spécificités : **Centre urbain**
- 📍 S'applique à : Bordeaux, Bègles, Talence, Le Bouscat, Cenon, Floirac, Lormont (7 villes)

### Exemple 3 : Proposition spécifique à l'intra-rocade

**Proposition** : "Réduire le stationnement en voirie"
- ❌ Attente commune : **Décochée**
- 🎯 Spécificités : **Intra-rocade**
- 📍 S'applique à : Les 7 villes intra-rocade

### Exemple 4 : Proposition pour plusieurs spécificités

**Proposition** : "Développer les parkings vélo sécurisés"
- ❌ Attente commune : **Décochée**
- 🎯 Spécificités : **Centre urbain** ET **Intra-rocade**
- 📍 S'applique à : Toutes les villes ayant au moins une de ces spécificités

## 📊 Statistiques actuelles

Après le chargement des données de test :

- **6 spécificités** créées
- **28 villes** avec leurs spécificités
- **37 propositions** (toutes communes par défaut)

### Répartition des villes par spécificité

| Spécificité | Nombre de villes |
|-------------|------------------|
| Centre urbain | 7 |
| Extra-rocade | 21 |
| Intra-rocade | 7 |
| Périurbain | 21 |
| Rive droite | 11 |
| Rive gauche | 17 |

## 🔍 Comment savoir si une proposition s'applique à une ville ?

Une proposition s'applique à une ville si :

1. **C'est une attente commune** (case cochée)
   → S'applique à **toutes** les villes

2. **C'est une attente spécifique** (case décochée)
   → S'applique uniquement aux villes ayant **au moins une** des spécificités sélectionnées

### Exemple pratique

**Ville** : Pessac
**Spécificités** : Extra-rocade, Périurbain, Rive gauche

**Proposition A** : "Développer le ReVE"
- Attente commune : ✅ Oui
- **Résultat** : ✅ S'applique à Pessac

**Proposition B** : "Créer des zones piétonnes"
- Attente commune : ❌ Non
- Spécificités : Centre urbain
- **Résultat** : ❌ Ne s'applique PAS à Pessac (Pessac n'a pas "Centre urbain")

**Proposition C** : "Développer les pistes cyclables périurbaines"
- Attente commune : ❌ Non
- Spécificités : Périurbain
- **Résultat** : ✅ S'applique à Pessac (Pessac a "Périurbain")

## ⚙️ Fonctionnalités techniques

### Pour les développeurs

La méthode `appliesTo()` permet de vérifier programmatiquement si une proposition s'applique à une ville :

```php
$city = $cityRepository->findOneBySlug('bordeaux');
$proposition = $propositionRepository->find(1);

if ($proposition->appliesTo($city)) {
    echo "Cette proposition s'applique à Bordeaux";
}
```

### Requêtes utiles

```php
// Récupérer toutes les spécificités d'une ville
$city->getSpecificities();

// Récupérer toutes les villes d'une spécificité
$specificity->getCities();

// Récupérer toutes les propositions spécifiques à une spécificité
$specificity->getPropositions();

// Vérifier si une proposition est commune
$proposition->isCommonExpectation();
```

## 📚 Documentation complète

Pour plus de détails techniques, consultez :

- `docs/SPECIFICITY_FEATURE.md` - Documentation technique complète
- `docs/CHANGELOG_SPECIFICITY.md` - Journal des modifications
- `docs/TESTING_SPECIFICITY.md` - Guide de test
- `IMPLEMENTATION_SUMMARY.md` - Résumé de l'implémentation

## ❓ Questions fréquentes

### Puis-je créer mes propres spécificités ?

Oui ! Allez dans "Spécificités" et cliquez sur "Créer". Le slug sera généré automatiquement.

### Une ville peut-elle n'avoir aucune spécificité ?

Oui, c'est possible. Dans ce cas, seules les propositions "attentes communes" s'appliqueront à cette ville.

### Puis-je modifier les spécificités d'une ville après sa création ?

Oui, vous pouvez modifier les spécificités à tout moment dans l'interface d'administration.

### Que se passe-t-il si je supprime une spécificité ?

Les associations avec les villes et propositions seront supprimées automatiquement (CASCADE).

### Les propositions existantes sont-elles affectées ?

Non, toutes les propositions existantes sont automatiquement marquées comme "attentes communes" et continuent de s'appliquer à toutes les villes.

### Puis-je avoir plusieurs spécificités pour une même proposition ?

Oui ! Une proposition spécifique peut être associée à plusieurs spécificités. Elle s'appliquera alors aux villes ayant **au moins une** de ces spécificités.

## 🚀 Prochaines étapes

1. **Configurer les spécificités** de vos villes
2. **Identifier les propositions spécifiques** dans votre liste
3. **Marquer ces propositions** comme spécifiques et associer les bonnes spécificités
4. **Utiliser les filtres** pour analyser les données par spécificité

## 💬 Support

Pour toute question ou problème :
- Consultez la documentation dans le dossier `docs/`
- Contactez l'administrateur système
- Vérifiez les logs en cas d'erreur

---

**Version** : 1.0
**Date** : 30 septembre 2025

