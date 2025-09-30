<?php

namespace App\Entity;

use App\Repository\AssociationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AssociationRepository::class)]
class Association
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 7)]
    private ?string $color = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $image = null;

    /**
     * @var Collection<int, City>
     */
    #[ORM\ManyToMany(targetEntity: City::class, mappedBy: 'referentesAssociations')]
    private Collection $cities;

    /**
     * @var Collection<int, User>
     */
    #[ORM\OneToMany(targetEntity: User::class, mappedBy: 'association')]
    private Collection $users;

    public function __construct()
    {
        $this->cities = new ArrayCollection();
        $this->users = new ArrayCollection();
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

    public function getColor(): ?string
    {
        return $this->color;
    }

    public function setColor(string $color): static
    {
        $this->color = $color;

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
            $city->addReferenteAssociation($this);
        }

        return $this;
    }

    public function removeCity(City $city): static
    {
        if ($this->cities->removeElement($city)) {
            $city->removeReferenteAssociation($this);
        }

        return $this;
    }

    /**
     * @return Collection<int, User>
     */
    public function getUsers(): Collection
    {
        return $this->users;
    }

    public function addUser(User $user): static
    {
        if (!$this->users->contains($user)) {
            $this->users->add($user);
            $user->setAssociation($this);
        }

        return $this;
    }

    public function removeUser(User $user): static
    {
        if ($this->users->removeElement($user)) {
            // set the owning side to null (unless already changed)
            if ($user->getAssociation() === $this) {
                $user->setAssociation(null);
            }
        }

        return $this;
    }

    public function __toString(): string
    {
        return $this->name ?? '';
    }
}
