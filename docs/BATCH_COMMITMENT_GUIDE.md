# Guide d'utilisation : Engagement en masse pour les listes candidates

## Vue d'ensemble

Cette fonctionnalité permet aux administrateurs de créer des engagements pour une liste candidate sur toutes les propositions en une seule fois, directement depuis le back office EasyAdmin.

## Comment utiliser la fonctionnalité

### 1. Accéder à la fonctionnalité

1. Connectez-vous au back office : `/admin`
2. Naviguez vers la section "Listes" dans le menu
3. Sélectionnez une liste candidate (soit depuis l'index, soit en consultant les détails d'une liste)
4. Cliquez sur le bouton **"Engager sur toutes les propositions"** (icône double check ✓✓)

### 2. Utiliser l'interface d'engagement en masse

L'interface vous présente :

#### Informations de la liste
- Nom de la liste
- Nom et prénom du contact
- Ville associée

#### Commentaire global (optionnel)
- Zone de texte pour saisir un commentaire qui sera appliqué à tous les engagements sélectionnés
- Limite de 1000 caractères
- Compteur de caractères en temps réel

#### Sélection des propositions
- Liste de toutes les propositions organisées par catégorie
- Cases à cocher pour sélectionner les propositions
- Boutons "Tout sélectionner" / "Tout désélectionner"
- Indication visuelle des engagements existants (badge "Déjà engagé")

### 3. Fonctionnalités avancées

#### Gestion des engagements existants
- Les propositions pour lesquelles la liste est déjà engagée sont pré-cochées
- Le système met à jour le commentaire des engagements existants si un nouveau commentaire global est fourni
- Aucun doublon n'est créé

#### Validation et sécurité
- Validation côté client et serveur
- Confirmation avant soumission
- Gestion des erreurs avec messages informatifs
- Transaction de base de données pour assurer la cohérence

#### Interface utilisateur
- Compteur en temps réel du nombre d'engagements sélectionnés
- Validation de la longueur du commentaire
- Interface responsive et intuitive

## Messages de retour

### Messages de succès
- `X engagement(s) créé(s) et Y mis à jour pour la liste "Nom de la liste"`

### Messages d'erreur
- `Veuillez sélectionner au moins une proposition.`
- `Le commentaire ne peut pas dépasser 1000 caractères.`
- `Une erreur est survenue lors de l'enregistrement des engagements.`

### Messages d'avertissement
- `X erreur(s) rencontrée(s) lors du traitement. Certains engagements n'ont pas pu être créés.`

## Aspects techniques

### Sécurité
- Validation des données côté serveur
- Protection contre les injections SQL via Doctrine ORM
- Gestion des transactions pour éviter les états incohérents

### Performance
- Utilisation de requêtes optimisées pour éviter les problèmes N+1
- Traitement par lot pour les engagements multiples
- Cache clearing automatique si nécessaire

### Base de données
- Respect des contraintes d'intégrité référentielle
- Gestion automatique des dates de création et mise à jour
- Prévention des doublons via vérification avant insertion

## Cas d'usage typiques

1. **Nouvelle liste candidate** : Engager rapidement une nouvelle liste sur toutes les propositions pertinentes
2. **Mise à jour en masse** : Ajouter un commentaire global à tous les engagements existants
3. **Engagement sélectif** : Choisir spécifiquement les propositions sur lesquelles engager la liste
4. **Correction d'erreurs** : Mettre à jour les commentaires d'engagements existants

## Limitations

- Maximum 1000 caractères pour le commentaire global
- Fonctionnalité réservée aux administrateurs du back office
- Traitement séquentiel des propositions (pas de parallélisation)

## Support et dépannage

En cas de problème :
1. Vérifiez les logs d'erreur PHP
2. Consultez les messages flash dans l'interface
3. Vérifiez que la base de données est accessible
4. Assurez-vous que les permissions sont correctes

## Évolutions futures possibles

- Export des résultats d'engagement en masse
- Planification d'engagements différés
- Notifications par email aux listes concernées
- Interface de désengagement en masse
- Historique des actions d'engagement en masse
