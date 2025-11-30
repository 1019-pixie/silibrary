<?php
namespace Library\Services;

use Library\Repositories\BookRepository;
use Library\Repositories\UserRepository;
use Library\Exceptions\ValidationException;

class LibraryService
{
    private BookRepository $bookRepository;
    private UserRepository $userRepository;

    public function __construct(
        BookRepository $bookRepository,
        UserRepository $userRepository
    ) {
        $this->bookRepository = $bookRepository;
        $this->userRepository = $userRepository;
    }

    public function borrowBook(string $userId, string $bookId): array
    {
        $user = $this->userRepository->find($userId);
        $book = $this->bookRepository->find($bookId);

        if (!$book->canBorrow()) {
            throw new ValidationException('Book is not available for borrowing');
        }

        $book->borrow($user);
        $this->bookRepository->save($book);

        return [
            'success' => true,
            'message' => 'Book borrowed successfully',
            'data' => [
                'book' => $book->toArray(),
                'user' => $user->toArray()
            ]
        ];
    }

    public function returnBook(string $userId, string $bookId): array
    {
        $user = $this->userRepository->find($userId);
        $book = $this->bookRepository->find($bookId);

        if ($book->getBorrowedBy()?->getId() !== $userId) {
            throw new ValidationException('This book was not borrowed by this user');
        }

        $book->returnItem();
        $this->bookRepository->save($book);

        return [
            'success' => true,
            'message' => 'Book returned successfully',
            'data' => [
                'book' => $book->toArray(),
                'user' => $user->toArray()
            ]
        ];
    }

    public function searchBooks(array $criteria): array
    {
        $books = $this->bookRepository->search($criteria);

        return [
            'success' => true,
            'count' => count($books),
            'data' => array_map(fn($book) => $book->toArray(), $books)
        ];
    }

    public function getAvailableBooks(): array
    {
        $books = $this->bookRepository->findAvailable();

        return [
            'success' => true,
            'count' => count($books),
            'data' => array_map(fn($book) => $book->toArray(), $books)
        ];
    }

    public function getStatistics(): array
    {
        $allBooks = $this->bookRepository->findAll();
        $availableBooks = $this->bookRepository->findAvailable();
        $allUsers = $this->userRepository->findAll();

        $totalBorrowed = count($allBooks) - count($availableBooks);

        return [
            'success' => true,
            'data' => [
                'total_books' => count($allBooks),
                'available_books' => count($availableBooks),
                'borrowed_books' => $totalBorrowed,
                'total_users' => count($allUsers),
                'borrowing_rate' => count($allBooks) > 0 
                    ? round(($totalBorrowed / count($allBooks)) * 100, 2) 
                    : 0
            ]
        ];
    }
}