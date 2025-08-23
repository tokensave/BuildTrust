import { describe, it, expect, vi, beforeEach } from 'vitest';
import { mount } from '@vue/test-utils';
import StatusBadge from './StatusBadge.vue';

// Mock useEnums composable
const mockUseEnums = vi.fn();
vi.mock('@/shared/composables/data/useEnums', () => ({
    useEnums: mockUseEnums,
}));

describe('StatusBadge', () => {
    beforeEach(() => {
        vi.clearAllMocks();
        
        // Default mock implementation
        mockUseEnums.mockReturnValue({
            getEnumItemByValue: vi.fn((enumType: string, value: string) => {
                if (enumType === 'adStatuses') {
                    const statusMap = {
                        'active': { label: 'Активное', color: 'green' },
                        'inactive': { label: 'Неактивное', color: 'gray' },
                        'pending': { label: 'На модерации', color: 'yellow' },
                    };
                    return statusMap[value as keyof typeof statusMap] || null;
                }
                return null;
            }),
        });
    });

    it('should render status badge with correct label and color', () => {
        const wrapper = mount(StatusBadge, {
            props: {
                status: 'active',
                enumType: 'adStatuses',
            },
        });

        expect(wrapper.text()).toBe('Активное');
        expect(wrapper.classes()).toContain('bg-green-100');
        expect(wrapper.classes()).toContain('text-green-800');
    });

    it('should render different status correctly', () => {
        const wrapper = mount(StatusBadge, {
            props: {
                status: 'pending',
                enumType: 'adStatuses',
            },
        });

        expect(wrapper.text()).toBe('На модерации');
        expect(wrapper.classes()).toContain('bg-yellow-100');
        expect(wrapper.classes()).toContain('text-yellow-800');
    });

    it('should render fallback when status not found', () => {
        mockUseEnums.mockReturnValue({
            getEnumItemByValue: vi.fn(() => null),
        });

        const wrapper = mount(StatusBadge, {
            props: {
                status: 'unknown',
                enumType: 'adStatuses',
            },
        });

        expect(wrapper.text()).toBe('unknown');
        expect(wrapper.classes()).toContain('bg-gray-100');
        expect(wrapper.classes()).toContain('text-gray-800');
    });

    it('should apply custom class when provided', () => {
        const wrapper = mount(StatusBadge, {
            props: {
                status: 'active',
                enumType: 'adStatuses',
                class: 'custom-class',
            },
        });

        expect(wrapper.classes()).toContain('custom-class');
    });

    it('should call getEnumItemByValue with correct parameters', () => {
        const mockGetEnumItem = vi.fn(() => ({ label: 'Test', color: 'blue' }));
        mockUseEnums.mockReturnValue({
            getEnumItemByValue: mockGetEnumItem,
        });

        mount(StatusBadge, {
            props: {
                status: 'test-status',
                enumType: 'testEnums',
            },
        });

        expect(mockGetEnumItem).toHaveBeenCalledWith('testEnums', 'test-status');
    });
