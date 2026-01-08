<?php

namespace App\Enums;

enum AuditStatus: string
{
    case PENDING = 'pending';
    case PROCESSING = 'processing';
    case COMPLETED = 'completed';
    case FAILED = 'failed';

    public function isFinal(): bool
    {
        return match($this) {
            self::COMPLETED, self::FAILED => true,
            self::PENDING, self::PROCESSING => false,
        };
    }

    public function canRetry(): bool
    {
        return $this === self::FAILED;
    }

    public function isCompleted(): bool
    {
        return $this === self::COMPLETED;
    }

    public function isInProgress(): bool
    {
        return match($this) {
            self::PENDING, self::PROCESSING => true,
            default => false,
        };
    }

    public function label(): string
    {
        return match($this) {
            self::PENDING => 'Ожидает',
            self::PROCESSING => 'Обрабатывается',
            self::COMPLETED => 'Завершен',
            self::FAILED => 'Ошибка',
        };
    }
}

