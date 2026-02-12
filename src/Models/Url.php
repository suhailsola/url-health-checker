<?php

namespace UrlHealthChecker\Models;

use DateTime;

class Url
{
    public function __construct(
        public readonly ?int $id,
        public readonly string $url,
        public readonly ?string $name,
        public readonly string $status,
        public readonly ?DateTime $lastCheckedAt,
        public readonly ?int $lastStatusCode,
        public readonly DateTime $createdAt,
        public readonly DateTime $updatedAt
    ) {
        $this->validate();
    }

    private function validate(): void
    {
        if (!filter_var($this->url, FILTER_VALIDATE_URL)) {
            throw new \InvalidArgumentException('Invalid URL format');
        }

        if (!in_array($this->status, ['pending', 'online', 'offline'])) {
            throw new \InvalidArgumentException('Invalid status. Must be: pending, online, or offline');
        }

        if ($this->name !== null && strlen($this->name) > 255) {
            throw new \InvalidArgumentException('Name must not exceed 255 characters');
        }
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'url' => $this->url,
            'name' => $this->name,
            'status' => $this->status,
            'last_checked_at' => $this->lastCheckedAt?->format('Y-m-d H:i:s'),
            'last_status_code' => $this->lastStatusCode,
            'created_at' => $this->createdAt->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt->format('Y-m-d H:i:s'),
        ];
    }
}
