<?php
namespace Library\Models\Traits;

trait Validatable
{
    protected array $errors = [];

    protected function addError(string $field, string $message): void
    {
        if (!isset($this->errors[$field])) {
            $this->errors[$field] = [];
        }
        $this->errors[$field][] = $message;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function hasErrors(): bool
    {
        return !empty($this->errors);
    }

    public function clearErrors(): void
    {
        $this->errors = [];
    }

    protected function validateRequired(string $field, mixed $value): bool
    {
        if (empty($value)) {
            $this->addError($field, "Field {$field} is required");
            return false;
        }
        return true;
    }

    protected function validateEmail(string $field, string $value): bool
    {
        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $this->addError($field, "Field {$field} must be a valid email");
            return false;
        }
        return true;
    }
}