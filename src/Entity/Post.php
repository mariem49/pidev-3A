<?php

namespace App\Entity;

use App\Repository\PostRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: PostRepository::class)]
class Post
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Content cannot be blank.")]
    #[Assert\Length(
        min: 4, minMessage: "Content must be at least 4 characters long.",
        max: 255, maxMessage: "Content cannot be longer than 255 characters."
    )]
    private ?string $content = null;

    #[ORM\Column(type: 'string', length: 255, nullable: false)]
    #[Assert\NotBlank(message: "An image is required.")]
    #[Assert\Url(message: "The image must be a valid URL.")]
    private ?string $image = null;
    

    #[ORM\Column(nullable: true)]
    #[Assert\Type(\DateTimeImmutable::class, message: "Invalid date format.")]
    private ?\DateTimeImmutable $updateAt = null;

    #[ORM\Column]
    #[Assert\NotNull(message: "The creation date cannot be null.")]
    #[Assert\Type(\DateTimeImmutable::class, message: "Invalid date format.")]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\ManyToOne(inversedBy: 'posts')]
    #[Assert\NotNull(message: "A post must be linked to a blog.")]
    private ?Blog $blog = null;

    // ✅ Ajout du constructeur pour initialiser createdAt
    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable(); // Valeur par défaut
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): static
    {
        $this->content = $content;
        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): self
    {
        $this->image = $image;
        return $this;
    }

    public function getUpdateAt(): ?\DateTimeImmutable
    {
        return $this->updateAt;
    }

    public function setUpdateAt(?\DateTimeImmutable $updateAt): static
    {
        $this->updateAt = $updateAt;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    // ✅ Ajout d'une valeur par défaut en cas de null
    public function setCreatedAt(?\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt ?? new \DateTimeImmutable();
        return $this;
    }

    public function getBlog(): ?Blog
    {
        return $this->blog;
    }

    public function setBlog(?Blog $blog): static
    {
        $this->blog = $blog;
        return $this;
    }
}
