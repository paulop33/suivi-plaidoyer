<?php

namespace App\Entity;

use App\Repository\CityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CityRepository::class)]
class City
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 255, unique: true)]
    private ?string $slug = null;

    /**
     * @var Collection<int, CandidateList>
     */
    #[ORM\OneToMany(targetEntity: CandidateList::class, mappedBy: 'city', orphanRemoval: true)]
    private Collection $contacts;

    /**
     * @var Collection<int, Association>
     */
    #[ORM\ManyToMany(targetEntity: Association::class, inversedBy: 'cities')]
    private Collection $referentesAssociations;

    /**
     * @var Collection<int, Specificity>
     */
    #[ORM\ManyToMany(targetEntity: Specificity::class, inversedBy: 'cities')]
    private Collection $specificities;

    /**
     * @var Collection<int, ElectedList>
     */
    #[ORM\OneToMany(targetEntity: ElectedList::class, mappedBy: 'city', orphanRemoval: true)]
    private Collection $electedLists;

    public function __construct()
    {
        $this->contacts = new ArrayCollection();
        $this->referentesAssociations = new ArrayCollection();
        $this->specificities = new ArrayCollection();
        $this->electedLists = new ArrayCollection();
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
     * @return Collection<int, CandidateList>
     */
    public function getContacts(): Collection
    {
        return $this->contacts;
    }

    public function addContact(CandidateList $contact): static
    {
        if (!$this->contacts->contains($contact)) {
            $this->contacts->add($contact);
            $contact->setCity($this);
        }

        return $this;
    }

    public function removeContact(CandidateList $contact): static
    {
        if ($this->contacts->removeElement($contact)) {
            // set the owning side to null (unless already changed)
            if ($contact->getCity() === $this) {
                $contact->setCity(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Association>
     */
    public function getReferentesAssociations(): Collection
    {
        return $this->referentesAssociations;
    }

    public function addReferenteAssociation(Association $referenteAssociation): static
    {
        if (!$this->referentesAssociations->contains($referenteAssociation)) {
            $this->referentesAssociations->add($referenteAssociation);
        }

        return $this;
    }

    public function removeReferenteAssociation(Association $referenteAssociation): static
    {
        $this->referentesAssociations->removeElement($referenteAssociation);

        return $this;
    }

    /**
     * @return Collection<int, Specificity>
     */
    public function getSpecificities(): Collection
    {
        return $this->specificities;
    }

    public function addSpecificity(Specificity $specificity): static
    {
        if (!$this->specificities->contains($specificity)) {
            $this->specificities->add($specificity);
        }

        return $this;
    }

    public function removeSpecificity(Specificity $specificity): static
    {
        $this->specificities->removeElement($specificity);

        return $this;
    }

    /**
     * @return Collection<int, ElectedList>
     */
    public function getElectedLists(): Collection
    {
        return $this->electedLists;
    }

    public function addElectedList(ElectedList $electedList): static
    {
        if (!$this->electedLists->contains($electedList)) {
            $this->electedLists->add($electedList);
            $electedList->setCity($this);
        }

        return $this;
    }

    public function removeElectedList(ElectedList $electedList): static
    {
        if ($this->electedLists->removeElement($electedList)) {
            // set the owning side to null (unless already changed)
            if ($electedList->getCity() === $this) {
                $electedList->setCity(null);
            }
        }

        return $this;
    }

    /**
     * Get the current elected list for this city
     */
    public function getCurrentElectedList(): ?ElectedList
    {
        $currentYear = (int) date('Y');

        foreach ($this->electedLists as $electedList) {
            if ($electedList->getIsActive() && $electedList->isCurrentMandate()) {
                return $electedList;
            }
        }

        return null;
    }

    /**
     * Check if this city has an elected list
     */
    public function hasElectedList(): bool
    {
        return $this->getCurrentElectedList() !== null;
    }

    public function __toString(): string
    {
        return $this->getName();
    }
}
