<?php

namespace App\Entity;

use App\Repository\PropositionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PropositionRepository::class)]
class Proposition
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private string $name;

    #[ORM\Column(nullable: true)]
    private ?int $bareme = null;

    #[ORM\ManyToOne(inversedBy: 'propositions')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Category $category = null;

    /**
     * @var Collection<int, Commitment>
     */
    #[ORM\OneToMany(targetEntity: Commitment::class, mappedBy: 'proposition', orphanRemoval: true)]
    private Collection $commitments;

    public function __construct()
    {
        $this->commitments = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getBareme(): ?int
    {
        return $this->bareme;
    }

    public function setBareme(?int $bareme): static
    {
        $this->bareme = $bareme;

        return $this;
    }

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setCategory(?Category $category): static
    {
        $this->category = $category;

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
            $commitment->setProposition($this);
        }

        return $this;
    }

    public function removeCommitment(Commitment $commitment): static
    {
        if ($this->commitments->removeElement($commitment)) {
            // set the owning side to null (unless already changed)
            if ($commitment->getProposition() === $this) {
                $commitment->setProposition(null);
            }
        }

        return $this;
    }
}
