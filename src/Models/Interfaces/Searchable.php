<?php
namespace Library\Models\Interfaces;

interface Searchable
{
    public function search(array $criteria): array;
}