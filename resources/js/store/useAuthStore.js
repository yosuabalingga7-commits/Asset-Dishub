// resources/js/store/useAuthStore.js
// ============================================================================
// useAuthStore (GRASP Information Expert — Authentication Domain)
// ============================================================================
// Manages the authenticated user's session state in memory (Zustand).
//
// WHY ZUSTAND (not URL params) for auth:
//   Auth state is NOT URL-representable. A user's session is a global
//   cross-cutting concern, not a per-route filter. This is the correct
//   scope for Zustand as per our architecture rules.
//
// WHY NO localStorage:
//   Sanctum SPA cookie is HttpOnly + SameSite=Lax.
//   We never persist auth to localStorage or sessionStorage → XSS-resistant.
//   On hard refresh, initAuth() re-validates with the server via cookie.
//
// AUTH FLOW:
//   1. SpaApp.mount  → initAuth() → GET /api/auth/me
//      ├── 200 OK    → set { user, isAuthenticated: true }
//      └── 401       → set { user: null, isAuthenticated: false }
//   2. Login form    → login(nip, password) → POST /api/auth/login
//      ├── 200 OK    → set { user, isAuthenticated: true }
//      └── 401       → return error message (no state change)
//   3. Logout button → logout() → POST /api/auth/logout
//      └── always    → set { user: null, isAuthenticated: false }
// ============================================================================

import { create } from 'zustand';
import api from '../services/api';

const useAuthStore = create((set, get) => ({
    // ── State ──
    user:             null,    // { id, name, nip, email, role, foto }
    isAuthenticated:  false,
    isInitializing:   true,    // true while the first /me check is in-flight

    // ── Actions ──

    /**
     * initAuth — called once on SpaApp mount.
     * Validates the existing Sanctum session cookie with the server.
     * Sets isInitializing = false when done (gates the AppRouter render).
     */
    initAuth: async () => {
        set({ isInitializing: true });
        try {
            const data = await api.get('/auth/me');
            set({
                user: data.user,
                isAuthenticated: true,
                isInitializing: false,
            });
        } catch {
            // 401: No active session or cookie expired
            set({
                user: null,
                isAuthenticated: false,
                isInitializing: false,
            });
        }
    },

    /**
     * login — called by the LoginPage form submission.
     * The SPA must call GET /sanctum/csrf-cookie before this if the
     * XSRF-TOKEN cookie is not yet set. The Axios interceptor handles
     * injecting the X-CSRF-TOKEN header automatically.
     *
     * @returns {{ success: boolean, message?: string }}
     */
    login: async (nip, password) => {
        try {
            const data = await api.post('/auth/login', { nip, password });
            set({
                user: data.user,
                isAuthenticated: true,
            });
            return { success: true };
        } catch (err) {
            return {
                success: false,
                message: err.message || 'Login gagal. Periksa NIP dan password Anda.',
            };
        }
    },

    /**
     * logout — called by any Logout button in the SPA.
     * Destroys the server session + clears local Zustand state.
     */
    logout: async () => {
        try {
            await api.post('/auth/logout');
        } catch {
            // Ignore network errors on logout — clear state regardless
        } finally {
            set({ user: null, isAuthenticated: false });
        }
    },

    // ── Computed helpers (used by AppRouter for role-based guards) ──

    /** Returns true if the current user has one of the given roles */
    hasRole: (...roles) => roles.includes(get().user?.role),

    isAdmin:          () => get().user?.role === 'admin',
    isKadis:          () => get().user?.role === 'kadis',
    isSeksi:          () => get().user?.role === 'seksi',
    isPetugasLapangan:() => get().user?.role === 'petugas_lapangan',
}));

export default useAuthStore;
