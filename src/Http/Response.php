<?php
namespace Library\Http;

class Response
{
    private int $statusCode;
    private array $headers;
    private mixed $body;

    public function __construct(mixed $body = null, int $statusCode = 200, array $headers = [])
    {
        $this->body = $body;
        $this->statusCode = $statusCode;
        $this->headers = array_merge(['Content-Type' => 'application/json'], $headers);
    }

    public function send(): void
    {
        http_response_code($this->statusCode);

        foreach ($this->headers as $key => $value) {
            header("{$key}: {$value}");
        }

        if ($this->body !== null) {
            if (is_array($this->body) || is_object($this->body)) {
                echo json_encode($this->body, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
            } else {
                echo $this->body;
            }
        }
    }

    public static function json(mixed $data, int $statusCode = 200): self
    {
        return new self($data, $statusCode);
    }

    public static function error(string $message, int $statusCode = 500): self
    {
        return new self([
            'error' => true,
            'message' => $message,
            'code' => $statusCode
        ], $statusCode);
    }

    public static function success(mixed $data, ?string $message = null, int $statusCode = 200): self
    {
        $body = ['success' => true, 'data' => $data];
        if ($message !== null) $body['message'] = $message;
        return new self($body, $statusCode);
    }

    public static function created(mixed $data, string $message = 'Resource created'): self
    {
        return self::success($data, $message, 201);
    }
}