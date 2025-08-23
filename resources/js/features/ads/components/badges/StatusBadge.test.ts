import { describe, it, expect, vi, beforeEach } from 'vitest';
import { mount } from '@vue/test-utils';
import StatusBadge from '@/shared/components/feedback/StatusBadge.vue';

// Mock useEnums composable
const mockUseEnums = vi.fn();
vi.mock('@/shared/composables/data/useEnums', () => ({
    useEnums: mockUseEnums,
}));

describe('StatusBadge', () => {
    beforeEach(() => {
        vi.clearAllMocks();

        // Default mock implementation using existing methods
        mockUseEnums.mockReturnValue({
            getStatusLabel: vi.fn((status: string, type: string = 'ad') => {
                const statusMap = {
                    'active': 'Активное',
                    'inactive': 'Неактивное',
                    'pending': 'На модерации',
                };
                return statusMap[status as keyof typeof statusMap] || status;
            }),
            getStatusColor: vi.fn((status: string, type: string = 'ad') => {
                const colorMap = {
                    'active': 'green',
                    'inactive': 'gray',
                    'pending': 'yellow',
                };
                return colorMap[status as keyof typeof colorMap] || 'gray';
            }),
        });
    });

    it('should render status badge with correct label and color', () => {
        const wrapper = mount(StatusBadge, {
            props: {
                status: 'active',
                enumType: 'ad',
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
                enumType: 'ad',
            },
        });

        expect(wrapper.text()).toBe('На модерации');
        expect(wrapper.classes()).toContain('bg-yellow-100');
        expect(wrapper.classes()).toContain('text-yellow-800');
    });

    it('should render fallback when status not found', () => {
        mockUseEnums.mockReturnValue({
            getStatusLabel: vi.fn((status: string) => status),
            getStatusColor: vi.fn(() => 'gray'),
        });

        const wrapper = mount(StatusBadge, {
            props: {
                status: 'unknown',
                enumType: 'ad',
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
                enumType: 'ad',
                class: 'custom-class',
            },
        });

        expect(wrapper.classes()).toContain('custom-class');
    });

    it('should call getStatusLabel and getStatusColor with correct parameters', () => {
        const mockGetLabel = vi.fn(() => 'Test Status');
        const mockGetColor = vi.fn(() => 'blue');

        mockUseEnums.mockReturnValue({
            getStatusLabel: mockGetLabel,
            getStatusColor: mockGetColor,
        });

        mount(StatusBadge, {
            props: {
                status: 'test-status',
                enumType: 'ad',
            },
        });

        expect(mockGetLabel).toHaveBeenCalledWith('test-status', 'ad');
        expect(mockGetColor).toHaveBeenCalledWith('test-status', 'ad');
    });
});
