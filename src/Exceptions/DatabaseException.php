<?php
namespace Library\Exceptions;

class DatabaseException extends LibraryException
{
    protected int $statusCode = 500;

    public function __construct(string $message, int $code = 500)
    {
        parent::__construct("Database Error: {$message}", $code);
    }
}