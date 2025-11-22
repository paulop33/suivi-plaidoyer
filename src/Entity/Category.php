<?php

namespace App\Entity;

use App\Repository\CategoryRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CategoryRepository::class)]
class Category
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 255, unique: true)]
    private ?string $slug = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $image = null;

    #[ORM\Column(nullable: true)]
    private ?int $position = null;

    /**
     * @var Collection<int, Proposition>
     */
    #[ORM\OneToMany(targetEntity: Proposition::class, mappedBy: 'category', orphanRemoval: true)]
    private Collection $propositions;

    public function __construct()
    {
        $this->propositions = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;
        // Auto-generate slug when name is set
        $this->slug = $this->generateSlug($name);

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): static
    {
        $this->slug = $slug;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
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

    /**
     * @return Collection<int, Proposition>
     */
    public function getPropositions(): Collection
    {
        return $this->propositions;
    }

    public function addProposition(Proposition $proposition): static
    {
        if (!$this->propositions->contains($proposition)) {
            $this->propositions->add($proposition);
            $proposition->setCategory($this);

            // Auto-assign order if not set
            if ($proposition->getPosition() === null) {
                $proposition->setPosition($this->getNextPropositionOrder());
            }
        }

        return $this;
    }

    public function removeProposition(Proposition $proposition): static
    {
        if ($this->propositions->removeElement($proposition)) {
            // set the owning side to null (unless already changed)
            if ($proposition->getCategory() === $this) {
                $proposition->setCategory(null);
            }
        }

        return $this;
    }

    private function generateSlug(string $text): string
    {
        // Convert to lowercase
        $slug = strtolower($text);

        // Replace accented characters
        $slug = iconv('UTF-8', 'ASCII//TRANSLIT', $slug);

        // Remove special characters and replace spaces with hyphens
        $slug = preg_replace('/[^a-z0-9\s-]/', '', $slug);
        $slug = preg_replace('/[\s-]+/', '-', $slug);

        // Trim hyphens from start and end
        $slug = trim($slug, '-');

        return $slug;
    }

    /**
     * Obtient le prochain ordre disponible pour une nouvelle proposition
     */
    public function getNextPropositionOrder(): int
    {
        $maxOrder = 0;
        foreach ($this->propositions as $proposition) {
            if ($proposition->getPosition() !== null && $proposition->getPosition() > $maxOrder) {
                $maxOrder = $proposition->getPosition();
            }
        }
        return $maxOrder + 1;
    }

    public function __toString(): string
    {
        return $this->getName();
    }
}
