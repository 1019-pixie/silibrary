<?php
namespace Library\Models;

use Library\Models\Abstract\MediaItem;
use Library\Models\Interfaces\Borrowable;
use Library\Exceptions\ValidationException;

class Book extends MediaItem implements Borrowable
{
    private string $author;
    private string $isbn;
    private int $pages;
    private ?User $borrowedBy = null;

    public function __construct(
        string $id,
        string $title,
        string $author,
        string $isbn,
        int $pages,
        string $location = 'Main Library'
    ) {
        parent::__construct($id, $title, $location);
        $this->setAuthor($author);
        $this->setIsbn($isbn);
        $this->setPages($pages);
    }

    public function getAuthor(): string { return $this->author; }
    public function getIsbn(): string { return $this->isbn; }
    public function getPages(): int { return $this->pages; }

    public function setAuthor(string $author): void
    {
        if (empty($author)) {
            throw new ValidationException('Author cannot be empty');
        }
        $this->author = $author;
    }

    public function setIsbn(string $isbn): void
    {
        if (!preg_match('/^[\d-]{10,17}$/', $isbn)) {
            throw new ValidationException('Invalid ISBN format');
        }
        $this->isbn = $isbn;
    }

    public function setPages(int $pages): void
    {
        if ($pages <= 0) {
            throw new ValidationException('Pages must be greater than 0');
        }
        $this->pages = $pages;
    }

    public function getMediaType(): string
    {
        return 'book';
    }

    public function getDetails(): array
    {
        return [
            'author' => $this->author,
            'isbn' => $this->isbn,
            'pages' => $this->pages,
            'borrowed_by' => $this->borrowedBy?->getId()
        ];
    }

    public function canBorrow(): bool
    {
        return $this->available && $this->borrowedBy === null;
    }

    public function borrow(User $user): void
    {
        if (!$this->canBorrow()) {
            throw new ValidationException('Book is not available for borrowing');
        }
        $this->borrowedBy = $user;
        $this->setAvailable(false);
    }

    public function returnItem(): void
    {
        if ($this->borrowedBy === null) {
            throw new ValidationException('Book is not currently borrowed');
        }
        $this->borrowedBy = null;
        $this->setAvailable(true);
    }

    public function getBorrowedBy(): ?User
    {
        return $this->borrowedBy;
    }
}