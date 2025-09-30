<?php

namespace App\Entity;

use App\Repository\SpecificityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SpecificityRepository::class)]
class Specificity
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

    /**
     * @var Collection<int, City>
     */
    #[ORM\ManyToMany(targetEntity: City::class, mappedBy: 'specificities')]
    private Collection $cities;

    /**
     * @var Collection<int, SpecificExpectation>
     */
    #[ORM\OneToMany(targetEntity: SpecificExpectation::class, mappedBy: 'specificity', orphanRemoval: true)]
    private Collection $specificExpectations;

    public function __construct()
    {
        $this->cities = new ArrayCollection();
        $this->specificExpectations = new ArrayCollection();
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

    /**
     * @return Collection<int, City>
     */
    public function getCities(): Collection
    {
        return $this->cities;
    }

    public function addCity(City $city): static
    {
        if (!$this->cities->contains($city)) {
            $this->cities->add($city);
            $city->addSpecificity($this);
        }

        return $this;
    }

    public function removeCity(City $city): static
    {
        if ($this->cities->removeElement($city)) {
            $city->removeSpecificity($this);
        }

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
            $specificExpectation->setSpecificity($this);
        }

        return $this;
    }

    public function removeSpecificExpectation(SpecificExpectation $specificExpectation): static
    {
        if ($this->specificExpectations->removeElement($specificExpectation)) {
            // set the owning side to null (unless already changed)
            if ($specificExpectation->getSpecificity() === $this) {
                $specificExpectation->setSpecificity(null);
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

    public function __toString(): string
    {
        return $this->name ?? '';
    }
}

