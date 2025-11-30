<?php
namespace Library\Repositories;

use Library\Config\Database;
use Library\Models\User;
use Library\Models\Interfaces\RepositoryInterface;
use Library\Exceptions\NotFoundException;

class UserRepository implements RepositoryInterface
{
    private Database $db;

    public function __construct(Database $db)
    {
        $this->db = $db;
    }

    public function find(string $id): User
    {
        $sql = "SELECT * FROM users WHERE id = :id";
        $result = $this->db->query($sql, ['id' => $id]);

        if (empty($result)) {
            throw new NotFoundException('User', $id);
        }

        return $this->hydrate($result[0]);
    }

    public function findAll(): array
    {
        $sql = "SELECT * FROM users ORDER BY name";
        $results = $this->db->query($sql);
        return array_map(fn($row) => $this->hydrate($row), $results);
    }

    public function save(mixed $user): bool
    {
        if (!$user instanceof User) {
            throw new \InvalidArgumentException('Entity must be User');
        }

        try {
            $this->find($user->getId());
            return $this->update($user);
        } catch (NotFoundException $e) {
            return $this->insert($user);
        }
    }

    public function delete(string $id): bool
    {
        $sql = "DELETE FROM users WHERE id = :id";
        return $this->db->execute($sql, ['id' => $id]) > 0;
    }

    public function findByEmail(string $email): ?User
    {
        $sql = "SELECT * FROM users WHERE email = :email";
        $result = $this->db->query($sql, ['email' => $email]);
        return !empty($result) ? $this->hydrate($result[0]) : null;
    }

    private function insert(User $user): bool
    {
        $sql = "INSERT INTO users (id, name, email, membership_type, created_at, updated_at) 
                VALUES (:id, :name, :email, :membership_type, :created_at, :updated_at)";

        $params = [
            'id' => $user->getId(),
            'name' => $user->getName(),
            'email' => $user->getEmail(),
            'membership_type' => $user->getMembershipType(),
            'created_at' => $user->getCreatedAt()->format('Y-m-d H:i:s'),
            'updated_at' => $user->getUpdatedAt()->format('Y-m-d H:i:s')
        ];

        return $this->db->execute($sql, $params) > 0;
    }

    private function update(User $user): bool
    {
        $sql = "UPDATE users 
                SET name = :name, email = :email, membership_type = :membership_type, updated_at = :updated_at
                WHERE id = :id";

        $params = [
            'id' => $user->getId(),
            'name' => $user->getName(),
            'email' => $user->getEmail(),
            'membership_type' => $user->getMembershipType(),
            'updated_at' => $user->getUpdatedAt()->format('Y-m-d H:i:s')
        ];

        return $this->db->execute($sql, $params) > 0;
    }

    private function hydrate(array $data): User
    {
        $user = new User(
            $data['id'],
            $data['name'],
            $data['email'],
            $data['membership_type']
        );

        if (isset($data['created_at'])) {
            $user->setCreatedAt(new \DateTime($data['created_at']));
        }

        return $user;
    }
}