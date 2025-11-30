<?php
namespace Library\Repositories;

use Library\Config\Database;
use Library\Models\Book;
use Library\Models\Interfaces\RepositoryInterface;
use Library\Models\Interfaces\Searchable;
use Library\Exceptions\NotFoundException;

class BookRepository implements RepositoryInterface, Searchable
{
    private Database $db;

    public function __construct(Database $db)
    {
        $this->db = $db;
    }

    public function find(string $id): Book
    {
        $sql = "SELECT * FROM books WHERE id = :id";
        $result = $this->db->query($sql, ['id' => $id]);

        if (empty($result)) {
            throw new NotFoundException('Book', $id);
        }

        return $this->hydrate($result[0]);
    }

    public function findAll(): array
    {
        $sql = "SELECT * FROM books ORDER BY title";
        $results = $this->db->query($sql);
        return array_map(fn($row) => $this->hydrate($row), $results);
    }

    public function save(mixed $book): bool
    {
        if (!$book instanceof Book) {
            throw new \InvalidArgumentException('Entity must be a Book instance');
        }

        try {
            $this->find($book->getId());
            return $this->update($book);
        } catch (NotFoundException $e) {
            return $this->insert($book);
        }
    }

    public function delete(string $id): bool
    {
        $sql = "DELETE FROM books WHERE id = :id";
        return $this->db->execute($sql, ['id' => $id]) > 0;
    }

    public function search(array $criteria): array
    {
        $conditions = [];
        $params = [];

        if (isset($criteria['title'])) {
            $conditions[] = "title LIKE :title";
            $params['title'] = "%{$criteria['title']}%";
        }

        if (isset($criteria['author'])) {
            $conditions[] = "author LIKE :author";
            $params['author'] = "%{$criteria['author']}%";
        }

        if (isset($criteria['available'])) {
            $conditions[] = "available = :available";
            $params['available'] = $criteria['available'] ? 1 : 0;
        }

        $where = !empty($conditions) ? 'WHERE ' . implode(' AND ', $conditions) : '';
        $sql = "SELECT * FROM books {$where} ORDER BY title";

        $results = $this->db->query($sql, $params);
        return array_map(fn($row) => $this->hydrate($row), $results);
    }

    public function findAvailable(): array
    {
        return $this->search(['available' => true]);
    }

    private function insert(Book $book): bool
    {
        $sql = "INSERT INTO books (id, title, author, isbn, pages, available, location, created_at, updated_at) 
                VALUES (:id, :title, :author, :isbn, :pages, :available, :location, :created_at, :updated_at)";

        $params = [
            'id' => $book->getId(),
            'title' => $book->getTitle(),
            'author' => $book->getAuthor(),
            'isbn' => $book->getIsbn(),
            'pages' => $book->getPages(),
            'available' => $book->isAvailable() ? 1 : 0,
            'location' => $book->getLocation(),
            'created_at' => $book->getCreatedAt()->format('Y-m-d H:i:s'),
            'updated_at' => $book->getUpdatedAt()->format('Y-m-d H:i:s')
        ];

        return $this->db->execute($sql, $params) > 0;
    }

    private function update(Book $book): bool
    {
        $sql = "UPDATE books 
                SET title = :title, author = :author, isbn = :isbn, pages = :pages, 
                    available = :available, location = :location, updated_at = :updated_at
                WHERE id = :id";

        $params = [
            'id' => $book->getId(),
            'title' => $book->getTitle(),
            'author' => $book->getAuthor(),
            'isbn' => $book->getIsbn(),
            'pages' => $book->getPages(),
            'available' => $book->isAvailable() ? 1 : 0,
            'location' => $book->getLocation(),
            'updated_at' => $book->getUpdatedAt()->format('Y-m-d H:i:s')
        ];

        return $this->db->execute($sql, $params) > 0;
    }

    private function hydrate(array $data): Book
    {
        $book = new Book(
            $data['id'],
            $data['title'],
            $data['author'],
            $data['isbn'],
            (int)$data['pages'],
            $data['location']
        );

        $book->setAvailable((bool)$data['available']);
        
        if (isset($data['created_at'])) {
            $book->setCreatedAt(new \DateTime($data['created_at']));
        }

        return $book;
    }
}