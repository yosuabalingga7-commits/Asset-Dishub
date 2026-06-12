// resources/js/pages/AppShell.jsx
// ============================================================================
// AppShell — Protected Layout Wrapper (GRASP Controller)
// ============================================================================
// Renders the persistent sidebar + topbar UI frame for all authenticated
// pages. The <Outlet /> is where each feature page injects its content.
//
// Design compliance:
//   - No rounded corners (border-radius: 0 global)
//   - No shadows
//   - Sidebar: 240px fixed, border-right: 1px solid slate-200
//   - Topbar: 48px, border-bottom: 1px solid slate-200
//   - Font: Roboto via CSS design system
// ============================================================================

import React, { useState } from 'react';
import { Outlet, NavLink, useNavigate } from 'react-router-dom';
import useAuthStore from '../store/useAuthStore';
import {
    LayoutDashboard,
    Map,
    ClipboardList,
    Wrench,
    Users,
    BarChart2,
    Settings,
    LogOut,
    ChevronRight,
    Menu,
    X,
    Bell,
} from 'lucide-react';

// ── Navigation structure grouped by Bounded Context ──
const NAV_GROUPS = [
    {
        label: 'Monitor',
        items: [
            { to: '/dashboard', icon: LayoutDashboard, label: 'Dashboard', roles: ['admin', 'kadis'] },
            { to: '/gis',       icon: Map,             label: 'Peta Spasial', roles: ['admin', 'kadis', 'seksi'] },
        ],
    },
    {
        label: 'Intake & Alur Kerja',
        items: [
            { to: '/intake/reports',  icon: ClipboardList, label: 'Laporan Masuk',  roles: ['admin', 'seksi'] },
            { to: '/workflow/tasks',  icon: Wrench,        label: 'Tugas Lapangan', roles: ['admin', 'seksi', 'petugas_lapangan'] },
            { to: '/workflow/tickets',icon: Wrench,        label: 'Tiket Maintenance', roles: ['admin', 'seksi'] },
        ],
    },
    {
        label: 'Aset',
        items: [
            { to: '/assets', icon: LayoutDashboard, label: 'Manajemen Aset', roles: ['admin'] },
        ],
    },
    {
        label: 'Eksekutif',
        items: [
            { to: '/telemetry/executive', icon: BarChart2, label: 'Telemetri Eksekutif', roles: ['admin', 'kadis'] },
        ],
    },
    {
        label: 'Konfigurasi',
        items: [
            { to: '/governance/users',       icon: Users,    label: 'Manajemen User', roles: ['admin'] },
            { to: '/governance/map-settings', icon: Settings, label: 'Pengaturan Peta', roles: ['admin'] },
            { to: '/governance/activity',    icon: ClipboardList, label: 'Log Aktivitas', roles: ['admin'] },
        ],
    },
];

function SidebarNavItem({ item, userRole }) {
    if (!item.roles.includes(userRole)) return null;

    return (
        <NavLink
            to={item.to}
            className={({ isActive }) =>
                `lintas-nav-item ${isActive ? 'active' : ''}`
            }
        >
            <item.icon size={15} strokeWidth={1.75} className="shrink-0" />
            <span className="truncate">{item.label}</span>
        </NavLink>
    );
}

function Sidebar({ user, onLogout, collapsed, onToggle }) {
    return (
        <aside
            className="lintas-sidebar"
            style={{ width: collapsed ? '48px' : '240px', transition: 'width 200ms ease' }}
        >
            {/* Brand header */}
            <div className="lintas-section-header h-12 shrink-0">
                {!collapsed && (
                    <div className="flex flex-col leading-tight">
                        <span className="font-medium text-slate-900 text-sm tracking-tight">LINTAS</span>
                        <span className="text-[10px] text-slate-400 uppercase tracking-widest">Dishub KBB</span>
                    </div>
                )}
                <button
                    id="sidebar-toggle-btn"
                    onClick={onToggle}
                    className="p-1 text-slate-400 hover:text-slate-700 hover:bg-slate-100 transition-colors"
                    aria-label="Toggle sidebar"
                >
                    {collapsed ? <ChevronRight size={15} /> : <Menu size={15} />}
                </button>
            </div>

            {/* Navigation groups */}
            <nav className="flex-1 overflow-y-auto py-2">
                {NAV_GROUPS.map((group) => {
                    const visibleItems = group.items.filter(i => i.roles.includes(user?.role));
                    if (visibleItems.length === 0) return null;

                    return (
                        <div key={group.label} className="mb-1">
                            {!collapsed && (
                                <div className="px-4 py-1.5">
                                    <span className="text-[10px] font-medium text-slate-400 uppercase tracking-widest">
                                        {group.label}
                                    </span>
                                </div>
                            )}
                            {visibleItems.map((item) => (
                                <SidebarNavItem key={item.to} item={item} userRole={user?.role} />
                            ))}
                        </div>
                    );
                })}
            </nav>

            {/* User info + logout */}
            <div className="border-t border-slate-200 shrink-0">
                {!collapsed && (
                    <div className="px-4 py-2 border-b border-slate-200">
                        <p className="text-xs font-medium text-slate-800 truncate">{user?.name}</p>
                        <p className="text-[10px] text-slate-400 uppercase tracking-wider">{user?.role}</p>
                    </div>
                )}
                <button
                    id="logout-btn"
                    onClick={onLogout}
                    className="lintas-nav-item w-full text-red-600 hover:bg-red-50 border-l-transparent"
                >
                    <LogOut size={15} strokeWidth={1.75} className="shrink-0" />
                    {!collapsed && <span>Keluar</span>}
                </button>
            </div>
        </aside>
    );
}

function Topbar({ user, pageTitle }) {
    return (
        <header className="lintas-topbar shrink-0">
            <div className="flex-1">
                <h1 className="text-sm font-medium text-slate-800 tracking-tight">
                    {pageTitle ?? 'LINTAS'}
                </h1>
            </div>
            <div className="flex items-center gap-2">
                {/* Notification bell — wired in Phase 4 */}
                <button
                    id="topbar-notifications-btn"
                    className="p-1.5 text-slate-400 hover:text-slate-700 hover:bg-slate-100 transition-colors"
                    aria-label="Notifikasi"
                >
                    <Bell size={15} strokeWidth={1.75} />
                </button>
                {/* User avatar */}
                <div className="flex items-center gap-2 pl-2 border-l border-slate-200">
                    {user?.foto ? (
                        <img
                            src={user.foto}
                            alt={user.name}
                            className="w-6 h-6 object-cover border border-slate-200"
                        />
                    ) : (
                        <div className="w-6 h-6 bg-blue-700 flex items-center justify-center">
                            <span className="text-[10px] font-medium text-white">
                                {user?.name?.[0]?.toUpperCase() ?? 'U'}
                            </span>
                        </div>
                    )}
                    <span className="text-xs text-slate-600 hidden sm:block">{user?.name}</span>
                </div>
            </div>
        </header>
    );
}

export default function AppShell() {
    const { user, logout } = useAuthStore();
    const navigate = useNavigate();
    const [collapsed, setCollapsed] = useState(false);

    const handleLogout = async () => {
        await logout();
        navigate('/login', { replace: true });
    };

    return (
        <div className="lintas-layout">
            <Sidebar
                user={user}
                onLogout={handleLogout}
                collapsed={collapsed}
                onToggle={() => setCollapsed(c => !c)}
            />
            <div className="lintas-main">
                <Topbar user={user} />
                <main className="lintas-content">
                    <Outlet />
                </main>
            </div>
        </div>
    );
}
