<?php
namespace Library\Models;

use Library\Models\Traits\Validatable;
use Library\Models\Traits\Timestampable;
use Library\Exceptions\ValidationException;

class User
{
    use Validatable, Timestampable;

    private string $id;
    private string $name;
    private string $email;
    private string $membershipType;

    public function __construct(
        string $id,
        string $name,
        string $email,
        string $membershipType = 'regular'
    ) {
        $this->id = $id;
        $this->setName($name);
        $this->setEmail($email);
        $this->membershipType = $membershipType;
        $this->initializeTimestamps();
    }

    public function getId(): string { return $this->id; }
    public function getName(): string { return $this->name; }
    public function getEmail(): string { return $this->email; }
    public function getMembershipType(): string { return $this->membershipType; }

    public function setName(string $name): void
    {
        $this->clearErrors();
        if (!$this->validateRequired('name', $name)) {
            throw new ValidationException('Name is required');
        }
        $this->name = $name;
    }

    public function setEmail(string $email): void
    {
        $this->clearErrors();
        if (!$this->validateRequired('email', $email)) {
            throw new ValidationException('Email is required');
        }
        if (!$this->validateEmail('email', $email)) {
            throw new ValidationException('Invalid email format');
        }
        $this->email = $email;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'membership_type' => $this->membershipType,
            'created_at' => $this->createdAt->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt->format('Y-m-d H:i:s')
        ];
    }
}