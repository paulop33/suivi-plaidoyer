# Guide utilisateur - Attentes communes et spécifiques

## 🎯 Qu'est-ce qu'une attente ?

Une **attente** est ce que vous attendez concrètement d'une mairie pour une proposition donnée.

Il existe deux types d'attentes :

### 1. Attente commune
Ce qui est attendu de **toutes les mairies**, quelle que soit leur spécificité.

**Exemple** : Pour la proposition "Mettre en place le SRAV"
- Attente commune : "Mettre en place le Savoir Rouler À Vélo dans toutes les écoles de la commune"

### 2. Attentes spécifiques
Ce qui est attendu pour **chaque groupe de spécificités** (intra-rocade, extra-rocade, centre urbain, etc.).

**Exemple** : Pour la proposition "Développer les pistes cyclables"
- Attente pour les villes **intra-rocade** : "Créer un réseau cyclable continu et sécurisé avec des pistes séparées de la circulation"
- Attente pour les villes **extra-rocade** : "Développer des voies vertes et des liaisons intercommunales sécurisées"

## 📋 Comment ça fonctionne ?

### Cas 1 : Attente commune uniquement

Vous définissez **une seule attente** qui s'applique à toutes les villes.

**Exemple** :
- Proposition : "Mettre en place le SRAV"
- Attente commune : "Mettre en place le SRAV dans toutes les écoles"
- Attentes spécifiques : (aucune)

**Résultat** : Bordeaux, Pessac, Cenon, etc. ont tous la même attente.

### Cas 2 : Attente commune + attentes spécifiques

Vous définissez **une attente générale** ET **des attentes plus précises** pour certaines spécificités.

**Exemple** :
- Proposition : "Développer les pistes cyclables"
- Attente commune : "Développer les pistes cyclables sur l'ensemble du territoire"
- Attentes spécifiques :
  - Intra-rocade : "Créer un réseau cyclable continu et sécurisé"
  - Extra-rocade : "Développer des voies vertes intercommunales"

**Résultat** :
- Bordeaux (intra-rocade) voit : "Créer un réseau cyclable continu et sécurisé"
- Pessac (extra-rocade) voit : "Développer des voies vertes intercommunales"
- Une ville sans spécificité voit : "Développer les pistes cyclables sur l'ensemble du territoire"

### Cas 3 : Uniquement des attentes spécifiques

Vous définissez **uniquement des attentes pour certaines spécificités**, sans attente commune.

**Exemple** :
- Proposition : "Aménager les espaces publics"
- Attente commune : (vide)
- Attentes spécifiques :
  - Centre urbain : "Créer des zones piétonnes permanentes"
  - Périurbain : "Aménager les centres-bourgs avec des zones 30"

**Résultat** :
- Bordeaux (centre urbain) voit : "Créer des zones piétonnes permanentes"
- Pessac (périurbain) voit : "Aménager les centres-bourgs avec des zones 30"
- Une ville sans ces spécificités ne voit **aucune attente** pour cette proposition

## 🔍 Quelle attente est affichée ?

Le système suit cette logique :

1. **Si une attente commune existe** → Elle est affichée pour toutes les villes
2. **Sinon, si une attente spécifique correspond à la ville** → Elle est affichée
3. **Sinon** → Aucune attente n'est affichée

**Priorité** : Attente commune > Attente spécifique > Aucune

## 💻 Comment créer des attentes ?

### Dans l'interface d'administration

1. **Allez dans "Propositions"**
2. **Créez ou éditez une proposition**
3. Vous verrez deux sections :

#### Section "Attente commune"
- Un champ texte libre
- Remplissez-le si vous voulez une attente pour toutes les villes
- Laissez-le vide si vous voulez uniquement des attentes spécifiques

#### Section "Attentes spécifiques"
- Une liste d'attentes par spécificité
- Cliquez sur "Ajouter" pour créer une nouvelle attente spécifique
- Pour chaque attente :
  - Sélectionnez la **spécificité** (Intra-rocade, Extra-rocade, etc.)
  - Écrivez le **texte de l'attente**

4. **Sauvegardez**

### Menu "Attentes spécifiques"

Vous pouvez aussi gérer les attentes spécifiques directement via le menu dédié :

1. **Allez dans "Attentes spécifiques"**
2. **Créez une nouvelle attente**
3. Remplissez :
   - **Proposition** : Sélectionnez la proposition concernée
   - **Spécificité** : Sélectionnez la spécificité (Intra-rocade, Centre urbain, etc.)
   - **Attente** : Écrivez le texte de l'attente

## 📊 Exemples concrets

### Exemple 1 : Proposition simple

**Proposition** : "Soutenir la mise en place d'une métropole à 30km/h"

**Configuration** :
- Attente commune : "Mettre en place la limitation à 30km/h sur l'ensemble de la commune"
- Attentes spécifiques : (aucune)

**Résultat** : Toutes les villes voient la même attente.

### Exemple 2 : Proposition avec nuances

**Proposition** : "Développer les infrastructures cyclables"

**Configuration** :
- Attente commune : "Développer les pistes cyclables"
- Attentes spécifiques :
  - Intra-rocade : "Créer un réseau cyclable continu avec pistes séparées"
  - Extra-rocade : "Développer des voies vertes et liaisons intercommunales"
  - Centre urbain : "Aménager des rues cyclables et des zones de rencontre"

**Résultat** :
- Bordeaux (intra-rocade + centre urbain) voit : "Créer un réseau cyclable continu avec pistes séparées" (première spécificité trouvée)
- Pessac (extra-rocade) voit : "Développer des voies vertes et liaisons intercommunales"
- Une petite commune sans spécificité voit : "Développer les pistes cyclables"

### Exemple 3 : Proposition ciblée

**Proposition** : "Créer des zones piétonnes"

**Configuration** :
- Attente commune : (vide)
- Attentes spécifiques :
  - Centre urbain : "Créer des zones piétonnes permanentes en centre-ville"
  - Intra-rocade : "Piétonniser les rues commerçantes"

**Résultat** :
- Bordeaux (centre urbain) voit : "Créer des zones piétonnes permanentes en centre-ville"
- Cenon (intra-rocade) voit : "Piétonniser les rues commerçantes"
- Pessac (extra-rocade) ne voit **aucune attente** pour cette proposition

## 🎨 Bonnes pratiques

### 1. Utilisez l'attente commune pour les propositions universelles

Si votre proposition s'applique de la même manière à toutes les villes, utilisez uniquement l'attente commune.

**Exemple** : "Former les agents municipaux au vélo"

### 2. Utilisez les attentes spécifiques pour adapter le message

Si votre proposition nécessite des actions différentes selon le contexte urbain, utilisez les attentes spécifiques.

**Exemple** : "Développer les pistes cyclables" (différent en ville dense vs périurbain)

### 3. Combinez les deux pour avoir un filet de sécurité

Définissez une attente commune générale + des attentes spécifiques plus précises. Ainsi, même les villes sans spécificité auront une attente.

### 4. Soyez précis dans les attentes spécifiques

Les attentes spécifiques doivent être concrètes et adaptées au contexte.

**Bon** : "Créer un réseau cyclable continu avec pistes séparées de la circulation automobile"
**Moins bon** : "Faire des pistes cyclables"

## 📈 Statistiques actuelles

Après le chargement des données de test :

- **37 propositions** créées
- **3 propositions** avec des attentes définies :
  - 1 avec attente commune uniquement
  - 1 avec attente commune + 2 attentes spécifiques
  - 1 avec 2 attentes spécifiques uniquement
- **4 attentes spécifiques** créées au total

## ❓ Questions fréquentes

### Puis-je avoir plusieurs attentes spécifiques pour une même proposition ?

Oui ! Vous pouvez créer autant d'attentes spécifiques que vous avez de spécificités.

### Que se passe-t-il si une ville a plusieurs spécificités ?

Le système retourne la première attente spécifique trouvée. Si vous avez défini une attente commune, elle sera prioritaire.

### Puis-je modifier les attentes après création ?

Oui, vous pouvez modifier les attentes à tout moment dans l'interface d'administration.

### Que se passe-t-il si je ne définis aucune attente ?

La proposition existera toujours, mais aucune attente ne sera affichée pour les villes. C'est utile si vous voulez créer la structure avant de définir les attentes.

### Puis-je supprimer une attente spécifique ?

Oui, vous pouvez supprimer des attentes spécifiques à tout moment.

## 🚀 Pour aller plus loin

### Consulter la documentation technique

Pour plus de détails sur l'implémentation, consultez :
- `docs/EXPECTATIONS_FEATURE.md` - Documentation technique complète
- `docs/SPECIFICITY_FEATURE.md` - Documentation sur les spécificités

### Tester les attentes

Vous pouvez tester les attentes en :
1. Créant des propositions avec différentes configurations
2. Consultant les villes pour voir quelles attentes s'affichent
3. Utilisant les requêtes SQL pour vérifier les données

### Exporter les données

Vous pouvez exporter les attentes par spécificité pour créer des rapports personnalisés.

---

**Version** : 2.0
**Date** : 30 septembre 2025

