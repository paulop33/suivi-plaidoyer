# Guide d'administration - Gestion des attentes

## Vue d'ensemble

Ce guide explique comment gérer les attentes (communes et spécifiques) dans l'interface d'administration.

## Accès

**URL** : `https://127.0.0.1:8000/admin`

## Deux types d'attentes

### 1. Attente commune
- **Où** : Dans le formulaire de la proposition
- **Quand** : Pour définir ce qui est attendu de TOUTES les mairies
- **Champ** : "Attente commune" (textarea)

### 2. Attentes spécifiques
- **Où** : Menu dédié "Attentes spécifiques"
- **Quand** : Pour définir des attentes différentes selon le type de territoire
- **Gestion** : Via un CRUD séparé

## Comment gérer les attentes

### Méthode 1 : Définir une attente commune

#### Étapes

1. **Allez dans "Propositions"**
2. **Cliquez sur "Modifier"** pour une proposition existante (ou "Créer" pour une nouvelle)
3. **Remplissez le champ "Attente commune"**
   - Exemple : "Mettre en place le SRAV dans toutes les écoles de la commune"
4. **Sauvegardez**

#### Résultat

- L'attente commune s'affiche pour toutes les villes sur le site public
- Une carte verte avec l'attente apparaît sur la page de la proposition

### Méthode 2 : Définir des attentes spécifiques

#### Étapes

1. **Allez dans "Attentes spécifiques"** (menu principal)
2. **Cliquez sur "Créer"**
3. **Remplissez le formulaire** :
   - **Proposition** : Sélectionnez la proposition concernée
   - **Spécificité** : Sélectionnez le type de territoire (Intra-rocade, Centre urbain, etc.)
   - **Attente** : Décrivez ce qui est attendu pour ce type de territoire
4. **Sauvegardez**
5. **Répétez** pour chaque spécificité concernée

#### Résultat

- Les attentes spécifiques s'affichent dans une carte bleue sur le site public
- Chaque attente est associée à son type de territoire

### Méthode 3 : Combiner attente commune + attentes spécifiques

#### Étapes

1. **Définissez d'abord l'attente commune** (Méthode 1)
2. **Puis créez des attentes spécifiques** (Méthode 2)

#### Résultat

- Sur le site public, les villes voient l'attente commune (carte verte)
- Les attentes spécifiques sont également affichées (carte bleue)
- Cela permet de donner un contexte général + des précisions par territoire

## Exemples pratiques

### Exemple 1 : Proposition simple (attente commune uniquement)

**Proposition** : "Mettre en place le SRAV"

**Configuration** :
1. Aller dans Propositions > Modifier la proposition
2. Remplir "Attente commune" :
   ```
   Mettre en place le Savoir Rouler À Vélo (SRAV) dans toutes les écoles de la commune
   ```
3. Sauvegarder

**Résultat sur le site public** :
- Carte verte avec l'attente commune
- Visible pour toutes les villes

### Exemple 2 : Proposition avec nuances (attente commune + spécifiques)

**Proposition** : "Développer les pistes cyclables"

**Configuration** :

1. **Attente commune** (dans Propositions) :
   ```
   Développer les pistes cyclables sur l'ensemble du territoire
   ```

2. **Attente spécifique 1** (dans Attentes spécifiques) :
   - Proposition : "Développer les pistes cyclables"
   - Spécificité : "Intra-rocade"
   - Attente :
     ```
     Créer un réseau cyclable continu et sécurisé avec des pistes séparées de la circulation automobile
     ```

3. **Attente spécifique 2** (dans Attentes spécifiques) :
   - Proposition : "Développer les pistes cyclables"
   - Spécificité : "Extra-rocade"
   - Attente :
     ```
     Développer des voies vertes et des liaisons intercommunales sécurisées
     ```

**Résultat sur le site public** :
- Carte verte avec l'attente commune
- Carte bleue avec 2 attentes spécifiques (Intra-rocade et Extra-rocade)

### Exemple 3 : Proposition ciblée (uniquement des attentes spécifiques)

**Proposition** : "Créer des zones piétonnes"

**Configuration** :

1. **Attente commune** (dans Propositions) :
   - Laisser vide

2. **Attente spécifique 1** (dans Attentes spécifiques) :
   - Proposition : "Créer des zones piétonnes"
   - Spécificité : "Centre urbain"
   - Attente :
     ```
     Créer des zones piétonnes permanentes et des rues à circulation apaisée
     ```

3. **Attente spécifique 2** (dans Attentes spécifiques) :
   - Proposition : "Créer des zones piétonnes"
   - Spécificité : "Périurbain"
   - Attente :
     ```
     Aménager les centres-bourgs avec des zones 30 et des traversées sécurisées
     ```

**Résultat sur le site public** :
- Pas de carte verte (pas d'attente commune)
- Carte bleue avec 2 attentes spécifiques (Centre urbain et Périurbain)
- Les villes sans ces spécificités ne voient aucune attente pour cette proposition

## Visualiser les attentes d'une proposition

### Dans l'interface d'administration

1. **Allez dans "Propositions"**
2. **Cliquez sur une proposition**
3. **Regardez le champ "Attentes spécifiques"**
   - Il affiche le nombre d'attentes spécifiques : "2 attente(s) spécifique(s)"
4. **Pour voir le détail**, allez dans "Attentes spécifiques" et filtrez par proposition

### Sur le site public

1. **Allez sur** `https://127.0.0.1:8000/propositions/{id}`
2. **Regardez la section** "Ce que nous attendons des mairies"
   - Carte verte = Attente commune
   - Carte bleue = Attentes spécifiques

## Modifier ou supprimer des attentes

### Modifier une attente commune

1. Allez dans "Propositions"
2. Cliquez sur "Modifier" pour la proposition
3. Modifiez le champ "Attente commune"
4. Sauvegardez

### Modifier une attente spécifique

1. Allez dans "Attentes spécifiques"
2. Trouvez l'attente à modifier
3. Cliquez sur "Modifier"
4. Modifiez le texte de l'attente
5. Sauvegardez

### Supprimer une attente spécifique

1. Allez dans "Attentes spécifiques"
2. Trouvez l'attente à supprimer
3. Cliquez sur "Supprimer"
4. Confirmez

## Bonnes pratiques

### 1. Commencez par l'attente commune

Si votre proposition s'applique à toutes les villes, commencez par définir une attente commune. Vous pourrez toujours ajouter des attentes spécifiques plus tard.

### 2. Soyez précis dans les attentes spécifiques

Les attentes spécifiques doivent être concrètes et adaptées au contexte du territoire.

**Bon exemple** :
```
Intra-rocade : Créer un réseau cyclable continu avec pistes séparées de la circulation automobile
```

**Mauvais exemple** :
```
Intra-rocade : Faire des pistes cyclables
```

### 3. Utilisez les attentes spécifiques pour les nuances

Si votre proposition nécessite des actions différentes selon le contexte urbain, utilisez les attentes spécifiques.

### 4. Vérifiez sur le site public

Après avoir créé ou modifié des attentes, vérifiez le rendu sur le site public pour vous assurer que tout s'affiche correctement.

## Dépannage

### Problème : L'attente commune ne s'affiche pas

**Solution** :
- Vérifiez que le champ "Attente commune" n'est pas vide
- Videz le cache : `php bin/console cache:clear`
- Rechargez la page

### Problème : Les attentes spécifiques ne s'affichent pas

**Solution** :
- Vérifiez que vous avez bien créé des attentes spécifiques dans le menu dédié
- Vérifiez que la proposition et la spécificité sont bien sélectionnées
- Videz le cache : `php bin/console cache:clear`

### Problème : Erreur lors de la modification d'une proposition

**Solution** :
- Ne tentez pas de modifier les attentes spécifiques depuis le formulaire de proposition
- Utilisez le menu "Attentes spécifiques" pour gérer les attentes spécifiques

## Raccourcis

### Créer rapidement une attente spécifique

1. Depuis "Propositions", notez l'ID de la proposition
2. Allez dans "Attentes spécifiques" > "Créer"
3. Sélectionnez la proposition et la spécificité
4. Remplissez l'attente
5. Sauvegardez

### Voir toutes les attentes d'une proposition

1. Allez dans "Attentes spécifiques"
2. Utilisez le filtre ou la recherche pour trouver la proposition
3. Vous verrez toutes les attentes spécifiques associées

## Statistiques

Pour voir combien d'attentes sont définies :

1. **Propositions avec attente commune** :
   - Allez dans "Propositions"
   - Regardez la colonne "Attente commune" (si affichée)

2. **Propositions avec attentes spécifiques** :
   - Allez dans "Propositions"
   - Regardez la colonne "Attentes spécifiques" qui affiche le nombre

3. **Total des attentes spécifiques** :
   - Allez dans "Attentes spécifiques"
   - Le nombre total est affiché en haut de la liste

## Support

Pour toute question ou problème :
- Consultez `docs/EXPECTATIONS_FEATURE.md` pour la documentation technique
- Consultez `README_ATTENTES.md` pour le guide utilisateur
- Consultez `docs/FRONTEND_EXPECTATIONS.md` pour l'affichage sur le site public

