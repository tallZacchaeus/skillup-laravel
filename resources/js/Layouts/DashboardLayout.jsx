import { useState } from 'react';
import { Link, usePage } from '@inertiajs/react';
import { Award, Bell, BookOpen, Heart, LayoutDashboard, LogOut, Menu, User, X } from 'lucide-react';
import Img from '@/Components/Img';

const NAV = [
    { label: 'Dashboard', href: '/dashboard', icon: LayoutDashboard },
    { label: 'My Courses', href: '/dashboard#my-courses', icon: BookOpen },
    { label: 'Wishlist', href: '/wishlist', icon: Heart },
    { label: 'Certificates', href: '/dashboard#certificates', icon: Award },
];

const FOOTER_LINKS = [
    { label: 'Help Centre', href: '/resources' },
    { label: 'Support', href: '/contact' },
    { label: 'Contact', href: '/contact' },
    { label: 'Privacy', href: '/privacy' },
    { label: 'Terms', href: '/terms' },
];

/**
 * Authenticated learner shell: a lightweight top nav (Dashboard, My Courses,
 * Wishlist, Certificates, notifications, profile, logout) and a minimal footer.
 * Distinct from the marketing PublicLayout so learners get a workspace, not a
 * sales nav.
 */
export default function DashboardLayout({ children, notificationsCount = 0 }) {
    const { props } = usePage();
    const user = props.auth?.user;
    const firstName = (user?.name || 'Learner').split(' ')[0];
    const initial = firstName.charAt(0).toUpperCase();

    const [mobileOpen, setMobileOpen] = useState(false);
    const [profileOpen, setProfileOpen] = useState(false);

    return (
        <div className="flex min-h-dvh flex-col bg-slate-50">
            <a href="#dashboard-main" className="sr-only focus:not-sr-only focus:absolute focus:left-4 focus:top-4 focus:z-50 focus:rounded-md focus:bg-skillup-blue focus:px-4 focus:py-2 focus:text-sm focus:font-semibold focus:text-white">
                Skip to content
            </a>

            {/* Top nav */}
            <header className="sticky top-0 z-40 border-b border-slate-200 bg-white/95 backdrop-blur">
                <nav className="mx-auto flex h-16 max-w-7xl items-center justify-between gap-4 px-4 sm:px-6 lg:px-8" aria-label="Learner">
                    <Link href="/dashboard" className="flex items-center rounded focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-skillup-blue/40">
                        <Img src="/images/skillUp.png" alt="SkillUp — go to dashboard" className="h-8 w-auto object-contain" eager />
                    </Link>

                    {/* Desktop nav */}
                    <ul className="hidden items-center gap-1 lg:flex">
                        {NAV.map((item) => (
                            <li key={item.label}>
                                <Link href={item.href} className="inline-flex items-center gap-2 rounded-md px-3 py-2 text-sm font-semibold text-slate-600 transition-colors hover:bg-slate-100 hover:text-skillup-navy focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-skillup-blue/40">
                                    <item.icon className="h-4 w-4" aria-hidden="true" />
                                    {item.label}
                                </Link>
                            </li>
                        ))}
                    </ul>

                    <div className="flex items-center gap-1 sm:gap-2">
                        {/* Notifications */}
                        <a href="/dashboard#notifications" aria-label={`Notifications${notificationsCount ? `, ${notificationsCount} unread` : ''}`} className="relative flex h-10 w-10 items-center justify-center rounded-full text-slate-600 transition-colors hover:bg-slate-100 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-skillup-blue/40">
                            <Bell className="h-5 w-5" aria-hidden="true" />
                            {notificationsCount > 0 && (
                                <span className="absolute right-1.5 top-1.5 flex h-4 min-w-4 items-center justify-center rounded-full bg-red-500 px-1 text-[10px] font-bold text-white">
                                    {notificationsCount > 9 ? '9+' : notificationsCount}
                                </span>
                            )}
                        </a>

                        {/* Profile menu (desktop) */}
                        <div className="relative hidden lg:block">
                            <button
                                type="button"
                                onClick={() => setProfileOpen((o) => !o)}
                                aria-expanded={profileOpen}
                                aria-haspopup="menu"
                                className="flex items-center gap-2 rounded-full py-1 pl-1 pr-2 text-sm font-semibold text-slate-700 transition-colors hover:bg-slate-100 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-skillup-blue/40"
                            >
                                <span className="flex h-8 w-8 items-center justify-center rounded-full bg-skillup-blue text-sm font-bold text-white" aria-hidden="true">{initial}</span>
                                <span className="max-w-24 truncate">{firstName}</span>
                            </button>
                            {profileOpen && (
                                <>
                                    <button type="button" className="fixed inset-0 z-0 cursor-default" aria-hidden="true" tabIndex={-1} onClick={() => setProfileOpen(false)} />
                                    <div role="menu" className="absolute right-0 z-10 mt-2 w-48 overflow-hidden rounded-xl border border-slate-200 bg-white py-1 shadow-elevated">
                                        <Link href="/profile" role="menuitem" className="flex items-center gap-2 px-4 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-50">
                                            <User className="h-4 w-4" aria-hidden="true" /> Profile
                                        </Link>
                                        <Link href="/logout" method="post" as="button" role="menuitem" className="flex w-full items-center gap-2 px-4 py-2.5 text-left text-sm font-medium text-red-600 hover:bg-red-50">
                                            <LogOut className="h-4 w-4" aria-hidden="true" /> Logout
                                        </Link>
                                    </div>
                                </>
                            )}
                        </div>

                        {/* Mobile toggle */}
                        <button
                            type="button"
                            onClick={() => setMobileOpen((o) => !o)}
                            aria-expanded={mobileOpen}
                            aria-controls="mobile-nav"
                            aria-label="Toggle menu"
                            className="flex h-10 w-10 items-center justify-center rounded-full text-slate-600 transition-colors hover:bg-slate-100 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-skillup-blue/40 lg:hidden"
                        >
                            {mobileOpen ? <X className="h-5 w-5" aria-hidden="true" /> : <Menu className="h-5 w-5" aria-hidden="true" />}
                        </button>
                    </div>
                </nav>

                {/* Mobile nav */}
                {mobileOpen && (
                    <div id="mobile-nav" className="border-t border-slate-200 bg-white px-4 py-3 lg:hidden">
                        <ul className="space-y-1">
                            {NAV.map((item) => (
                                <li key={item.label}>
                                    <Link href={item.href} onClick={() => setMobileOpen(false)} className="flex items-center gap-3 rounded-md px-3 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-100">
                                        <item.icon className="h-5 w-5 text-skillup-blue" aria-hidden="true" />
                                        {item.label}
                                    </Link>
                                </li>
                            ))}
                            <li className="border-t border-slate-100 pt-1">
                                <Link href="/profile" onClick={() => setMobileOpen(false)} className="flex items-center gap-3 rounded-md px-3 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-100">
                                    <User className="h-5 w-5 text-skillup-blue" aria-hidden="true" /> Profile
                                </Link>
                            </li>
                            <li>
                                <Link href="/logout" method="post" as="button" className="flex w-full items-center gap-3 rounded-md px-3 py-2.5 text-left text-sm font-semibold text-red-600 hover:bg-red-50">
                                    <LogOut className="h-5 w-5" aria-hidden="true" /> Logout
                                </Link>
                            </li>
                        </ul>
                    </div>
                )}
            </header>

            <main id="dashboard-main" className="flex-1">{children}</main>

            {/* Lightweight learner footer */}
            <footer className="border-t border-slate-200 bg-white">
                <div className="mx-auto flex max-w-7xl flex-col items-center justify-between gap-4 px-4 py-6 sm:flex-row sm:px-6 lg:px-8">
                    <p className="text-sm text-slate-500">© {new Date().getFullYear()} SkillUp Edtech. All rights reserved.</p>
                    <ul className="flex flex-wrap items-center justify-center gap-x-5 gap-y-2">
                        {FOOTER_LINKS.map((link) => (
                            <li key={link.label}>
                                <Link href={link.href} className="text-sm text-slate-500 transition-colors hover:text-skillup-blue focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-skillup-blue/40">
                                    {link.label}
                                </Link>
                            </li>
                        ))}
                    </ul>
                </div>
            </footer>
        </div>
    );
}
