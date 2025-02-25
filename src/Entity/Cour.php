<?php

namespace App\Entity;

use App\Repository\CourRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: CourRepository::class)]
class Cour
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Le nom du cours est obligatoire.")]
    #[Assert\Length(
        min: 4,
        minMessage: "Le nom du cours doit contenir au moins {{ limit }} caractères."
    )]
    #[Assert\Regex(
        pattern: "/^[a-zA-Z0-9\s]+$/",
        message: "Le nom du cours ne peut contenir que des lettres, des chiffres et des espaces."
    )]
    private ?string $nom = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "La description du cours est obligatoire.")]
    #[Assert\Length(
        min: 10,
        minMessage: "La description du cours doit contenir au moins {{ limit }} caractères."
    )]
    private ?string $description = null;

    #[ORM\Column(type: "integer")]
    #[Assert\NotBlank(message: "La durée du cours est obligatoire.")]
    #[Assert\Positive(message: "La durée doit être un nombre positif.")]
    private ?int $duree = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'cours')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    /**
     * @var Collection<int, Seance>
     */
    #[ORM\OneToMany(targetEntity: Seance::class, mappedBy: 'cour', orphanRemoval: true)]
    private Collection $seances;

    public function __construct()
    {
        $this->seances = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): static
    {
        $this->nom = $nom;
        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;
        return $this;
    }

    public function getDuree(): ?int
    {
        return $this->duree;
    }

    public function setDuree(int $duree): static
    {
        $this->duree = $duree;
        return $this;
    }

    /**
     * @return Collection<int, Seance>
     */
    public function getSeances(): Collection
    {
        return $this->seances;
    }

    public function addSeance(Seance $seance): static
    {
        if (!$this->seances->contains($seance)) {
            $this->seances->add($seance);
            $seance->setCour($this);
        }
        return $this;
    }

    public function removeSeance(Seance $seance): static
    {
        if ($this->seances->removeElement($seance)) {
            if ($seance->getCour() === $this) {
                $seance->setCour(null);
            }
        }
        return $this;
    }
}
