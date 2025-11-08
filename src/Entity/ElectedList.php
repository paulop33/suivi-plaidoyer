<?php

namespace App\Entity;

use App\Repository\ElectedListRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ElectedListRepository::class)]
class ElectedList
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToOne(targetEntity: CandidateList::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?CandidateList $candidateList = null;

    #[ORM\ManyToOne(inversedBy: 'electedLists')]
    #[ORM\JoinColumn(nullable: false)]
    private ?City $city = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTime $electionDate = null;

    #[ORM\Column]
    private ?int $mandateStartYear = null;

    #[ORM\Column]
    private ?int $mandateEndYear = null;

    #[ORM\Column(length: 255)]
    private ?string $mayorName = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $contactEmail = null;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $contactPhone = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $programSummary = null;

    #[ORM\Column]
    private ?\DateTime $creationDate = null;

    #[ORM\Column]
    private ?\DateTime $updateDate = null;

    /**
     * @var Collection<int, ProgressUpdate>
     */
    #[ORM\OneToMany(targetEntity: ProgressUpdate::class, mappedBy: 'electedList', orphanRemoval: true)]
    private Collection $progressUpdates;

    #[ORM\Column]
    private ?bool $isActive = true;

    public function __construct()
    {
        $this->progressUpdates = new ArrayCollection();
        $this->creationDate = new \DateTime();
        $this->updateDate = new \DateTime();
        $this->isActive = true;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCandidateList(): ?CandidateList
    {
        return $this->candidateList;
    }

    public function setCandidateList(CandidateList $candidateList): static
    {
        $this->candidateList = $candidateList;

        return $this;
    }

    public function getCity(): ?City
    {
        return $this->city;
    }

    public function setCity(?City $city): static
    {
        $this->city = $city;

        return $this;
    }

    public function getElectionDate(): ?\DateTime
    {
        return $this->electionDate;
    }

    public function setElectionDate(\DateTime $electionDate): static
    {
        $this->electionDate = $electionDate;

        return $this;
    }

    public function getMandateStartYear(): ?int
    {
        return $this->mandateStartYear;
    }

    public function setMandateStartYear(int $mandateStartYear): static
    {
        $this->mandateStartYear = $mandateStartYear;

        return $this;
    }

    public function getMandateEndYear(): ?int
    {
        return $this->mandateEndYear;
    }

    public function setMandateEndYear(int $mandateEndYear): static
    {
        $this->mandateEndYear = $mandateEndYear;

        return $this;
    }

    public function getMayorName(): ?string
    {
        return $this->mayorName;
    }

    public function setMayorName(string $mayorName): static
    {
        $this->mayorName = $mayorName;

        return $this;
    }

    public function getContactEmail(): ?string
    {
        return $this->contactEmail;
    }

    public function setContactEmail(?string $contactEmail): static
    {
        $this->contactEmail = $contactEmail;

        return $this;
    }

    public function getContactPhone(): ?string
    {
        return $this->contactPhone;
    }

    public function setContactPhone(?string $contactPhone): static
    {
        $this->contactPhone = $contactPhone;

        return $this;
    }

    public function getProgramSummary(): ?string
    {
        return $this->programSummary;
    }

    public function setProgramSummary(?string $programSummary): static
    {
        $this->programSummary = $programSummary;

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

    public function getUpdateDate(): ?\DateTime
    {
        return $this->updateDate;
    }

    public function setUpdateDate(\DateTime $updateDate): static
    {
        $this->updateDate = $updateDate;

        return $this;
    }

    /**
     * @return Collection<int, ProgressUpdate>
     */
    public function getProgressUpdates(): Collection
    {
        return $this->progressUpdates;
    }

    public function addProgressUpdate(ProgressUpdate $progressUpdate): static
    {
        if (!$this->progressUpdates->contains($progressUpdate)) {
            $this->progressUpdates->add($progressUpdate);
            $progressUpdate->setElectedList($this);
        }

        return $this;
    }

    public function removeProgressUpdate(ProgressUpdate $progressUpdate): static
    {
        if ($this->progressUpdates->removeElement($progressUpdate)) {
            // set the owning side to null (unless already changed)
            if ($progressUpdate->getElectedList() === $this) {
                $progressUpdate->setElectedList(null);
            }
        }

        return $this;
    }

    public function getIsActive(): ?bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): static
    {
        $this->isActive = $isActive;

        return $this;
    }

    public function isCurrentMandate(): bool
    {
        $currentYear = (int) date('Y');
        return $currentYear >= $this->mandateStartYear && $currentYear <= $this->mandateEndYear;
    }

    public function getMandateDuration(): string
    {
        return $this->mandateStartYear . '-' . $this->mandateEndYear;
    }

    public function __toString(): string
    {
        return sprintf('%s (%s)', $this->mayorName, $this->city?->getName());
    }
}
