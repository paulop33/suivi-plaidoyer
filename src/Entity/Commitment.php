<?php

namespace App\Entity;

use App\Repository\CommitmentRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CommitmentRepository::class)]
class Commitment
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'commitments')]
    #[ORM\JoinColumn(nullable: false)]
    private ?City $city = null;

    #[ORM\ManyToOne(inversedBy: 'commitments')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Proposition $proposition = null;

    #[ORM\Column]
    private ?\DateTime $creationDate = null;

    #[ORM\Column]
    private ?\DateTime $updateDate = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $commentCity = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $commentAssociation = null;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getProposition(): ?Proposition
    {
        return $this->proposition;
    }

    public function setProposition(?Proposition $proposition): static
    {
        $this->proposition = $proposition;

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

    public function getCommentCity(): ?string
    {
        return $this->commentCity;
    }

    public function setCommentCity(?string $commentCity): static
    {
        $this->commentCity = $commentCity;

        return $this;
    }

    public function getCommentAssociation(): ?string
    {
        return $this->commentAssociation;
    }

    public function setCommentAssociation(?string $commentAssociation): static
    {
        $this->commentAssociation = $commentAssociation;

        return $this;
    }
}
