import '@testing-library/jest-dom';
import { config } from '@vue/test-utils';

// Mock global functions
global.route = vi.fn((name: string, params?: any) => {
    return `/${name}${params ? '?' + new URLSearchParams(params).toString() : ''}`;
});

// Configure Vue Test Utils
config.global.mocks = {
    route: global.route,
};

// Mock Inertia.js
vi.mock('@inertiajs/vue3', () => ({
    usePage: vi.fn(() => ({
        props: {
            auth: { user: { id: 1, username: 'testuser', email: 'test@example.com' } },
            ziggy: { location: 'test' },
        },
    })),
    useForm: vi.fn(() => ({
        data: {},
        errors: {},
        processing: false,
        post: vi.fn(),
        put: vi.fn(),
        patch: vi.fn(),
        delete: vi.fn(),
        reset: vi.fn(),
        clearErrors: vi.fn(),
    })),
    router: {
        visit: vi.fn(),
        get: vi.fn(),
        post: vi.fn(),
        put: vi.fn(),
        patch: vi.fn(),
        delete: vi.fn(),
    },
}));
