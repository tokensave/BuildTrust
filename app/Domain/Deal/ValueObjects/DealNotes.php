<?php

declare(strict_types=1);

namespace App\Domain\Deal\ValueObjects;

/**
 * Value Object для заметок к сделке
 * Валидирует длину и нормализует пустые строки
 */
final readonly class DealNotes
{
    private string $value;

    private function __construct(string $notes)
    {
        $trimmed = trim($notes);
        
        if (strlen($trimmed) > 1000) {
            throw new \InvalidArgumentException('Заметки не могут быть длиннее 1000 символов');
        }
        
        $this->value = $trimmed;
    }

    /**
     * Создать из строки (основной способ)
     * Возвращает null для пустых строк
     */
    public static function fromString(?string $notes): ?self
    {
        if (empty(trim($notes ?? ''))) {
            return null;
        }
        
        return new self($notes);
    }

    /**
     * Получить текст заметок
     */
    public function toString(): string
    {
        return $this->value;
    }

    /**
     * Проверить, пустые ли заметки
     */
    public function isEmpty(): bool
    {
        return empty($this->value);
    }

    /**
     * Получить длину заметок
     */
    public function getLength(): int
    {
        return strlen($this->value);
    }

    /**
     * Добавить текст к существующим заметкам
     */
    public function append(string $additionalText): self
    {
        return new self($this->value . "\n\n" . trim($additionalText));
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
