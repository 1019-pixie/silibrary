<?php
namespace Library\Models\Interfaces;

interface RepositoryInterface
{
    public function find(string $id): mixed;
    public function findAll(): array;
    public function save(mixed $entity): bool;
    public function delete(string $id): bool;
}