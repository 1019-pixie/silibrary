<?php
// src/Http/Request.php - FIXED VERSION

namespace Library\Http;

class Request
{
    private string $method;
    private string $uri;
    private array $query;
    private array $body;
    private array $params;

    public function __construct()
    {
        $this->method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        
        // Get URI and remove query string
        $uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
        
        // Remove base path for Laragon/XAMPP
        // Example: /digital-library-api/public/api/books -> /api/books
        $basePaths = [
            '/digital-library-api/public',
            '/digital-library-api',
        ];
        
        foreach ($basePaths as $basePath) {
            if (str_starts_with($uri, $basePath)) {
                $uri = substr($uri, strlen($basePath));
                break;
            }
        }
        
        // Ensure starts with /
        $this->uri = $uri ?: '/';
        
        $this->query = $_GET;
        $this->params = [];
        $this->body = $this->parseBody();
    }

    private function parseBody(): array
    {
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';

        if (str_contains($contentType, 'application/json')) {
            $json = file_get_contents('php://input');
            return json_decode($json, true) ?? [];
        }

        return $_POST;
    }

    public function getMethod(): string 
    { 
        return $this->method; 
    }
    
    public function getUri(): string 
    { 
        return $this->uri; 
    }

    // FIXED: Tambahkan ? untuk nullable parameter
    public function getQuery(?string $key = null, mixed $default = null): mixed
    {
        if ($key === null) {
            return $this->query;
        }
        return $this->query[$key] ?? $default;
    }

    // FIXED: Tambahkan ? untuk nullable parameter
    public function getBody(?string $key = null, mixed $default = null): mixed
    {
        if ($key === null) {
            return $this->body;
        }
        return $this->body[$key] ?? $default;
    }

    public function setParams(array $params): void
    {
        $this->params = $params;
    }

    public function getParam(string $key, mixed $default = null): mixed
    {
        return $this->params[$key] ?? $default;
    }
    
    public function getParams(): array
    {
        return $this->params;
    }
}