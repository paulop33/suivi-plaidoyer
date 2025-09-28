# Référence technique - Système d'authentification

## 🏗️ Architecture technique

### Stack technologique
- **Framework** : Symfony 7.3
- **Base de données** : PostgreSQL
- **ORM** : Doctrine
- **Interface admin** : EasyAdmin 4
- **Frontend** : Bootstrap 5 + Stimulus
- **Authentification** : Symfony Security Component

### Structure des fichiers

```
src/
├── Controller/
│   ├── Admin/
│   │   ├── DashboardController.php      # Dashboard EasyAdmin
│   │   └── UserManagementController.php # Gestion utilisateurs
│   ├── ProfileController.php            # Profil utilisateur
│   └── SecurityController.php           # Authentification
├── Entity/
│   ├── User.php                         # Entité utilisateur
│   └── Association.php                  # Entité association (modifiée)
├── Repository/
│   └── UserRepository.php               # Repository utilisateur
├── DataFixtures/
│   └── UserFixtures.php                 # Données de test
└── EventListener/
    └── OrderAssignmentListener.php      # Listener modifié

config/packages/
└── security.yaml                        # Configuration sécurité

templates/
├── admin/
│   └── profile.html.twig                # Template profil
└── security/
    └── login.html.twig                  # Template connexion

tests/
└── Security/
    └── AccessControlTest.php            # Tests d'accès
```

## 🔐 Entité User

### Propriétés
```php
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    private ?int $id;
    private string $email;                // Identifiant unique
    private array $roles = [];            // Rôles Symfony
    private string $password;             // Mot de passe haché
    private string $firstname;            // Prénom
    private string $lastname;             // Nom de famille
    private ?Association $association;    // Association liée (nullable)
    private bool $isActive = true;        // Statut actif/inactif
    private \DateTimeImmutable $createdAt; // Date de création
    private ?\DateTimeImmutable $updatedAt; // Date de modification
}
```

### Méthodes utiles
```php
// Vérification des rôles
public function isSuperAdmin(): bool
public function isAssociation(): bool

// Informations utilisateur
public function getFullName(): string
public function getUserIdentifier(): string

// Gestion des rôles
public function getRoles(): array
public function setRoles(array $roles): self
```

### Contraintes de validation
```php
#[Assert\NotBlank]
#[Assert\Email]
private string $email;

#[Assert\NotBlank]
#[Assert\Length(min: 2, max: 50)]
private string $firstname;

#[Assert\NotBlank]
#[Assert\Length(min: 2, max: 50)]
private string $lastname;
```

## 🛡️ Configuration de sécurité

### Providers
```yaml
providers:
    app_user_provider:
        entity:
            class: App\Entity\User
            property: email  # Authentification par email
```

### Firewalls
```yaml
firewalls:
    main:
        lazy: true
        provider: app_user_provider
        form_login:
            login_path: app_login
            check_path: app_login
            default_target_path: admin
            always_use_default_target_path: true
        logout:
            path: app_logout
            target: app_login
        remember_me:
            secret: '%kernel.secret%'
            lifetime: 604800  # 1 semaine
        switch_user: true     # Impersonation pour super admins
```

### Contrôles d'accès
```yaml
access_control:
    - { path: ^/login, roles: PUBLIC_ACCESS }
    - { path: ^/admin, roles: ROLE_USER }
    - { path: ^/admin/user, roles: ROLE_SUPER_ADMIN }
```

### Hiérarchie des rôles
```yaml
role_hierarchy:
    ROLE_ASSOCIATION: ROLE_USER
    ROLE_SUPER_ADMIN: [ROLE_ASSOCIATION, ROLE_ALLOWED_TO_SWITCH]
```

## 🎛️ Contrôleurs

### SecurityController
```php
#[Route('/login', name: 'app_login')]
public function login(AuthenticationUtils $authenticationUtils): Response

#[Route('/logout', name: 'app_logout')]
public function logout(): void
```

### UserManagementController
```php
// Gestion CRUD personnalisée des utilisateurs
#[Route('/admin/users', name: 'admin_users_list')]
public function list(): Response

#[Route('/admin/users/new', name: 'admin_users_new')]
public function new(Request $request): Response

#[Route('/admin/users/{id}/edit', name: 'admin_users_edit')]
public function edit(User $user, Request $request): Response

// Restriction d'accès
#[IsGranted('ROLE_SUPER_ADMIN')]
```

### DashboardController
```php
// Menu adaptatif selon les rôles
public function configureMenuItems(): iterable

// Contrôle d'accès
public function configureDashboard(): Dashboard
```

## 🗃️ Base de données

### Migration User
```sql
CREATE TABLE user (
    id SERIAL PRIMARY KEY,
    email VARCHAR(180) NOT NULL UNIQUE,
    roles JSON NOT NULL,
    password VARCHAR(255) NOT NULL,
    firstname VARCHAR(50) NOT NULL,
    lastname VARCHAR(50) NOT NULL,
    association_id INT DEFAULT NULL,
    is_active BOOLEAN NOT NULL DEFAULT true,
    created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
    updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL,
    CONSTRAINT FK_user_association FOREIGN KEY (association_id) REFERENCES association (id)
);

CREATE INDEX IDX_user_association ON user (association_id);
CREATE UNIQUE INDEX UNIQ_user_email ON user (email);
```

### Relation avec Association
```php
// Dans User.php
#[ORM\ManyToOne(targetEntity: Association::class, inversedBy: 'users')]
#[ORM\JoinColumn(nullable: true)]
private ?Association $association = null;

// Dans Association.php
#[ORM\OneToMany(mappedBy: 'association', targetEntity: User::class)]
private Collection $users;
```

## 🧪 Tests

### Tests d'accès
```php
class AccessControlTest extends WebTestCase
{
    public function testAdminRequiresAuthentication(): void
    {
        $client = static::createClient();
        $client->request('GET', '/admin');
        $this->assertResponseRedirects('/login');
    }

    public function testUserManagementRequiresSuperAdmin(): void
    {
        $client = static::createClient();
        // Test avec utilisateur association
        $this->assertResponseStatusCodeSame(403);
    }
}
```

### Fixtures de test
```php
class UserFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // Super Admin
        $admin = new User();
        $admin->setEmail('admin@suivi-plaidoyer.fr')
              ->setRoles([User::ROLE_SUPER_ADMIN])
              ->setPassword($this->passwordHasher->hashPassword($admin, 'admin123'));
        
        // Utilisateur Association
        $association = new User();
        $association->setEmail('association@example.com')
                   ->setRoles([User::ROLE_ASSOCIATION])
                   ->setAssociation($this->getReference('association-1'));
    }
}
```

## 🔧 Services et utilitaires

### UserRepository
```php
// Recherche par rôle
public function findByRole(string $role): array

// Utilisateurs actifs
public function findActiveUsers(): array

// Mise à jour du mot de passe
public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
```

### Hachage des mots de passe
```php
// Dans UserManagementController
private function createUserForm(User $user, bool $isNew): FormInterface
{
    // Création du formulaire avec validation et hachage automatique
    // Gestion des rôles et associations
}
```

## 🎨 Templates

### Login template
```twig
{# templates/security/login.html.twig #}
<form method="post">
    <input type="email" name="email" value="{{ last_username }}" required>
    <input type="password" name="password" required>
    <input type="checkbox" name="_remember_me">
    <input type="hidden" name="_csrf_token" value="{{ csrf_token('authenticate') }}">
    <button type="submit">Se connecter</button>
</form>
```

### Profile template
```twig
{# templates/admin/profile.html.twig #}
<div class="user-info">
    <h1>{{ user.fullName }}</h1>
    <p>{{ user.email }}</p>
    {% for role in user.roles %}
        <span class="badge">{{ role }}</span>
    {% endfor %}
</div>
```

## 🔄 Événements et listeners

### OrderAssignmentListener (modifié)
```php
// Correction pour éviter les erreurs avec les nouvelles entités
public function prePersist(PrePersistEventArgs $args): void
{
    $entity = $args->getObject();
    
    if ($entity instanceof Engagement) {
        // Vérifier que les entités ont des IDs avant assignation
        if ($entity->getId() === null) {
            return; // Skip pour les nouvelles entités
        }
        // ... logique d'assignation
    }
}
```

## 📊 Monitoring et logs

### Logs de sécurité
```yaml
# config/packages/monolog.yaml
monolog:
    channels: ['security']
    handlers:
        security:
            type: stream
            path: '%kernel.logs_dir%/security.log'
            channels: ['security']
```

### Métriques utiles
- Tentatives de connexion
- Échecs d'authentification
- Accès refusés
- Créations/modifications d'utilisateurs

## 🚀 Déploiement

### Variables d'environnement
```env
# .env.prod
APP_ENV=prod
APP_SECRET=your-production-secret-key
DATABASE_URL=postgresql://user:pass@localhost:5432/prod_db
```

### Commandes de déploiement
```bash
# Migration de la base
php bin/console doctrine:migrations:migrate --no-interaction

# Cache
php bin/console cache:clear --env=prod
php bin/console cache:warmup --env=prod

# Assets
php bin/console asset-map:compile
```

## 🔮 Extensions futures

### API REST
```php
// Authentification JWT pour API
#[Route('/api/login', methods: ['POST'])]
public function apiLogin(): JsonResponse

// Endpoints protégés
#[Route('/api/users', methods: ['GET'])]
#[IsGranted('ROLE_SUPER_ADMIN')]
public function getUsers(): JsonResponse
```

### Audit Trail
```php
// Tracking des actions utilisateurs
class UserActionListener
{
    public function onUserAction(UserActionEvent $event): void
    {
        // Log des actions importantes
    }
}
```

### Notifications
```php
// Service de notification
class NotificationService
{
    public function notifyUserCreated(User $user): void
    public function notifyRoleChanged(User $user, array $oldRoles): void
}
```

## 🛠️ Outils de développement

### Commandes utiles
```bash
# Créer un utilisateur en CLI
php bin/console app:create-user email@example.com password ROLE_SUPER_ADMIN

# Lister les utilisateurs
php bin/console app:list-users

# Réinitialiser un mot de passe
php bin/console app:reset-password email@example.com
```

### Debug
```bash
# Vérifier la configuration de sécurité
php bin/console debug:security

# Tester les rôles
php bin/console security:check-user email@example.com

# Profiler les requêtes
php bin/console debug:doctrine
```
