import { describe, it, expect, vi, beforeEach } from 'vitest';
import { useEnums } from './useEnums';

// Mock fetch for API calls
const mockFetch = vi.fn();

global.fetch = mockFetch;

describe('useEnums', () => {
    beforeEach(() => {
        vi.clearAllMocks();
    });

    it('should load enums from API', async () => {
        const mockEnumData = {
            adStatuses: [
                { value: 'active', label: 'Активное', color: 'green' },
                { value: 'inactive', label: 'Неактивное', color: 'gray' },
            ],
            adTypes: [
                { value: 'sell', label: 'Продажа', description: 'Продажа товара' },
                { value: 'buy', label: 'Покупка', description: 'Покупка товара' },
            ],
            dealStatuses: [],
            categories_structure: {}
        };

        mockFetch.mockResolvedValue({
            ok: true,
            json: () => Promise.resolve(mockEnumData)
        });

        const { loadEnums, enums, isLoading } = useEnums();

        expect(isLoading.value).toBe(false);

        const loadPromise = loadEnums();
        expect(isLoading.value).toBe(true);

        await loadPromise;

        expect(isLoading.value).toBe(false);
        expect(enums.value).toEqual(mockEnumData);
        expect(mockFetch).toHaveBeenCalledWith('/api/filters/all-enums');
    });

    // it('should handle API errors gracefully', async () => {
    //     const consoleError = vi.spyOn(console, 'error').mockImplementation(() => {});
    //     mockFetch.mockResolvedValue({
    //         ok: false,
    //         status: 500
    //     });
    //
    //     const { loadEnums, error } = useEnums();
    //     await loadEnums();
    //
    //     expect(error.value).toBe('Ошибка загрузки справочников');
    //     expect(consoleError).toHaveBeenCalled();
    //
    //     consoleError.mockRestore();
    // });

    it('should get status label and color correctly', async () => {
        const mockEnumData = {
            adStatuses: [
                { value: 'active', label: 'Активное', color: 'green' },
                { value: 'inactive', label: 'Неактивное', color: 'gray' },
            ],
            adTypes: [],
            dealStatuses: [],
            categories_structure: {}
        };

        mockFetch.mockResolvedValue({
            ok: true,
            json: () => Promise.resolve(mockEnumData)
        });

        const { loadEnums, getStatusLabel, getStatusColor } = useEnums();
        await loadEnums();

        const activeLabel = getStatusLabel('active', 'ad');
        expect(activeLabel).toBe('Активное');

        const activeColor = getStatusColor('active', 'ad');
        expect(activeColor).toBe('green');

        const unknownLabel = getStatusLabel('unknown', 'ad');
        expect(unknownLabel).toBe('unknown');

        const unknownColor = getStatusColor('unknown', 'ad');
        expect(unknownColor).toBe('gray');
    });

    it('should get ad types correctly', async () => {
        const mockEnumData = {
            adTypes: [
                { value: 'sell', label: 'Продажа', description: 'Продажа товара' },
                { value: 'buy', label: 'Покупка', description: 'Покупка товара' },
            ],
            adStatuses: [],
            dealStatuses: [],
            categories_structure: {}
        };

        mockFetch.mockResolvedValue({
            ok: true,
            json: () => Promise.resolve(mockEnumData)
        });

        const { loadEnums, getAdTypes } = useEnums();
        await loadEnums();

        const adTypes = getAdTypes.value;
        expect(adTypes).toEqual([
            { value: 'sell', label: 'Продажа', description: 'Продажа товара' },
            { value: 'buy', label: 'Покупка', description: 'Покупка товара' },
        ]);
    });

    // it('should get categories by type', async () => {
    //     const mockEnumData = {
    //         categories_structure: {
    //             goods: {
    //                 label: 'Товары',
    //                 subcategories: {
    //                     electronics: { label: 'Электроника' },
    //                     clothing: { label: 'Одежда' },
    //                 }
    //             },
    //             services: {
    //                 label: 'Услуги',
    //                 subcategories: {
    //                     books: { label: 'Книги' },
    //                 }
    //             },
    //         },
    //         adStatuses: [],
    //         adTypes: [],
    //         dealStatuses: []
    //     };
    //
    //     mockFetch.mockResolvedValue({
    //         ok: true,
    //         json: () => Promise.resolve(mockEnumData)
    //     });
    //
    //     const { loadEnums, getCategoriesByType } = useEnums();
    //     await loadEnums();
    //
    //     const goodsCategories = getCategoriesByType('goods');
    //     expect(goodsCategories).toEqual({
    //         goods: {
    //             label: 'Товары',
    //             subcategories: {
    //                 electronics: { label: 'Электроника' },
    //                 clothing: { label: 'Одежда' },
    //             }
    //         }
    //     });
    //
    //     const servicesCategories = getCategoriesByType('services');
    //     expect(servicesCategories).toEqual({
    //         services: {
    //             label: 'Услуги',
    //             subcategories: {
    //                 books: { label: 'Книги' },
    //             }
    //         }
    //     });
    // });

    // it('should not reload enums if already loaded', async () => {
    //     const mockEnumData = {
    //         adStatuses: [{ value: 'active', label: 'Active' }],
    //         adTypes: [],
    //         dealStatuses: [],
    //         categories_structure: {}
    //     };
    //
    //     mockFetch.mockResolvedValue({
    //         ok: true,
    //         json: () => Promise.resolve(mockEnumData)
    //     });
    //
    //     const { loadEnums } = useEnums();
    //
    //     await loadEnums();
    //     expect(mockFetch).toHaveBeenCalledTimes(1);
    //
    //     await loadEnums();
    //     expect(mockFetch).toHaveBeenCalledTimes(1); // Should not call again
    // });
});
