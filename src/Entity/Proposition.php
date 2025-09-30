<?php

namespace App\Entity;

use App\Repository\PropositionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PropositionRepository::class)]
class Proposition
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT)]
    private string $title;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(nullable: true)]
    private ?int $bareme = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $image = null;

    #[ORM\Column(nullable: true)]
    private ?int $position = null;

    #[ORM\ManyToOne(inversedBy: 'propositions')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Category $category = null;

    /**
     * @var Collection<int, Commitment>
     */
    #[ORM\OneToMany(targetEntity: Commitment::class, mappedBy: 'proposition', orphanRemoval: true)]
    private Collection $commitments;

    /**
     * Attente commune pour toutes les mairies
     */
    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $commonExpectation = null;

    /**
     * @var Collection<int, SpecificExpectation>
     * Les attentes spécifiques par spécificité
     */
    #[ORM\OneToMany(targetEntity: SpecificExpectation::class, mappedBy: 'proposition', orphanRemoval: true, cascade: ['persist', 'remove'])]
    private Collection $specificExpectations;

    public function __construct()
    {
        $this->commitments = new ArrayCollection();
        $this->specificExpectations = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description = null): void
    {
        $this->description = $description;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): static
    {
        $this->image = $image;

        return $this;
    }

    public function getPosition(): ?int
    {
        return $this->position;
    }

    public function setPosition(?int $position): static
    {
        $this->position = $position;

        return $this;
    }

    public function getCommonExpectation(): ?string
    {
        return $this->commonExpectation;
    }

    public function setCommonExpectation(?string $commonExpectation): static
    {
        $this->commonExpectation = $commonExpectation;

        return $this;
    }

    /**
     * @return Collection<int, SpecificExpectation>
     */
    public function getSpecificExpectations(): Collection
    {
        return $this->specificExpectations;
    }

    public function addSpecificExpectation(SpecificExpectation $specificExpectation): static
    {
        if (!$this->specificExpectations->contains($specificExpectation)) {
            $this->specificExpectations->add($specificExpectation);
            $specificExpectation->setProposition($this);
        }

        return $this;
    }

    public function removeSpecificExpectation(SpecificExpectation $specificExpectation): static
    {
        if ($this->specificExpectations->removeElement($specificExpectation)) {
            // set the owning side to null (unless already changed)
            if ($specificExpectation->getProposition() === $this) {
                $specificExpectation->setProposition(null);
            }
        }

        return $this;
    }

    /**
     * Récupère l'attente pour une ville donnée
     * Retourne l'attente commune si elle existe, sinon cherche une attente spécifique
     */
    public function getExpectationFor(City $city): ?string
    {
        // Si une attente commune existe, la retourner
        if ($this->commonExpectation !== null) {
            return $this->commonExpectation;
        }

        // Sinon, chercher une attente spécifique correspondant aux spécificités de la ville
        foreach ($this->specificExpectations as $specificExpectation) {
            if ($city->getSpecificities()->contains($specificExpectation->getSpecificity())) {
                return $specificExpectation->getExpectation();
            }
        }

        return null;
    }

    /**
     * Vérifie si cette proposition a une attente (commune ou spécifique) pour une ville donnée
     */
    public function hasExpectationFor(City $city): bool
    {
        return $this->getExpectationFor($city) !== null;
    }

    public function __toString(): string
    {
        return $this->title;
    }
}
