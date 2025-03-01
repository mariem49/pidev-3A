<?php

namespace App\Entity;

use App\Repository\BlogRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;


#[ORM\Entity(repositoryClass: BlogRepository::class)]
class Blog
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "The title cannot be blank.")]
    #[Assert\Length(
        max: 255,
        maxMessage: "The title cannot be longer than 255 characters."
    )]
    #[Assert\Length(
        min: 5,
        minMessage: "The title must be at least 5 characters long."
    )]
    private ?string $title = null;

    #[ORM\Column(type: "text")]
    #[Assert\NotBlank(message: "The description cannot be blank.")]
    #[Assert\Length(
        min: 10,
        minMessage: "The description must be at least 10 characters long."
    )]
    private ?string $description = null;

    #[ORM\Column(type: "datetime")]
    #[Assert\NotNull(message: "The creation date is required.")]
    private ?\DateTimeInterface $createdAtBlog = null;

    #[ORM\Column(type: "datetime", nullable: true)]
    private ?\DateTimeInterface $updatedAtBlog = null;

    #[ORM\OneToMany(mappedBy: "blog", targetEntity: Post::class, cascade: ["remove"])]
    private Collection $posts;


    public function __construct()
    {
        $this->posts = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;
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

    public function getCreatedAtBlog(): ?\DateTimeInterface
    {
        return $this->createdAtBlog;
    }

    public function setCreatedAtBlog(\DateTimeInterface $createdAtBlog): static
    {
        $this->createdAtBlog = $createdAtBlog;
        return $this;
    }

    public function getUpdatedAtBlog(): ?\DateTimeInterface
    {
        return $this->updatedAtBlog;
    }

    public function setUpdatedAtBlog(?\DateTimeInterface $updatedAtBlog): static
    {
        $this->updatedAtBlog = $updatedAtBlog;
        return $this;
    }
//////////post//////////
    public function getPosts()
    {
        return $this->posts;
    }

    public function addPost(Post $post): static
    {
        if (!$this->posts->contains($post)) {
            $this->posts[] = $post;
            $post->setBlog($this);
        }

        return $this;
    }

    public function removePost(Post $post): static
    {
        if ($this->posts->contains($post)) {
            $this->posts->removeElement($post);
            // set the owning side to null
            if ($post->getBlog() === $this) {
                $post->setBlog(null);
            }
        }

        return $this;
    }
    //////end post////

  //////////////user////////////


  #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'blogs')]
  #[ORM\JoinColumn(nullable: false)]
  private ?User $user = null;

  // Getters and Setters
  public function getUser(): ?User
  {
      return $this->user;
  }

  public function setUser(?User $user): static
  {
      $this->user = $user;
      return $this;
  }


  ////////////enduser////////





}