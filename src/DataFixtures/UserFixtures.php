<?php

namespace App\DataFixtures;

use App\Entity\User;
use App\Entity\Association;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture implements DependentFixtureInterface
{
    public function __construct(
        private UserPasswordHasherInterface $passwordHasher
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        // Créer un super administrateur par défaut
        $superAdmin = new User();
        $superAdmin->setEmail('admin@suivi-plaidoyer.fr');
        $superAdmin->setFirstname('Super');
        $superAdmin->setLastname('Admin');
        $superAdmin->setRoles([User::ROLE_SUPER_ADMIN]);
        $superAdmin->setIsActive(true);

        // Hash du mot de passe "admin123"
        $hashedPassword = $this->passwordHasher->hashPassword($superAdmin, 'admin123');
        $superAdmin->setPassword($hashedPassword);

        $manager->persist($superAdmin);

        // Créer un utilisateur association exemple
        $associationUser = new User();
        $associationUser->setEmail('association@example.com');
        $associationUser->setFirstname('Utilisateur');
        $associationUser->setLastname('Association');
        $associationUser->setRoles([User::ROLE_ASSOCIATION]);
        $associationUser->setIsActive(true);

        // Associer à la première association si elle existe
        $associations = $manager->getRepository(Association::class)->findAll();
        if (!empty($associations)) {
            $associationUser->setAssociation($associations[0]);
        }

        // Hash du mot de passe "password123"
        $hashedPassword = $this->passwordHasher->hashPassword($associationUser, 'password123');
        $associationUser->setPassword($hashedPassword);

        $manager->persist($associationUser);

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            AppFixtures::class,
        ];
    }
}
