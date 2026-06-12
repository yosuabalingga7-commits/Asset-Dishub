// resources/js/spa-app.jsx
// ============================================================================
// LINTAS SPA — Root Entry Point
// ============================================================================
// Bootstraps the entire React application into #spa-root.
// Responsibilities:
//   1. Hydrate the auth session on first load via GET /api/auth/me
//   2. Provide the Router context (BrowserRouter)
//   3. Render the top-level route tree (AppRouter)
//
// Auth Architecture:
//   - Sanctum SPA cookie (same-domain, HttpOnly)
//   - No token in localStorage (XSS-resistant)
//   - useAuthStore (Zustand) holds user state in memory only
//
// Design System: See resources/css/app.css
//   - Font: Roboto | No rounded corners | No shadows | Data-dense
// ============================================================================

import React, { useEffect } from 'react';
import { createRoot } from 'react-dom/client';
import { BrowserRouter } from 'react-router-dom';
import AppRouter from './router/AppRouter';
import useAuthStore from './store/useAuthStore';

/**
 * SpaApp — Root Orchestrator
 *
 * On mount, calls GET /api/auth/me to determine if a Sanctum session cookie
 * is already active. This allows the SPA to "remember" an authenticated
 * user across hard page refreshes without storing tokens in localStorage.
 *
 * Pattern: [GRASP Controller] — delegates auth hydration to useAuthStore,
 * routing to AppRouter. Does not contain business logic itself.
 */
function SpaApp() {
    const initAuth = useAuthStore((state) => state.initAuth);

    useEffect(() => {
        // Bootstrap authentication state from the existing session cookie.
        // If the cookie is valid → populates useAuthStore.user.
        // If unauthenticated → useAuthStore.user remains null → AppRouter
        //   redirects to /login.
        initAuth();
    }, [initAuth]);

    return (
        <BrowserRouter>
            <AppRouter />
        </BrowserRouter>
    );
}

// Mount React into the Blade shell's #spa-root div
const container = document.getElementById('spa-root');
if (container) {
    const root = createRoot(container);
    root.render(<SpaApp />);
} else {
    console.error('[LINTAS SPA] Fatal: #spa-root mount point not found in DOM.');
}
