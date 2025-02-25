<?php

namespace App\Entity;

use App\Repository\ClubRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity(repositoryClass: ClubRepository::class)]
#[UniqueEntity(fields: ["nom"], message: "Ce nom de club est déjà utilisé.")]
class Club
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, unique: true)]
    #[Assert\Length(max: 50, min: 10)]
    private ?string $nom = null;

    #[ORM\Column(type: "string", length: 255, nullable: true)]
    #[Assert\File(mimeTypes: ["image/jpeg", "image/png", "image.jpg"])]
    private ?string $logo = null;

    #[ORM\Column(length: 255)]
    #[Assert\Choice(choices: ["CULTUREL", "SPORTIF", "EDUCATIF", "EVENEMENTIEL", "AUTRE"])]
    private ?string $type = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $date_creation = null;

    /**
     * @var Collection<int, Evenement>
     */
    #[ORM\OneToMany(targetEntity: Evenement::class, mappedBy: 'club')]
    private Collection $evenements;

    // Ajouter la relation ManyToOne pour User
    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'clubs')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    public function __construct()
    {
        $this->evenements = new ArrayCollection();
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

    public function getLogo(): ?string
    {
        return $this->logo;
    }

    public function setLogo(string $logo): static
    {
        $this->logo = $logo;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): static
    {
        // Validate if the type is valid
        if (!in_array($type, ["CULTUREL", "SPORTIF", "EDUCATIF", "EVENEMENTIEL", "AUTRE"])) {
            throw new \InvalidArgumentException("Invalid type.");
        }

        $this->type = $type;

        return $this;
    }

    public function getDateCreation(): ?\DateTimeInterface
    {
        return $this->date_creation;
    }

    public function setDateCreation(\DateTimeInterface $date_creation): static
    {
        $this->date_creation = $date_creation;

        return $this;
    }

    /**
     * @return Collection<int, Evenement>
     */
    public function getEvenements(): Collection
    {
        return $this->evenements;
    }

    public function addEvenement(Evenement $evenement): static
    {
        if (!$this->evenements->contains($evenement)) {
            $this->evenements->add($evenement);
            $evenement->setClub($this);
        }

        return $this;
    }

    public function removeEvenement(Evenement $evenement): static
    {
        if ($this->evenements->removeElement($evenement)) {
            // set the owning side to null (unless already changed)
            if ($evenement->getClub() === $this) {
                $evenement->setClub(null);
            }
        }

        return $this;
    }

    // Ajout des méthodes pour accéder et définir l'utilisateur
    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(User $user): static
    {
        $this->user = $user;

        return $this;
    }
}
