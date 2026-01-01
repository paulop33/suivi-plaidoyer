<?php

namespace App\Security;

use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Classe simple pour utiliser le système de hachage de Symfony
 * avec les mots de passe des listes candidates
 */
class CandidateListPasswordUser implements UserInterface, PasswordAuthenticatedUserInterface
{
    private string $password = '';

    public function __construct(string $password = '')
    {
        $this->password = $password;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;
        return $this;
    }

    public function getRoles(): array
    {
        return ['ROLE_CANDIDATE_LIST'];
    }

    public function eraseCredentials(): void
    {
        // Rien à effacer
    }

    public function getUserIdentifier(): string
    {
        return 'candidate_list_password_user';
    }
}
