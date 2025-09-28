# Guide de démarrage rapide - Administration

## 🚀 Accès à l'administration

### 1. Connexion
1. Accédez à : `https://votre-domaine.com/admin`
2. Vous serez automatiquement redirigé vers la page de connexion
3. Utilisez un des comptes par défaut :

#### Comptes par défaut
| Rôle | Email | Mot de passe | Permissions |
|------|-------|--------------|-------------|
| **Super Admin** | `admin@suivi-plaidoyer.fr` | `admin123` | Accès complet |
| **Association** | `association@example.com` | `password123` | Accès limité |

> ⚠️ **Important** : Changez ces mots de passe par défaut en production !

### 2. Interface d'administration
Après connexion, vous accédez au dashboard EasyAdmin avec :
- **Dashboard** : Vue d'ensemble avec graphiques
- **Gestion des données** : Associations, Catégories, Propositions, etc.
- **Administration** : Gestion des utilisateurs (Super Admin uniquement)
- **Mon compte** : Profil et déconnexion

## 👑 Super Administrateur

### Gestion des utilisateurs
1. **Menu** → Administration → Utilisateurs
2. **Créer un utilisateur** :
   - Cliquez sur "Créer Utilisateur"
   - Remplissez les informations obligatoires
   - Sélectionnez le(s) rôle(s) approprié(s)
   - Pour un utilisateur association, sélectionnez l'association
   - Définissez un mot de passe temporaire
3. **Modifier un utilisateur** :
   - Cliquez sur l'icône "Modifier" dans la liste
   - Modifiez les informations nécessaires
   - Laissez le champ mot de passe vide pour le conserver

### Rôles disponibles
- **Super Admin** : Accès complet à toutes les fonctionnalités
- **Association** : Accès limité pour le suivi des propositions

### Gestion des contenus
Le Super Admin peut gérer tous les contenus :
- **Associations** : Créer, modifier, supprimer les associations
- **Catégories** : Organiser les propositions par thèmes
- **Propositions** : Gérer les propositions de plaidoyer
- **Villes** : Administrer les communes de la métropole
- **Listes** : Gérer les listes candidates
- **Engagements** : Suivre les engagements pris

## 🏢 Utilisateur Association

### Accès limité
Les utilisateurs avec le rôle Association peuvent :
- ✅ Consulter le dashboard
- ✅ Voir les données existantes
- ✅ Accéder à leur profil
- ❌ Gérer les utilisateurs
- ❌ Supprimer des données critiques

### Développement futur
Les fonctionnalités suivantes seront ajoutées :
- Suivi spécifique des propositions de leur association
- Mise à jour du statut des engagements
- Génération de rapports personnalisés
- Notifications par email

## 🔧 Fonctionnalités communes

### Profil utilisateur
1. **Menu** → Mon compte → Profil
2. Consultez vos informations personnelles
3. Voyez vos rôles et association liée
4. Les Super Admins peuvent modifier leur profil directement

### Déconnexion
1. **Menu** → Mon compte → Déconnexion
2. Ou cliquez sur l'icône de déconnexion dans la barre de navigation

### Navigation
- **Site public** : Lien vers la partie publique du site
- **Dashboard** : Retour à l'accueil admin
- **Breadcrumb** : Navigation contextuelle dans les sections

## 📊 Dashboard et statistiques

### Vue d'ensemble
Le dashboard affiche :
- Graphiques de suivi des engagements
- Statistiques globales
- Accès rapide aux fonctionnalités principales

### Données en temps réel
- Nombre total de propositions
- Engagements par commune
- Taux de signature par catégorie
- Évolution temporelle

## 🛠️ Gestion des données

### Associations
- **Nom** : Nom de l'association
- **Couleur** : Code couleur pour l'affichage
- **Image** : URL de l'image/logo
- **Villes** : Communes associées

### Catégories
- **Nom** : Titre de la catégorie
- **Description** : Description détaillée
- **Barème** : Points maximum pour la catégorie
- **Position** : Ordre d'affichage
- **Image** : Illustration de la catégorie

### Propositions
- **Titre** : Intitulé de la proposition
- **Description** : Détails de la proposition
- **Barème** : Points attribués
- **Catégorie** : Catégorie parente
- **Position** : Ordre dans la catégorie

### Villes
- **Nom** : Nom de la commune
- **Slug** : URL-friendly (généré automatiquement)
- **Associations** : Associations référentes

### Listes candidates
- **Nom de la liste** : Nom de la liste électorale
- **Prénom/Nom** : Contact principal
- **Email/Téléphone** : Coordonnées
- **Ville** : Commune concernée

### Engagements
- **Liste** : Liste candidate qui s'engage
- **Proposition** : Proposition concernée
- **Ville** : Commune de l'engagement
- **Statut** : État de l'engagement

## 🔒 Sécurité et bonnes pratiques

### Mots de passe
- Utilisez des mots de passe forts (8+ caractères, majuscules, chiffres, symboles)
- Changez régulièrement les mots de passe
- Ne partagez jamais vos identifiants

### Sessions
- Déconnectez-vous après utilisation
- Les sessions expirent automatiquement
- Option "Se souvenir de moi" pour les appareils personnels

### Permissions
- Respectez les niveaux d'accès
- Ne créez que les comptes nécessaires
- Désactivez les comptes inutilisés

## 🆘 Dépannage

### Problèmes de connexion
1. Vérifiez l'email et le mot de passe
2. Assurez-vous que le compte est actif
3. Videz le cache du navigateur si nécessaire
4. Contactez un Super Admin si nécessaire

### Erreurs d'accès
- "Access Denied" : Permissions insuffisantes
- Contactez un Super Admin pour ajuster les rôles

### Performance
- Utilisez les filtres pour limiter les résultats
- Évitez de charger trop de données simultanément

### Support technique
En cas de problème technique :
1. Notez l'erreur exacte
2. Indiquez les étapes pour reproduire
3. Contactez l'équipe technique avec ces informations

## 📱 Responsive Design

L'interface d'administration est optimisée pour :
- **Desktop** : Expérience complète
- **Tablette** : Navigation adaptée
- **Mobile** : Fonctionnalités essentielles

## 🔄 Mises à jour

### Notifications
- Les mises à jour importantes seront communiquées
- Vérifiez régulièrement les nouveautés
- Consultez la documentation mise à jour

### Nouvelles fonctionnalités
Les prochaines versions incluront :
- Amélioration du suivi des propositions
- Notifications automatiques
- Rapports avancés
- API pour intégrations externes

## 📞 Contact et support

Pour toute question ou assistance :
- **Documentation** : Consultez les guides dans `/docs`
- **Support technique** : Contactez l'équipe de développement
- **Formation** : Sessions de formation disponibles sur demande

---

> 💡 **Conseil** : Explorez l'interface progressivement et n'hésitez pas à tester les fonctionnalités en mode développement avant la mise en production.
