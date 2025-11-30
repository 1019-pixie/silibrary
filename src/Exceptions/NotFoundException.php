<?php
namespace Library\Exceptions;

class NotFoundException extends LibraryException
{
    protected int $statusCode = 404;

    public function __construct(string $resource, string $id)
    {
        parent::__construct("{$resource} with ID '{$id}' not found", 404);
    }
}