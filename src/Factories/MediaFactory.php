<?php
namespace Library\Factories;

use Library\Models\Book;
use Library\Models\Abstract\MediaItem;
use Library\Exceptions\ValidationException;

class MediaFactory
{
    public static function createMedia(string $type, array $data): MediaItem
    {
        return match (strtolower($type)) {
            'book' => self::createBook($data),
            default => throw new ValidationException("Unknown media type: {$type}")
        };
    }

    private static function createBook(array $data): Book
    {
        self::validateRequiredFields($data, ['id', 'title', 'author', 'isbn', 'pages']);

        return new Book(
            id: $data['id'],
            title: $data['title'],
            author: $data['author'],
            isbn: $data['isbn'],
            pages: (int)$data['pages'],
            location: $data['location'] ?? 'Main Library'
        );
    }

    private static function validateRequiredFields(array $data, array $requiredFields): void
    {
        $missing = [];
        
        foreach ($requiredFields as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                $missing[] = $field;
            }
        }

        if (!empty($missing)) {
            throw new ValidationException(
                'Missing required fields',
                ['missing_fields' => $missing]
            );
        }
    }
}