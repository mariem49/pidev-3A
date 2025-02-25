<?php

namespace App\Entity;

use App\Repository\SeanceRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: SeanceRepository::class)]
class Seance
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    // Date field with dynamic today check
    #[ORM\Column(type: "datetime")]
    #[Assert\NotBlank(message: "La date de la séance est obligatoire.")]
    private ?\DateTimeInterface $date = null;

    // Professeur field
    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Le professeur est obligatoire.")]
    private ?string $professeur = null;

    // Cour field with validation for null
    #[ORM\ManyToOne(targetEntity: Cour::class)]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(message: "Le cours est obligatoire.")]
    private ?Cour $cour = null;

    // Getters and Setters

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): self
    {
        $this->date = $date;

        return $this;
    }

    public function getProfesseur(): ?string
    {
        return $this->professeur;
    }

    public function setProfesseur(string $professeur): self
    {
        $this->professeur = $professeur;

        return $this;
    }

    public function getCour(): ?Cour
    {
        return $this->cour;
    }

    public function setCour(?Cour $cour): self
    {
        $this->cour = $cour;

        return $this;
    }
}
