# Documentation - Suivi Plaidoyer

Bienvenue dans la documentation du système de suivi des engagements de plaidoyer pour Bordeaux Métropole.

## 📚 Guide de navigation

### 🚀 Pour commencer rapidement
- **[Guide de démarrage rapide](ADMIN_QUICK_START.md)** - Pour les administrateurs et utilisateurs
- **[Système d'authentification](AUTHENTICATION_SYSTEM.md)** - Vue d'ensemble du système de droits

### 🔧 Pour les développeurs
- **[Référence technique](TECHNICAL_REFERENCE.md)** - Documentation technique complète
- **[Architecture](AUTHENTICATION_SYSTEM.md#architecture)** - Structure du code et des entités

## 🎯 Aperçu du système

### Qu'est-ce que Suivi Plaidoyer ?
Suivi Plaidoyer est une plateforme web qui permet de :
- Suivre les engagements des listes candidates dans les communes de Bordeaux Métropole
- Analyser les propositions par catégorie et par commune
- Générer des statistiques et rapports de suivi
- Gérer les associations et leurs propositions

### Système de droits
Le système utilise deux rôles principaux :

#### 👑 Super Administrateur
- Accès complet à toutes les fonctionnalités
- Gestion des utilisateurs et des droits
- Administration de tous les contenus
- Accès aux statistiques globales

#### 🏢 Association
- Accès limité pour le suivi des propositions
- Gestion des contenus liés à leur association
- Consultation des données publiques
- Fonctionnalités de suivi (développement futur)

## 🗂️ Structure de la documentation

```
docs/
├── README.md                    # Ce fichier - Navigation générale
├── ADMIN_QUICK_START.md        # Guide rapide pour les administrateurs
├── AUTHENTICATION_SYSTEM.md    # Documentation complète du système d'auth
└── TECHNICAL_REFERENCE.md      # Référence technique pour développeurs
```

## 🔐 Comptes par défaut

Pour tester le système, utilisez ces comptes :

| Rôle | Email | Mot de passe | Usage |
|------|-------|--------------|-------|
| **Super Admin** | `admin@suivi-plaidoyer.fr` | `admin123` | Administration complète |
| **Association** | `association@example.com` | `password123` | Test des permissions limitées |

> ⚠️ **Important** : Changez ces mots de passe en production !

## 🚀 Démarrage rapide

### 1. Installation et configuration
```bash
# Cloner le projet
git clone [repository-url]
cd suivi-plaidoyer

# Installer les dépendances
composer install
npm install

# Configurer la base de données
cp .env .env.local
# Éditer .env.local avec vos paramètres de base de données

# Créer la base et appliquer les migrations
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate

# Charger les données de test
php bin/console doctrine:fixtures:load
```

### 2. Lancement du serveur
```bash
# Démarrer le serveur Symfony
symfony serve -d

# Ou avec PHP
php -S localhost:8000 -t public/
```

### 3. Accès à l'administration
1. Ouvrir `http://localhost:8000/admin`
2. Se connecter avec un compte par défaut
3. Explorer l'interface d'administration

## 📖 Guides par profil

### 👨‍💼 Administrateurs
1. **[Démarrage rapide](ADMIN_QUICK_START.md)** - Premiers pas
2. **[Gestion des utilisateurs](ADMIN_QUICK_START.md#gestion-des-utilisateurs)** - Créer et gérer les comptes
3. **[Gestion des contenus](ADMIN_QUICK_START.md#gestion-des-contenus)** - Administrer les données

### 🏢 Utilisateurs Association
1. **[Accès limité](ADMIN_QUICK_START.md#utilisateur-association)** - Vos permissions
2. **[Dashboard](ADMIN_QUICK_START.md#dashboard-et-statistiques)** - Vue d'ensemble
3. **[Profil](ADMIN_QUICK_START.md#profil-utilisateur)** - Gérer votre compte

### 👨‍💻 Développeurs
1. **[Architecture](TECHNICAL_REFERENCE.md#architecture-technique)** - Structure du code
2. **[Entités](TECHNICAL_REFERENCE.md#entité-user)** - Modèle de données
3. **[Sécurité](TECHNICAL_REFERENCE.md#configuration-de-sécurité)** - Configuration Symfony
4. **[Tests](TECHNICAL_REFERENCE.md#tests)** - Tests automatisés

## 🔧 Fonctionnalités principales

### ✅ Implémentées
- ✅ Système d'authentification complet
- ✅ Gestion des rôles et permissions
- ✅ Interface d'administration EasyAdmin
- ✅ CRUD pour tous les contenus
- ✅ Dashboard avec statistiques
- ✅ Gestion des utilisateurs
- ✅ Profils utilisateurs
- ✅ Sécurité et protection CSRF

### 🚧 En développement
- 🚧 Suivi avancé des propositions par association
- 🚧 Notifications par email
- 🚧 Rapports personnalisés
- 🚧 API REST pour intégrations
- 🚧 Audit trail des actions

### 🔮 Prévues
- 🔮 Intégration SSO (Single Sign-On)
- 🔮 Application mobile
- 🔮 Exports avancés (PDF, Excel)
- 🔮 Workflow de validation
- 🔮 Système de commentaires

## 🛠️ Technologies utilisées

### Backend
- **Symfony 7.3** - Framework PHP
- **Doctrine ORM** - Mapping objet-relationnel
- **PostgreSQL** - Base de données
- **EasyAdmin 4** - Interface d'administration

### Frontend
- **Bootstrap 5** - Framework CSS
- **Stimulus** - Framework JavaScript
- **Chart.js** - Graphiques et statistiques
- **Font Awesome** - Icônes

### Outils de développement
- **Composer** - Gestionnaire de dépendances PHP
- **NPM** - Gestionnaire de dépendances JavaScript
- **Symfony CLI** - Outils de développement
- **PHPUnit** - Tests unitaires

## 📞 Support et contribution

### 🆘 Besoin d'aide ?
1. **Documentation** : Consultez les guides appropriés ci-dessus
2. **Issues** : Vérifiez les problèmes connus dans le repository
3. **Support** : Contactez l'équipe de développement

### 🤝 Contribuer
1. **Fork** le repository
2. **Créer** une branche pour votre fonctionnalité
3. **Tester** vos modifications
4. **Soumettre** une pull request

### 📋 Conventions
- **Code** : Suivre les standards Symfony
- **Commits** : Messages clairs et descriptifs
- **Tests** : Ajouter des tests pour les nouvelles fonctionnalités
- **Documentation** : Mettre à jour la documentation si nécessaire

## 🔄 Mises à jour

### Changelog
Les modifications importantes sont documentées dans :
- **Git tags** pour les versions
- **Release notes** pour les fonctionnalités
- **Migration guides** pour les changements majeurs

### Notifications
- **Email** : Notifications des mises à jour importantes
- **Dashboard** : Alertes dans l'interface admin
- **Documentation** : Mise à jour continue des guides

## 📊 Métriques et monitoring

### Statistiques d'usage
- Nombre d'utilisateurs actifs
- Propositions suivies
- Engagements enregistrés
- Performance du système

### Monitoring technique
- Logs d'erreurs et de sécurité
- Performance des requêtes
- Utilisation des ressources
- Temps de réponse

---

## 🎯 Prochaines étapes

1. **Lisez** le guide approprié à votre profil
2. **Testez** le système avec les comptes par défaut
3. **Explorez** les fonctionnalités disponibles
4. **Contactez** l'équipe pour toute question

> 💡 **Conseil** : Commencez par le [Guide de démarrage rapide](ADMIN_QUICK_START.md) pour une prise en main immédiate !

---

*Documentation mise à jour le 28 septembre 2025*
