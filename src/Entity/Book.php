<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\BookRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: BookRepository::class)]
class Book
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 255)]
    private string $title;

    #[ORM\Column(type: 'string', length: 13, nullable: true)]
    private ?string $isbn = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $pageCount = null;

    #[ORM\Column(type: 'json')]
    private array $authors = [];

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $publishedDate = null;

    #[ORM\Column(type: 'string', length: 50)]
    private string $status = 'PUBLISH';

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $imagePath = null; // относительный путь после скачивания

    /**
     * @var Collection<int, Category>
     */
    #[ORM\ManyToMany(targetEntity: Category::class, inversedBy: 'books')]
    #[ORM\JoinTable(name: 'book_category')]
    private Collection $categories;

    public function __construct()
    {
        $this->categories = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;
        return $this;
    }

    public function getIsbn(): ?string
    {
        return $this->isbn;
    }

    public function setIsbn(?string $isbn): self
    {
        $this->isbn = $isbn;
        return $this;
    }

    public function getPageCount(): ?int
    {
        return $this->pageCount;
    }

    public function setPageCount(?int $pageCount): self
    {
        $this->pageCount = $pageCount;
        return $this;
    }

    public function getAuthors(): array
    {
        return $this->authors;
    }

    public function setAuthors(array $authors): self
    {
        $this->authors = $authors;
        return $this;
    }

    public function getPublishedDate(): ?\DateTimeImmutable
    {
        return $this->publishedDate;
    }

    public function setPublishedDate(?\DateTimeImmutable $publishedDate): self
    {
        $this->publishedDate = $publishedDate;
        return $this;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;
        return $this;
    }

    public function getImagePath(): ?string
    {
        return $this->imagePath;
    }

    public function setImagePath(?string $imagePath): self
    {
        $this->imagePath = $imagePath;
        return $this;
    }

    /**
     * @return Collection<int, Category>
     */
    public function getCategories(): Collection
    {
        return $this->categories;
    }

    public function addCategory(Category $category): self
    {
        if (!$this->categories->contains($category)) {
            $this->categories->add($category);
        }

        return $this;
    }

    public function removeCategory(Category $category): self
    {
        $this->categories->removeElement($category);
        return $this;
    }

    public function setCategories(Collection $categories): self
    {
        $this->categories = $categories;
        return $this;
    }
}
