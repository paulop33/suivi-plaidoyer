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

    /**
     * @var Collection<int, CandidateList>
     */
    #[ORM\OneToMany(targetEntity: CandidateList::class, mappedBy: 'city', orphanRemoval: true)]
    private Collection $contacts;

    /**
     * @var Collection<int, Commitment>
     */
    #[ORM\OneToMany(targetEntity: Commitment::class, mappedBy: 'city', orphanRemoval: true)]
    private Collection $commitments;

    /**
     * @var Collection<int, Association>
     */
    #[ORM\ManyToMany(targetEntity: Association::class, inversedBy: 'cities')]
    private Collection $referentesAssociations;

    public function __construct()
    {
        $this->contacts = new ArrayCollection();
        $this->commitments = new ArrayCollection();
        $this->referentesAssociations = new ArrayCollection();
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

        return $this;
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

    public function __toString(): string
    {
        return $this->getName();
    }
}
