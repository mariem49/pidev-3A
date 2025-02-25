<?php

// src/Entity/User.php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

#[ORM\Entity]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 255)]
    #[Assert\NotBlank]
    private ?string $name = null;

    #[ORM\Column(type: 'string', length: 255)]
    #[Assert\NotBlank]
    private ?string $lastname = null;

    #[ORM\Column(type: 'string', length: 255, unique: true)]
    #[Assert\NotBlank]
    #[Assert\Email]
    private ?string $email = null;

    #[ORM\Column(type: 'string')]
    private ?string $password = null;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $role = 'USER';

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $profilepic = null;

    #[ORM\Column(type: "string", length: 255, nullable: true)]
    private ?string $resetToken = null;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Post::class)]
    private Collection $posts;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Blog::class)]
    private Collection $blogs;

    public function __construct()
    {
        $this->posts = new ArrayCollection();
        $this->blogs = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function getLastname(): ?string
    {
        return $this->lastname;
    }

    public function setLastname(string $lastname): self
    {
        $this->lastname = $lastname;
        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;
        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;
        return $this;
    }

    public function getRole(): ?string
    {
        return $this->role;
    }

    public function setRole(string $role): self
    {
        $this->role = $role;
        return $this;
    }

    public function getProfilepic(): ?string
    {
        return $this->profilepic;
    }

    public function setProfilepic(?string $profilepic): self
    {
        $this->profilepic = $profilepic;
        return $this;
    }

    public function getResetToken(): ?string
    {
        return $this->resetToken;
    }

    public function setResetToken(?string $resetToken): self
    {
        $this->resetToken = $resetToken;
        return $this;
    }

    // UserInterface method to get roles
    public function getRoles(): array
    {
        return [$this->role]; // Ensure it returns an array
    }

    // Method to erase sensitive data (if any)
    public function eraseCredentials(): void
    {
        // You can add logic to clear sensitive data, but this class doesn’t store any additional credentials.
    }

    // Method to get the user's identifier (email is used here)
    public function getUserIdentifier(): string
    {
        return $this->email;
    }

    // Hash the password (plain password is hashed before storing)
    public function setPlainPassword(string $password): self
    {
        $this->password = password_hash($password, PASSWORD_BCRYPT); // Hashing password
        return $this;
    }

    ////////posts/////
    public function getPosts(): Collection
    {
        return $this->posts;
    }

    public function addPost(Post $post): self
    {
        if (!$this->posts->contains($post)) {
            $this->posts[] = $post;
            $post->setUser($this);  // Ensure the inverse side of the relationship is set
        }

        return $this;
    }

    public function removePost(Post $post): self
    {
        if ($this->posts->contains($post)) {
            $this->posts->removeElement($post);
            // Ensure the inverse side of the relationship is unset
            if ($post->getUser() === $this) {
                $post->setUser(null);
            }
        }

        return $this;
    }
    ////////end posts////////

    ////////blogs/////
    public function getBlogs(): Collection
    {
        return $this->blogs;
    }

    public function addBlog(Blog $blog): self
    {
        if (!$this->blogs->contains($blog)) {
            $this->blogs[] = $blog;
            $blog->setUser($this);
        }
        return $this;
    }

    public function removeBlog(Blog $blog): self
    {
        if ($this->blogs->contains($blog)) {
            $this->blogs->removeElement($blog);
            if ($blog->getUser() === $this) {
                $blog->setUser(null);
            }
        }
        return $this;
    }
    ////////end blogs/////
}