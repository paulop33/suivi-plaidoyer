<?php

namespace App\Entity;

use App\Repository\SpecificExpectationRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SpecificExpectationRepository::class)]
class SpecificExpectation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'specificExpectations')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Proposition $proposition = null;

    #[ORM\ManyToOne(inversedBy: 'specificExpectations')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Specificity $specificity = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $expectation = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getProposition(): ?Proposition
    {
        return $this->proposition;
    }

    public function setProposition(?Proposition $proposition): static
    {
        $this->proposition = $proposition;

        return $this;
    }

    public function getSpecificity(): ?Specificity
    {
        return $this->specificity;
    }

    public function setSpecificity(?Specificity $specificity): static
    {
        $this->specificity = $specificity;

        return $this;
    }

    public function getExpectation(): ?string
    {
        return $this->expectation;
    }

    public function setExpectation(string $expectation): static
    {
        $this->expectation = $expectation;

        return $this;
    }

    public function __toString(): string
    {
        return $this->specificity?->getName() . ': ' . substr($this->expectation ?? '', 0, 50);
    }
}

