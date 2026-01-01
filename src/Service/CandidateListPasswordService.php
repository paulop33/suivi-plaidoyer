<?php

namespace App\Service;

use App\Security\CandidateListPasswordUser;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * Service pour gérer le hachage et la vérification des mots de passe
 * des listes candidates
 */
class CandidateListPasswordService
{
    public function __construct(
        private readonly UserPasswordHasherInterface $passwordHasher
    ) {
    }

    /**
     * Hache un mot de passe en clair
     */
    public function hashPassword(string $plainPassword): string
    {
        $user = new CandidateListPasswordUser();
        return $this->passwordHasher->hashPassword($user, $plainPassword);
    }

    /**
     * Vérifie si un mot de passe en clair correspond au hash stocké
     */
    public function verifyPassword(string $plainPassword, string $hashedPassword): bool
    {
        $user = new CandidateListPasswordUser($hashedPassword);
        return $this->passwordHasher->isPasswordValid($user, $plainPassword);
    }
}
