<?php

namespace App\Entity;

use App\Enum\ImplementationStatus;
use App\Repository\ProgressUpdateRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProgressUpdateRepository::class)]
class ProgressUpdate
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'progressUpdates')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Commitment $commitment = null;

    #[ORM\ManyToOne(inversedBy: 'progressUpdates')]
    #[ORM\JoinColumn(nullable: false)]
    private ?ElectedList $electedList = null;

    #[ORM\Column(type: Types::STRING, nullable: false, enumType: ImplementationStatus::class)]
    private ImplementationStatus $status;

    #[ORM\Column]
    private ?\DateTime $updateDate = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $description = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $evidence = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $evidenceLinks = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true)]
    private ?User $updatedBy = null;

    #[ORM\Column(nullable: true)]
    private ?int $progressPercentage = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTime $expectedCompletionDate = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $challenges = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $nextSteps = null;

    #[ORM\Column]
    private ?\DateTime $creationDate = null;

    #[ORM\Column(nullable: true)]
    private ?float $budgetAllocated = null;

    #[ORM\Column(nullable: true)]
    private ?float $budgetSpent = null;

    #[ORM\Column]
    private ?bool $isValidated = false;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true)]
    private ?User $validatedBy = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTime $validationDate = null;

    public function __construct()
    {
        $this->updateDate = new \DateTime();
        $this->creationDate = new \DateTime();
        $this->isValidated = false;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCommitment(): ?Commitment
    {
        return $this->commitment;
    }

    public function setCommitment(?Commitment $commitment): static
    {
        $this->commitment = $commitment;

        return $this;
    }

    public function getElectedList(): ?ElectedList
    {
        return $this->electedList;
    }

    public function setElectedList(?ElectedList $electedList): static
    {
        $this->electedList = $electedList;

        return $this;
    }

    public function getStatus(): ImplementationStatus
    {
        return $this->status;
    }

    public function setStatus(ImplementationStatus $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getUpdateDate(): ?\DateTime
    {
        return $this->updateDate;
    }

    public function setUpdateDate(\DateTime $updateDate): static
    {
        $this->updateDate = $updateDate;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getEvidence(): ?string
    {
        return $this->evidence;
    }

    public function setEvidence(?string $evidence): static
    {
        $this->evidence = $evidence;

        return $this;
    }

    public function getEvidenceLinks(): ?string
    {
        return $this->evidenceLinks;
    }

    public function setEvidenceLinks(?string $evidenceLinks): static
    {
        $this->evidenceLinks = $evidenceLinks;

        return $this;
    }

    public function getUpdatedBy(): ?User
    {
        return $this->updatedBy;
    }

    public function setUpdatedBy(?User $updatedBy): static
    {
        $this->updatedBy = $updatedBy;

        return $this;
    }

    public function getProgressPercentage(): ?int
    {
        return $this->progressPercentage;
    }

    public function setProgressPercentage(?int $progressPercentage): static
    {
        if ($progressPercentage !== null && ($progressPercentage < 0 || $progressPercentage > 100)) {
            throw new \InvalidArgumentException('Progress percentage must be between 0 and 100');
        }
        
        $this->progressPercentage = $progressPercentage;

        return $this;
    }

    public function getExpectedCompletionDate(): ?\DateTime
    {
        return $this->expectedCompletionDate;
    }

    public function setExpectedCompletionDate(?\DateTime $expectedCompletionDate): static
    {
        $this->expectedCompletionDate = $expectedCompletionDate;

        return $this;
    }

    public function getChallenges(): ?string
    {
        return $this->challenges;
    }

    public function setChallenges(?string $challenges): static
    {
        $this->challenges = $challenges;

        return $this;
    }

    public function getNextSteps(): ?string
    {
        return $this->nextSteps;
    }

    public function setNextSteps(?string $nextSteps): static
    {
        $this->nextSteps = $nextSteps;

        return $this;
    }

    public function getCreationDate(): ?\DateTime
    {
        return $this->creationDate;
    }

    public function setCreationDate(\DateTime $creationDate): static
    {
        $this->creationDate = $creationDate;

        return $this;
    }

    public function getBudgetAllocated(): ?float
    {
        return $this->budgetAllocated;
    }

    public function setBudgetAllocated(?float $budgetAllocated): static
    {
        $this->budgetAllocated = $budgetAllocated;

        return $this;
    }

    public function getBudgetSpent(): ?float
    {
        return $this->budgetSpent;
    }

    public function setBudgetSpent(?float $budgetSpent): static
    {
        $this->budgetSpent = $budgetSpent;

        return $this;
    }

    public function getIsValidated(): ?bool
    {
        return $this->isValidated;
    }

    public function setIsValidated(bool $isValidated): static
    {
        $this->isValidated = $isValidated;

        return $this;
    }

    public function getValidatedBy(): ?User
    {
        return $this->validatedBy;
    }

    public function setValidatedBy(?User $validatedBy): static
    {
        $this->validatedBy = $validatedBy;

        return $this;
    }

    public function getValidationDate(): ?\DateTime
    {
        return $this->validationDate;
    }

    public function setValidationDate(?\DateTime $validationDate): static
    {
        $this->validationDate = $validationDate;

        return $this;
    }

    public function getBudgetUsagePercentage(): ?float
    {
        if ($this->budgetAllocated === null || $this->budgetAllocated == 0) {
            return null;
        }

        return ($this->budgetSpent ?? 0) / $this->budgetAllocated * 100;
    }

    public function isOverBudget(): bool
    {
        if ($this->budgetAllocated === null || $this->budgetSpent === null) {
            return false;
        }

        return $this->budgetSpent > $this->budgetAllocated;
    }

    public function getEffectiveProgressPercentage(): int
    {
        return $this->progressPercentage ?? $this->status->getProgressPercentage();
    }

    public function __toString(): string
    {
        return sprintf(
            '%s - %s (%s)',
            $this->commitment?->getProposition()?->getTitle(),
            $this->status->getLabel(),
            $this->updateDate?->format('d/m/Y')
        );
    }
}
