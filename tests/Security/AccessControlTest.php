<?php

namespace App\Tests\Security;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class AccessControlTest extends WebTestCase
{
    public function testLoginPageIsAccessible(): void
    {
        $client = static::createClient();
        $client->request('GET', '/login');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h2', 'Connexion');
    }

    public function testAdminRequiresAuthentication(): void
    {
        $client = static::createClient();
        $client->request('GET', '/admin');

        // Should redirect to login
        $this->assertResponseRedirects('/login');
    }

    public function testSuperAdminCanAccessUserManagement(): void
    {
        $client = static::createClient();
        
        // Login as super admin
        $userRepository = static::getContainer()->get('doctrine')->getRepository(User::class);
        $superAdmin = $userRepository->findOneByEmail('admin@suivi-plaidoyer.fr');
        
        $client->loginUser($superAdmin);
        
        // Access admin dashboard
        $client->request('GET', '/admin');
        $this->assertResponseIsSuccessful();
        
        // Access user management (should be accessible to super admin)
        $crawler = $client->request('GET', '/admin?crudAction=index&crudControllerFqcn=App%5CController%5CAdmin%5CUserCrudController');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Utilisateurs');
    }

    public function testAssociationUserCannotAccessUserManagement(): void
    {
        $client = static::createClient();
        
        // Login as association user
        $userRepository = static::getContainer()->get('doctrine')->getRepository(User::class);
        $associationUser = $userRepository->findOneByEmail('association@example.com');
        
        $client->loginUser($associationUser);
        
        // Access admin dashboard (should work)
        $client->request('GET', '/admin');
        $this->assertResponseIsSuccessful();
        
        // Try to access user management (should be forbidden)
        $client->request('GET', '/admin?crudAction=index&crudControllerFqcn=App%5CController%5CAdmin%5CUserCrudController');
        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testProfilePageIsAccessible(): void
    {
        $client = static::createClient();
        
        // Login as any user
        $userRepository = static::getContainer()->get('doctrine')->getRepository(User::class);
        $user = $userRepository->findOneByEmail('admin@suivi-plaidoyer.fr');
        
        $client->loginUser($user);
        
        $client->request('GET', '/admin/profile');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h3', 'Informations personnelles');
    }

    public function testLogoutWorks(): void
    {
        $client = static::createClient();
        
        // Login first
        $userRepository = static::getContainer()->get('doctrine')->getRepository(User::class);
        $user = $userRepository->findOneByEmail('admin@suivi-plaidoyer.fr');
        $client->loginUser($user);
        
        // Verify we're logged in
        $client->request('GET', '/admin');
        $this->assertResponseIsSuccessful();
        
        // Logout
        $client->request('GET', '/logout');
        
        // Try to access admin again - should redirect to login
        $client->request('GET', '/admin');
        $this->assertResponseRedirects('/login');
    }
}
