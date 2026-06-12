// resources/js/router/AppRouter.jsx
// ============================================================================
// AppRouter — Central Route Tree (GRASP Controller)
// ============================================================================
// Defines ALL client-side routes and their access guards.
//
// ROUTE GUARD PATTERN:
//   <AuthGuard>    — redirects to /login if not authenticated
//   <RoleGuard>    — redirects to /403 if authenticated but wrong role
//   <GuestGuard>   — redirects authenticated users away from /login
//
// RENDER GATING:
//   While useAuthStore.isInitializing is true (first /me check in-flight),
//   renders a full-screen skeleton loader. This prevents a flash of the
//   login page for authenticated users on hard refresh.
//
// URL PARAMS RULE (from Global Rules):
//   Filters, search, and pagination are stored in URL Search Params.
//   React Router's <useSearchParams> handles this — NOT local state.
//
// ROUTE GROUPING: Follows Domain Bounded Contexts (not CRUD).
// ============================================================================

import React from 'react';
import { Routes, Route, Navigate, Outlet } from 'react-router-dom';
import useAuthStore from '../store/useAuthStore';

// ── Lazy-loaded pages (code split per bounded context) ──
// Phase 0: Shell pages only. Feature pages added in Phase 1-4.
const LoginPage        = React.lazy(() => import('../pages/auth/LoginPage'));
const NotFoundPage     = React.lazy(() => import('../pages/errors/NotFoundPage'));
const ForbiddenPage    = React.lazy(() => import('../pages/errors/ForbiddenPage'));
const AppShell         = React.lazy(() => import('../pages/AppShell'));
const DashboardPage    = React.lazy(() => import('../pages/dashboard/DashboardPage'));

// Phase 1: Public & Officer Reporting pages
const LandingPage       = React.lazy(() => import('../pages/public/Landing'));
const PublicReportForm  = React.lazy(() => import('../pages/public/ReportForm'));
const OfficerReportForm = React.lazy(() => import('../pages/officer/ReportForm'));

// Phase 2: Workflow & Operational Ticketing pages
const ReportList = React.lazy(() => import('../pages/admin/ReportList'));
const TicketList = React.lazy(() => import('../pages/admin/TicketList'));
const TaskList   = React.lazy(() => import('../pages/officer/TaskList'));

// Phase 3: Executive Telemetry pages
const TelemetryDashboard = React.lazy(() => import('../pages/executive/Dashboard'));

// Phase 4: Governance & Configuration pages
const UserManagement = React.lazy(() => import('../pages/admin/UserManagement'));
const AuditLogs      = React.lazy(() => import('../pages/admin/AuditLogs'));
const SystemConfig   = React.lazy(() => import('../pages/admin/SystemConfig'));

// ── GIS Map (existing React island — preserved, integrated into SPA Router) ──
const GisMapPage       = React.lazy(() => import('../pages/gis/GisMapPage'));

/**
 * Full-screen loading skeleton shown while auth state is initializing.
 * Shown for < 300ms in normal conditions; prevents login-flash for
 * authenticated users on hard refresh.
 */
function InitializingSkeleton() {
    return (
        <div className="h-screen w-screen flex items-center justify-center bg-slate-50">
            <div className="flex flex-col items-center gap-3">
                {/* Loading bar — no animation library, pure CSS */}
                <div
                    className="w-48 h-0.5 bg-slate-200 overflow-hidden"
                    role="progressbar"
                    aria-label="Memuat aplikasi..."
                >
                    <div
                        className="h-full bg-blue-700"
                        style={{ animation: 'lintas-progress 1.4s ease infinite' }}
                    />
                </div>
                <span className="text-xs text-slate-400 tracking-wide">
                    LINTAS — Memuat...
                </span>
            </div>
            <style>{`
                @keyframes lintas-progress {
                    0%   { width: 0%; transform: translateX(0); }
                    50%  { width: 60%; }
                    100% { width: 100%; transform: translateX(100%); }
                }
            `}</style>
        </div>
    );
}

// ── Route Guard Components ──

/**
 * AuthGuard: Protects routes that require authentication.
 * Renders children if authenticated, redirects to /login otherwise.
 */
function AuthGuard() {
    const { isAuthenticated, isInitializing } = useAuthStore();
    if (isInitializing) return null; // Parent already handles skeleton
    if (!isAuthenticated) return <Navigate to="/login" replace />;
    return <Outlet />;
}

/**
 * GuestGuard: Prevents authenticated users from accessing /login.
 * Redirects them to their role-appropriate dashboard.
 */
function GuestGuard() {
    const { isAuthenticated, isInitializing, user } = useAuthStore();
    if (isInitializing) return null;
    if (isAuthenticated) {
        return <Navigate to={resolveDefaultRoute(user?.role)} replace />;
    }
    return <Outlet />;
}

/**
 * RoleGuard: Restricts a route to specific roles.
 * @param {string[]} roles - Allowed roles for the wrapped route
 */
function RoleGuard({ roles, children }) {
    const { user } = useAuthStore();
    if (!roles.includes(user?.role)) {
        return <Navigate to="/403" replace />;
    }
    return children || <Outlet />;
}

/**
 * Resolves the default landing route for a given user role.
 * Used by GuestGuard and the /dashboard catch-all redirect.
 */
function resolveDefaultRoute(role) {
    switch (role) {
        case 'kadis':             return '/telemetry/executive';
        case 'seksi':             return '/workflow/tasks';
        case 'petugas_lapangan':  return '/workflow/tasks';
        case 'admin':
        default:                  return '/dashboard';
    }
}

/**
 * AppRouter — The central route registry.
 */
export default function AppRouter() {
    const { isInitializing } = useAuthStore();

    // Render full-screen skeleton until the auth bootstrap /me call resolves
    if (isInitializing) {
        return <InitializingSkeleton />;
    }

    return (
        <React.Suspense fallback={<InitializingSkeleton />}>
            <Routes>

                {/* ── PUBLIC ROUTES ─────────────────────────────────────── */}
                <Route path="/"      element={<LandingPage />} />
                <Route path="/lapor" element={<PublicReportForm />} />

                {/* ── GUEST ROUTES ─────────────────────────────────────── */}
                <Route element={<GuestGuard />}>
                    <Route path="/login" element={<LoginPage />} />
                </Route>

                {/* ── PROTECTED ROUTES ──────────────────────────────────── */}
                <Route element={<AuthGuard />}>
                    {/* The AppShell wraps all protected pages with the
                        shared sidebar + topbar layout */}
                    <Route element={<AppShell />}>

                        {/* ─ Root redirect ─ */}
                        <Route
                            index
                            element={
                                <Navigate
                                    to={resolveDefaultRoute(useAuthStore.getState().user?.role)}
                                    replace
                                />
                            }
                        />

                        {/* ─ Admin / Kadis Dashboard ─ */}
                        <Route path="/dashboard" element={<DashboardPage />} />

                        {/* ─ GIS Spatial Monitor ─ */}
                        <Route path="/gis" element={<GisMapPage />} />

                        {/* ───────────────────────────────────────────────
                            BC-1: INTAKE WORKFLOW
                            Phase 1 will add these routes:
                              /intake/reports
                              /intake/reports/:id
                              /intake/submit (public report form)
                        ─────────────────────────────────────────────── */}
                        <Route path="/intake/reports" element={
                            <RoleGuard roles={['admin', 'seksi']}>
                                <ReportList />
                            </RoleGuard>
                        } />
                        <Route path="/petugas/lapor" element={
                            <RoleGuard roles={['petugas_lapangan', 'seksi', 'admin']}>
                                <OfficerReportForm />
                            </RoleGuard>
                        } />

                        {/* ───────────────────────────────────────────────
                            BC-2: WORKFLOW & TICKETING
                            Phase 2 will add:
                              /workflow/tickets
                              /workflow/tickets/:id
                              /workflow/tasks (officer queue)
                        ─────────────────────────────────────────────── */}
                        <Route path="/workflow/tickets" element={
                            <RoleGuard roles={['admin', 'seksi']}>
                                <TicketList />
                            </RoleGuard>
                        } />
                        <Route path="/workflow/tasks" element={
                            <RoleGuard roles={['admin', 'seksi', 'petugas_lapangan']}>
                                <TaskList />
                            </RoleGuard>
                        } />

                        {/* ───────────────────────────────────────────────
                            BC-3: ASSET GOVERNANCE
                            Phase 2 will add:
                              /assets
                              /assets/new
                              /assets/:id
                              /assets/:id/edit
                        ─────────────────────────────────────────────── */}

                        {/* ───────────────────────────────────────────────
                            BC-4: EXECUTIVE TELEMETRY
                            Phase 3 will add:
                              /telemetry/executive
                              /telemetry/summary
                        ─────────────────────────────────────────────── */}
                        <Route path="/telemetry/executive" element={
                            <RoleGuard roles={['kadis', 'admin']}>
                                <TelemetryDashboard />
                            </RoleGuard>
                        } />

                        {/* ───────────────────────────────────────────────
                            BC-5: GOVERNANCE & CONFIGURATION
                            Phase 4 will add:
                              /governance/users
                              /governance/map-settings
                              /governance/activity
                              /governance/announcements
                        ─────────────────────────────────────────────── */}
                        <Route path="/governance/users" element={
                            <RoleGuard roles={['admin']}>
                                <UserManagement />
                            </RoleGuard>
                        } />
                        <Route path="/governance/map-settings" element={
                            <RoleGuard roles={['admin']}>
                                <SystemConfig />
                            </RoleGuard>
                        } />
                        <Route path="/governance/activity" element={
                            <RoleGuard roles={['admin']}>
                                <AuditLogs />
                            </RoleGuard>
                        } />

                    </Route>
                </Route>

                {/* ── ERROR ROUTES ──────────────────────────────────────── */}
                <Route path="/403" element={<ForbiddenPage />} />
                <Route path="*"    element={<NotFoundPage />} />

            </Routes>
        </React.Suspense>
    );
}
