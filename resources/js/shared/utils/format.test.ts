import { describe, it, expect } from 'vitest';

// Simple utility functions to test
export const formatPrice = (price: number): string => {
    return new Intl.NumberFormat('ru-RU', {
        style: 'currency',
        currency: 'RUB',
    }).format(price);
};

export const formatDate = (date: string): string => {
    return new Date(date).toLocaleDateString('ru-RU');
};

export const truncateText = (text: string, maxLength: number): string => {
    if (text.length <= maxLength) return text;
    return text.substring(0, maxLength) + '...';
};

describe('Utility Functions', () => {
    describe('formatPrice', () => {
        it('should format price correctly', () => {
            expect(formatPrice(1000)).toBe('1 000,00 ₽');
            expect(formatPrice(1234.56)).toBe('1 234,56 ₽');
            expect(formatPrice(0)).toBe('0,00 ₽');
        });
    });

    describe('formatDate', () => {
        it('should format date correctly', () => {
            expect(formatDate('2024-01-15')).toBe('15.01.2024');
            expect(formatDate('2023-12-31')).toBe('31.12.2023');
        });
    });

    describe('truncateText', () => {
        it('should truncate text when longer than maxLength', () => {
            expect(truncateText('Hello, world!', 5)).toBe('Hello...');
            expect(truncateText('Short', 10)).toBe('Short');
            expect(truncateText('Exactly10!', 10)).toBe('Exactly10!');
        });

        it('should handle edge cases', () => {
            expect(truncateText('', 5)).toBe('');
            expect(truncateText('Test', 0)).toBe('...');
        });
    });
});
