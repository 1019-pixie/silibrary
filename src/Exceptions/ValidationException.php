<?php
namespace Library\Exceptions;

class ValidationException extends LibraryException
{
    protected int $statusCode = 400;
    private array $errors = [];

    public function __construct(string $message, array $errors = [])
    {
        parent::__construct($message, 400);
        $this->errors = $errors;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function toArray(): array
    {
        $data = parent::toArray();
        if (!empty($this->errors)) {
            $data['errors'] = $this->errors;
        }
        return $data;
    }
}