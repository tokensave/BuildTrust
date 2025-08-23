<?php

declare(strict_types=1);

namespace App\Shared\ValueObjects;

use App\Shared\Exceptions\InvalidInnException;

/**
 * Value Object для работы с ИНН
 * Содержит валидацию и бизнес-логику
 */
final class Inn
{
    private readonly string $value;

    public function __construct(string $inn)
    {
        $normalizedInn = $this->normalize($inn);
        
        if (!$this->isValid($normalizedInn)) {
            throw new InvalidInnException("Неверный формат ИНН: {$inn}");
        }
        
        $this->value = $normalizedInn;
    }

    public static function fromString(string $inn): self
    {
        return new self($inn);
    }

    public function toString(): string
    {
        return $this->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }

    /**
     * Проверяет, является ли ИНН индивидуальным предпринимателем
     */
    public function isIndividual(): bool
    {
        return strlen($this->value) === 12;
    }

    /**
     * Проверяет, является ли ИНН юридическим лицом
     */
    public function isLegalEntity(): bool
    {
        return strlen($this->value) === 10;
    }

    /**
     * Возвращает регион по ИНН
     */
    public function getRegionCode(): string
    {
        return substr($this->value, 0, 2);
    }

    public function equals(Inn $other): bool
    {
        return $this->value === $other->value;
    }

    /**
     * Нормализует ИНН (убирает пробелы, дефисы)
     */
    private function normalize(string $inn): string
    {
        return preg_replace('/[^0-9]/', '', trim($inn));
    }

    /**
     * Валидирует ИНН с проверкой контрольных сумм
     */
    private function isValid(string $inn): bool
    {
        // Проверяем длину
        if (!in_array(strlen($inn), [10, 12], true)) {
            return false;
        }

        // Проверяем, что состоит только из цифр
        if (!preg_match('/^\d+$/', $inn)) {
            return false;
        }

        // Валидация контрольной суммы для юридических лиц (10 цифр)
        if (strlen($inn) === 10) {
            return $this->validateLegalEntityInn($inn);
        }

        // Валидация контрольной суммы для ИП (12 цифр)
        return $this->validateIndividualInn($inn);
    }

    /**
     * Валидация ИНН юридического лица (10 цифр)
     */
    private function validateLegalEntityInn(string $inn): bool
    {
        $weights = [2, 4, 10, 3, 5, 9, 4, 6, 8];
        $sum = 0;
        
        for ($i = 0; $i < 9; $i++) {
            $sum += (int) $inn[$i] * $weights[$i];
        }
        
        $checksum = ($sum % 11) % 10;
        
        return (int) $inn[9] === $checksum;
    }

    /**
     * Валидация ИНН индивидуального предпринимателя (12 цифр)
     */
    private function validateIndividualInn(string $inn): bool
    {
        // Первая контрольная сумма
        $weights1 = [7, 2, 4, 10, 3, 5, 9, 4, 6, 8];
        $sum1 = 0;
        
        for ($i = 0; $i < 10; $i++) {
            $sum1 += (int) $inn[$i] * $weights1[$i];
        }
        
        $checksum1 = ($sum1 % 11) % 10;
        
        if ((int) $inn[10] !== $checksum1) {
            return false;
        }

        // Вторая контрольная сумма
        $weights2 = [3, 7, 2, 4, 10, 3, 5, 9, 4, 6, 8];
        $sum2 = 0;
        
        for ($i = 0; $i < 11; $i++) {
            $sum2 += (int) $inn[$i] * $weights2[$i];
        }
        
        $checksum2 = ($sum2 % 11) % 10;
        
        return (int) $inn[11] === $checksum2;
    }
}
