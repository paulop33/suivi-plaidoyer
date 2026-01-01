<?php

namespace App\Entity;

use App\Repository\CandidateListRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
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

    #[ORM\Column(length: 255, unique: true)]
    private ?string $slug = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $email = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $phone = null;

    #[ORM\ManyToOne(inversedBy: 'contacts')]
    #[ORM\JoinColumn(nullable: false)]
    private ?City $city = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $globalComment = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $password = null;

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
        $this->updateSlug();

        return $this;
    }

    public function getLastname(): ?string
    {
        return $this->lastname;
    }

    public function setLastname(string $lastname): static
    {
        $this->lastname = $lastname;
        $this->updateSlug();

        return $this;
    }

    public function getNameList(): string
    {
        return $this->nameList;
    }

    public function setNameList(string $nameList): void
    {
        $this->nameList = $nameList;
        $this->updateSlug();
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

    public function getGlobalComment(): ?string
    {
        return $this->globalComment;
    }

    public function setGlobalComment(?string $globalComment): static
    {
        $this->globalComment = $globalComment;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(?string $password): static
    {
        $this->password = $password;

        return $this;
    }

    public function hasPassword(): bool
    {
        return !empty($this->password);
    }

    public function __toString(): string
    {
        return sprintf('%s %s (%s)', $this->firstname, $this->lastname, $this->nameList);
    }

    /**
     * Updates the slug based on firstname and lastname
     */
    private function updateSlug(): void
    {
        if (isset($this->firstname) && isset($this->lastname) && isset($this->nameList)) {
            $this->slug = $this->generateSlug($this->firstname . ' ' . $this->lastname. ' '. $this->nameList);
        }
    }

    /**
     * Generates a slug from the given text
     */
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

    public function getPositiveCommitments(): Collection
    {
        return $this->commitments->filter(function ($commitment) {
            return $commitment->isAccepted();
        });
    }
}
