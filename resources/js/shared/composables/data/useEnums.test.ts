import { describe, it, expect, vi, beforeEach } from 'vitest';
import { useEnums } from './useEnums';

// Mock axios for API calls
const mockAxios = {
    get: vi.fn(),
};

vi.mock('axios', () => ({
    default: mockAxios,
}));

describe('useEnums', () => {
    beforeEach(() => {
        vi.clearAllMocks();
    });

    it('should load enums from API', async () => {
        const mockEnumData = {
            adStatuses: {
                active: { label: 'Активное', color: 'green' },
                inactive: { label: 'Неактивное', color: 'gray' },
            },
            adTypes: {
                sell: { label: 'Продажа', description: 'Продажа товара' },
                buy: { label: 'Покупка', description: 'Покупка товара' },
            },
        };

        mockAxios.get.mockResolvedValue({ data: mockEnumData });

        const { loadEnums, enums, isLoading } = useEnums();

        expect(isLoading.value).toBe(false);

        const loadPromise = loadEnums();
        expect(isLoading.value).toBe(true);

        await loadPromise;

        expect(isLoading.value).toBe(false);
        expect(enums.value).toEqual(mockEnumData);
        expect(mockAxios.get).toHaveBeenCalledWith('/api/filters/all-enums');
    });

    it('should handle API errors gracefully', async () => {
        const consoleError = vi.spyOn(console, 'error').mockImplementation(() => {});
        mockAxios.get.mockRejectedValue(new Error('API Error'));

        const { loadEnums, error } = useEnums();
        await loadEnums();

        expect(error.value).toBe('Ошибка загрузки enum данных');
        expect(consoleError).toHaveBeenCalled();
        
        consoleError.mockRestore();
    });

    it('should get enum item by value', async () => {
        const mockEnumData = {
            adStatuses: {
                active: { label: 'Активное', color: 'green' },
                inactive: { label: 'Неактивное', color: 'gray' },
            },
        };

        mockAxios.get.mockResolvedValue({ data: mockEnumData });

        const { loadEnums, getEnumItemByValue } = useEnums();
        await loadEnums();

        const activeItem = getEnumItemByValue('adStatuses', 'active');
        expect(activeItem).toEqual({ label: 'Активное', color: 'green' });

        const unknownItem = getEnumItemByValue('adStatuses', 'unknown');
        expect(unknownItem).toBeNull();

        const unknownEnum = getEnumItemByValue('unknownEnum', 'active');
        expect(unknownEnum).toBeNull();
    });

    it('should get ad types correctly', async () => {
        const mockEnumData = {
            adTypes: {
                sell: { label: 'Продажа', description: 'Продажа товара' },
                buy: { label: 'Покупка', description: 'Покупка товара' },
            },
        };

        mockAxios.get.mockResolvedValue({ data: mockEnumData });

        const { loadEnums, getAdTypes } = useEnums();
        await loadEnums();

        const adTypes = getAdTypes.value;
        expect(adTypes).toEqual([
            { value: 'sell', label: 'Продажа', description: 'Продажа товара' },
            { value: 'buy', label: 'Покупка', description: 'Покупка товара' },
        ]);
    });

    it('should get categories by type', async () => {
        const mockEnumData = {
            categories: {
                sell: {
                    electronics: { label: 'Электроника' },
                    clothing: { label: 'Одежда' },
                },
                buy: {
                    books: { label: 'Книги' },
                },
            },
        };

        mockAxios.get.mockResolvedValue({ data: mockEnumData });

        const { loadEnums, getCategoriesByType } = useEnums();
        await loadEnums();

        const sellCategories = getCategoriesByType('sell');
        expect(sellCategories).toEqual({
            electronics: { label: 'Электроника' },
            clothing: { label: 'Одежда' },
        });

        const buyCategories = getCategoriesByType('buy');
        expect(buyCategories).toEqual({
            books: { label: 'Книги' },
        });

        const unknownCategories = getCategoriesByType('unknown');
        expect(unknownCategories).toEqual({});
    });

    it('should not reload enums if already loaded', async () => {
        const mockEnumData = { adStatuses: {} };
        mockAxios.get.mockResolvedValue({ data: mockEnumData });

        const { loadEnums } = useEnums();
        
        await loadEnums();
        expect(mockAxios.get).toHaveBeenCalledTimes(1);
        
        await loadEnums();
        expect(mockAxios.get).toHaveBeenCalledTimes(1); // Should not call again
    });
});
