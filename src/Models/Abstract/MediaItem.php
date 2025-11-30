<?php
namespace Library\Models\Abstract;

use Library\Models\Traits\Timestampable;

abstract class MediaItem
{
    use Timestampable;

    protected string $id;
    protected string $title;
    protected bool $available;
    protected string $location;

    public function __construct(string $id, string $title, string $location = 'Main Library')
    {
        $this->id = $id;
        $this->title = $title;
        $this->available = true;
        $this->location = $location;
        $this->initializeTimestamps();
    }

    public function getId(): string { return $this->id; }
    public function getTitle(): string { return $this->title; }
    public function isAvailable(): bool { return $this->available; }
    public function getLocation(): string { return $this->location; }

    public function setAvailable(bool $available): void
    {
        $this->available = $available;
        $this->updateTimestamp();
    }

    public function setLocation(string $location): void
    {
        $this->location = $location;
        $this->updateTimestamp();
    }

    abstract public function getMediaType(): string;
    abstract public function getDetails(): array;

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'type' => $this->getMediaType(),
            'available' => $this->available,
            'location' => $this->location,
            'created_at' => $this->createdAt->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt->format('Y-m-d H:i:s'),
            'details' => $this->getDetails()
        ];
    }
}