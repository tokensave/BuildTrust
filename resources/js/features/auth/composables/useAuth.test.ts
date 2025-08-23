import { describe, it, expect, vi, beforeEach } from 'vitest';
import { useAuth } from './useAuth';

// Mock usePage from Inertia
const mockUsePage = vi.fn();
vi.mock('@inertiajs/vue3', () => ({
    usePage: mockUsePage,
    router: {
        visit: vi.fn(),
    },
}));

describe('useAuth', () => {
    beforeEach(() => {
        vi.clearAllMocks();
    });

    it('should return user data when authenticated', () => {
        const mockUser = {
            id: 1,
            username: 'testuser',
            email: 'test@example.com',
            avatar_url: 'https://example.com/avatar.jpg',
            company: null,
        };

        mockUsePage.mockReturnValue({
            props: {
                auth: { user: mockUser },
            },
        });

        const { user, isAuthenticated } = useAuth();

        expect(user.value).toEqual(mockUser);
        expect(isAuthenticated.value).toBe(true);
    });

    it('should return null user when not authenticated', () => {
        mockUsePage.mockReturnValue({
            props: {
                auth: { user: null },
            },
        });

        const { user, isAuthenticated } = useAuth();

        expect(user.value).toBeNull();
        expect(isAuthenticated.value).toBe(false);
    });

    it('should return correct avatar URL', () => {
        const mockUser = {
            id: 1,
            username: 'testuser',
            email: 'test@example.com',
            avatar_url: 'https://example.com/custom-avatar.jpg',
        };

        mockUsePage.mockReturnValue({
            props: {
                auth: { user: mockUser },
            },
        });

        const { avatarUrl } = useAuth();

        expect(avatarUrl.value).toBe('https://example.com/custom-avatar.jpg');
    });

    it('should return default avatar URL when no custom avatar', () => {
        const mockUser = {
            id: 1,
            username: 'testuser',
            email: 'test@example.com',
            avatar_url: '',
        };

        mockUsePage.mockReturnValue({
            props: {
                auth: { user: mockUser },
            },
        });

        const { avatarUrl } = useAuth();

        expect(avatarUrl.value).toBe('https://ui-avatars.com/api/?name=testuser&background=0369a1&color=ffffff');
    });

    it('should call logout function', () => {
        const mockVisit = vi.fn();
        vi.doMock('@inertiajs/vue3', () => ({
            usePage: mockUsePage,
            router: {
                visit: mockVisit,
            },
        }));

        mockUsePage.mockReturnValue({
            props: {
                auth: { user: { id: 1, username: 'test' } },
            },
        });

        const { logout } = useAuth();
        logout();

        expect(mockVisit).toHaveBeenCalledWith('/logout', {
            method: 'post',
        });
    });
});
