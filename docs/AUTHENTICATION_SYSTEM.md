# Système d'authentification et de gestion des droits

## Vue d'ensemble

Le système d'authentification de Suivi Plaidoyer utilise Symfony Security avec deux rôles principaux :
- **ROLE_SUPER_ADMIN** : Accès complet à toutes les fonctionnalités
- **ROLE_ASSOCIATION** : Accès limité pour le suivi des propositions

## Architecture

### Entités

#### User (`src/Entity/User.php`)
```php
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    const ROLE_SUPER_ADMIN = 'ROLE_SUPER_ADMIN';
    const ROLE_ASSOCIATION = 'ROLE_ASSOCIATION';
    
    private string $email;           // Identifiant unique
    private array $roles;            // Rôles de l'utilisateur
    private string $password;        // Mot de passe haché
    private string $firstname;       // Prénom
    private string $lastname;        // Nom
    private ?Association $association; // Association liée (pour ROLE_ASSOCIATION)
    private bool $isActive;          // Statut actif/inactif
}
```

#### Association (`src/Entity/Association.php`)
- Relation OneToMany avec User
- Permet d'associer des utilisateurs à des associations spécifiques

### Configuration de sécurité

#### `config/packages/security.yaml`
```yaml
security:
    password_hashers:
        App\Entity\User:
            algorithm: auto

    providers:
        app_user_provider:
            entity:
                class: App\Entity\User
                property: email

    firewalls:
        main:
            provider: app_user_provider
            form_login:
                login_path: app_login
                check_path: app_login
                default_target_path: admin
            logout:
                path: app_logout
                target: app_login
            remember_me:
                secret: '%kernel.secret%'
                lifetime: 604800 # 1 semaine

    access_control:
        - { path: ^/login, roles: PUBLIC_ACCESS }
        - { path: ^/admin, roles: ROLE_USER }
        - { path: ^/admin/user, roles: ROLE_SUPER_ADMIN }

    role_hierarchy:
        ROLE_ASSOCIATION: ROLE_USER
        ROLE_SUPER_ADMIN: [ROLE_ASSOCIATION, ROLE_ALLOWED_TO_SWITCH]
```

## Contrôleurs

### SecurityController (`src/Controller/SecurityController.php`)
- Gère la connexion et déconnexion
- Route `/login` pour l'authentification
- Route `/logout` pour la déconnexion

### UserManagementController (`src/Controller/Admin/UserManagementController.php`)
- Contrôleur personnalisé pour la gestion des utilisateurs
- Accessible uniquement aux ROLE_SUPER_ADMIN
- Hachage automatique des mots de passe
- Validation des rôles et associations
- Interface utilisateur dédiée avec formulaires Symfony

### ProfileController (`src/Controller/ProfileController.php`)
- Page de profil utilisateur
- Route `/admin/profile`
- Accessible à tous les utilisateurs connectés

## Templates

### Page de connexion (`templates/security/login.html.twig`)
- Interface moderne et responsive
- Protection CSRF
- Option "Se souvenir de moi"
- Redirection automatique après connexion

### Page de profil (`templates/admin/profile.html.twig`)
- Affichage des informations utilisateur
- Badges pour les rôles
- Informations sur l'association liée

## Fixtures et données de test

### UserFixtures (`src/DataFixtures/UserFixtures.php`)
Crée automatiquement :
- **Super Admin** : `admin@suivi-plaidoyer.fr` / `admin123`
- **Association** : `association@example.com` / `password123`

## Utilisation

### Connexion
1. Accéder à `/login`
2. Saisir email et mot de passe
3. Redirection automatique vers `/admin`

### Gestion des utilisateurs (Super Admin uniquement)
1. Se connecter en tant que Super Admin
2. Accéder au menu "Administration" > "Utilisateurs"
3. Créer, modifier ou supprimer des utilisateurs
4. Assigner des rôles et associations

### Profil utilisateur
1. Se connecter avec n'importe quel compte
2. Accéder au menu "Mon compte" > "Profil"
3. Consulter ses informations personnelles

## Permissions par rôle

### ROLE_SUPER_ADMIN
- ✅ Accès complet au dashboard admin
- ✅ Gestion des utilisateurs
- ✅ Gestion de tous les contenus (associations, catégories, propositions, etc.)
- ✅ Switch user (impersonation)
- ✅ Toutes les fonctionnalités

### ROLE_ASSOCIATION
- ✅ Accès au dashboard admin
- ✅ Gestion des contenus de base
- ✅ Suivi des propositions (développement futur)
- ❌ Gestion des utilisateurs
- ❌ Switch user

### ROLE_USER (base)
- ✅ Accès au dashboard admin
- ✅ Consultation du profil
- ❌ Gestion des contenus
- ❌ Fonctionnalités administratives

## Sécurité

### Mesures implémentées
- **Hachage des mots de passe** avec algorithme auto (bcrypt/argon2)
- **Protection CSRF** sur tous les formulaires
- **Validation des entrées** utilisateur
- **Contrôle d'accès** basé sur les rôles
- **Session sécurisée** avec remember me
- **Redirection sécurisée** après authentification

### Bonnes pratiques
- Mots de passe forts recommandés
- Sessions expirées automatiquement
- Logs d'authentification (via Symfony)
- Validation côté serveur

## Migration et déploiement

### Base de données
```bash
# Créer la migration
php bin/console make:migration

# Appliquer la migration
php bin/console doctrine:migrations:migrate

# Charger les fixtures
php bin/console doctrine:fixtures:load
```

### Variables d'environnement
```env
# .env
APP_SECRET=your-secret-key
DATABASE_URL=postgresql://user:pass@localhost:5432/db_name
```

## Développement futur

### Extensions prévues
- **Gestion fine des permissions** par association
- **Audit trail** des actions utilisateurs
- **Notifications** par email
- **API REST** avec authentification JWT
- **Intégration SSO** (Single Sign-On)

### Suivi des propositions
Les utilisateurs ROLE_ASSOCIATION pourront :
- Suivre les propositions de leur association
- Mettre à jour le statut des engagements
- Générer des rapports de suivi
- Recevoir des notifications

## Dépannage

### Problèmes courants

#### Erreur "Access Denied"
- Vérifier que l'utilisateur a le bon rôle
- Contrôler la configuration `access_control`
- Vérifier que l'utilisateur est actif (`isActive = true`)

#### Problème de connexion
- Vérifier l'email et le mot de passe
- Contrôler que l'utilisateur existe en base
- Vérifier la configuration du provider

#### Erreur CSRF
- Vérifier que le token CSRF est présent dans le formulaire
- Contrôler la configuration CSRF dans `security.yaml`

### Logs utiles
```bash
# Logs de sécurité
tail -f var/log/dev.log | grep security

# Logs d'authentification
tail -f var/log/dev.log | grep authentication
```

## Tests

### Tests fonctionnels
```php
// Exemple de test d'authentification
public function testLoginRedirectsToAdmin(): void
{
    $client = static::createClient();
    $userRepository = static::getContainer()->get('doctrine')->getRepository(User::class);
    $testUser = $userRepository->findOneByEmail('admin@suivi-plaidoyer.fr');
    
    $client->loginUser($testUser);
    $client->request('GET', '/admin');
    
    $this->assertResponseIsSuccessful();
}
```

### Tests unitaires
- Tests des entités User et Association
- Tests des contrôleurs de sécurité
- Tests des permissions et rôles

## Support

Pour toute question ou problème :
1. Consulter les logs Symfony
2. Vérifier la configuration de sécurité
3. Tester avec les comptes par défaut
4. Consulter la documentation Symfony Security
