<?php

namespace App\Entity;

use App\Repository\CandidateListRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CandidateListRepository::class)]
class CandidateList
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private string $firstname;

    #[ORM\Column(length: 255)]
    private string $lastname;

    #[ORM\Column(length: 255)]
    private string $nameList;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $email = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $phone = null;

    #[ORM\ManyToOne(inversedBy: 'contacts')]
    #[ORM\JoinColumn(nullable: false)]
    private ?City $city = null;

    /**
     * @var Collection<int, Commitment>
     */
    #[ORM\OneToMany(targetEntity: Commitment::class, mappedBy: 'candidateList', orphanRemoval: true)]
    private Collection $commitments;

    public function __construct()
    {
        $this->commitments = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFirstname(): ?string
    {
        return $this->firstname;
    }

    public function setFirstname(string $firstname): static
    {
        $this->firstname = $firstname;

        return $this;
    }

    public function getLastname(): ?string
    {
        return $this->lastname;
    }

    public function setLastname(string $lastname): static
    {
        $this->lastname = $lastname;

        return $this;
    }

    public function getNameList(): string
    {
        return $this->nameList;
    }

    public function setNameList(string $nameList): void
    {
        $this->nameList = $nameList;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): static
    {
        $this->phone = $phone;

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

    /**
     * @return Collection<int, Commitment>
     */
    public function getCommitments(): Collection
    {
        return $this->commitments;
    }

    public function addCommitment(Commitment $commitment): static
    {
        if (!$this->commitments->contains($commitment)) {
            $this->commitments->add($commitment);
            $commitment->setCandidateList($this);
        }

        return $this;
    }

    public function removeCommitment(Commitment $commitment): static
    {
        if ($this->commitments->removeElement($commitment)) {
            // set the owning side to null (unless already changed)
            if ($commitment->getCandidateList() === $this) {
                $commitment->setCandidateList(null);
            }
        }

        return $this;
    }

    public function __toString(): string
    {
        return sprintf('%s %s (%s)', $this->firstname, $this->lastname, $this->nameList);
    }
}
