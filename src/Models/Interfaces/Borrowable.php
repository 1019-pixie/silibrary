<?php
namespace Library\Models\Interfaces;

use Library\Models\User;

interface Borrowable
{
    public function canBorrow(): bool;
    public function borrow(User $user): void;
    public function returnItem(): void;
    public function getBorrowedBy(): ?User;
}