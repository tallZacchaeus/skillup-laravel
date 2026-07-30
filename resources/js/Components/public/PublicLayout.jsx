import { Head, Link, usePage } from '@inertiajs/react';
import Img from '@/Components/Img';
import { ChevronDown, Heart, Menu, ShoppingCart, X } from 'lucide-react';
import Toaster from '@/Components/Toaster';
import { useState } from 'react';
import { navLinks } from '@/data/site';
import { useScrolled } from '@/lib/hooks';
import { cn } from '@/lib/utils';

const headerGroups = [
    {
        label: 'Programs',
        matches: ['/courses', '/corporate', '/schools', '/career-center', '/jobs', '/employer'],
    },
    {
        label: 'Explore',
        matches: ['/blog', '/resources', '/events', '/community'],
    },
    {
        label: 'Company',
        matches: ['/about', '/contact', '/alumni', '/ambassadors', '/certificates/verify'],
    },
];

const footerColumns = [
    {
        title: 'Programs',
        items: [
            { label: 'Product Management', href: '/courses/product-management' },
            { label: 'Software Development', href: '/courses/software-development' },
            { label: 'Product Design (UI/UX)', href: '/courses/product-design' },
            { label: 'Virtual Assistance', href: '/courses/virtual-assistance' },
            { label: 'Data Analysis', href: '/courses/data-analysis' },
        ],
    },
    {
        title: 'Explore',
        items: [
            { label: 'All Courses', href: '/courses' },
            { label: 'Corporate Training', href: '/corporate' },
            { label: 'Community', href: '/community' },
            { label: 'Events & Webinars', href: '/events' },
            { label: 'Success Stories', href: '/about' },
        ],
    },
    {
        title: 'Company',
        items: [
            { label: 'About Us', href: '/about' },
            { label: 'Blog', href: '/blog' },
            { label: 'Careers', href: '/careers' },
            { label: 'Support', href: '/contact' },
            { label: 'Student Guides', href: '/resources' },
        ],
    },
];

const legalLinks = [
    { label: 'Privacy Policy', href: '/privacy' },
    { label: 'Terms', href: '/terms' },
    { label: 'Cookie Policy', href: '/cookie-policy' },
];

function FacebookIcon(props) {
    return (
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" aria-hidden="true" {...props}>
            <path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z" />
        </svg>
    );
}

function YoutubeIcon(props) {
    return (
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" aria-hidden="true" {...props}>
            <path d="M2.5 17a24.12 24.12 0 0 1 0-10 2 2 0 0 1 1.4-1.4 49.56 49.56 0 0 1 16.2 0A2 2 0 0 1 21.5 7a24.12 24.12 0 0 1 0 10 2 2 0 0 1-1.4 1.4 49.55 49.55 0 0 1-16.2 0A2 2 0 0 1 2.5 17" />
            <path d="m10 15 5-3-5-3z" />
        </svg>
    );
}

function LinkedinIcon(props) {
    return (
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" aria-hidden="true" {...props}>
            <path d="M16 8a6 6 0 0 1 6 6v7h-4v-7a2 2 0 0 0-2-2 2 2 0 0 0-2 2v7h-4V8h4v2.5" />
            <rect width="4" height="12" x="2" y="9" />
            <circle cx="4" cy="4" r="2" />
        </svg>
    );
}

const socialLinks = [
    { label: 'SkillUp on Facebook', href: 'https://www.facebook.com/skillupedtech', Icon: FacebookIcon },
    { label: 'SkillUp on YouTube', href: 'https://youtube.com/@theskillupedtech?si=LUIQ8mae_wX0tW88', Icon: YoutubeIcon },
    { label: 'SkillUp on LinkedIn', href: 'https://www.linkedin.com/company/theskillupglobal', Icon: LinkedinIcon },
];

export default function PublicLayout({ children }) {
    const { url, props } = usePage();
    const [open, setOpen] = useState(false);
    const scrolled = useScrolled(8);
    const user = props.auth?.user;
    const managedHeaderLinks = (props.navigation || [])
        .filter((item) => item.location === 'header')
        .map((item) => ({
            label: item.label,
            href: item.url,
            target: item.target || '_self',
        }));
    const activeNavLinks = managedHeaderLinks.length > 0 ? managedHeaderLinks : navLinks;
    const groupedNavLinks = buildHeaderNavigation(activeNavLinks);

    return (
        <div className="min-h-screen bg-white font-sans text-slate-900">
            {/* Site-wide SEO defaults. Individual pages override any of these by
                rendering a <Head> tag with the same head-key. */}
            <Head>
                <meta head-key="description" name="description" content="SkillUp — practical, mentor-led tech training and career programs for Africa’s next generation of tech talent." />
                <meta head-key="og:type" property="og:type" content="website" />
                <meta head-key="og:title" property="og:title" content="SkillUp Edtech" />
                <meta head-key="og:description" property="og:description" content="Practical, mentor-led tech training and career programs." />
                <meta head-key="og:image" property="og:image" content="/images/hero.jpg" />
                <meta head-key="twitter:card" name="twitter:card" content="summary_large_image" />
            </Head>
            <a
                href="#main"
                className="sr-only focus:not-sr-only focus:absolute focus:left-4 focus:top-4 focus:z-[100] focus:rounded-md focus:bg-white focus:px-4 focus:py-2 focus:font-semibold focus:text-skillup-navy focus:shadow-lg focus:ring-2 focus:ring-skillup-blue"
            >
                Skip to main content
            </a>
            <Toaster />
            <header
                className={cn(
                    'fixed inset-x-0 top-0 z-50 border-b transition-all duration-300 ease-premium',
                    scrolled
                        ? 'border-slate-200/80 bg-white/80 shadow-md backdrop-blur-lg'
                        : 'border-transparent bg-white/95 shadow-sm backdrop-blur-sm',
                )}
            >
                <div
                    className={cn(
                        'mx-auto flex max-w-7xl items-center justify-between gap-4 px-4 transition-all duration-300 ease-premium sm:px-6 lg:px-8',
                        scrolled ? 'h-16' : 'h-[72px]',
                    )}
                >
                    <Link
                        href="/"
                        className="flex min-w-[128px] items-center transition-transform duration-200 hover:scale-105"
                        aria-label="Go to SkillUp Edtech home"
                    >
                        <Img
                            src="/images/skillUp.png"
                            alt="SkillUp Edtech"
                            className={cn('w-auto object-contain transition-all duration-300', scrolled ? 'h-7' : 'h-8')}
                        />
                    </Link>

                    <nav className="hidden flex-1 items-center justify-center gap-1 md:flex lg:gap-2" aria-label="Primary">
                        {groupedNavLinks.map((item) => {
                            const active = item.items ? item.items.some((link) => isActiveLink(link, url)) : isActiveLink(item, url);

                            return item.items ? (
                                <DesktopNavGroup key={item.label} group={item} active={active} url={url} />
                            ) : (
                                <PublicNavLink key={`${item.label}-${item.href}`} item={item} active={active} />
                            );
                        })}
                    </nav>

                    <div className="hidden items-center justify-end gap-3 md:flex">
                        <Link
                            href="/cart"
                            aria-label={`Cart${props.cart?.count ? ` (${props.cart.count})` : ''}`}
                            className="relative inline-flex h-11 w-11 items-center justify-center rounded-md text-slate-600 transition-colors hover:bg-slate-100 hover:text-skillup-blue"
                        >
                            <ShoppingCart className="h-5 w-5" aria-hidden="true" />
                            {props.cart?.count > 0 && (
                                <span className="absolute -right-0.5 -top-0.5 flex h-5 min-w-[20px] items-center justify-center rounded-full bg-skillup-blue px-1 text-[10px] font-bold text-white">
                                    {props.cart.count}
                                </span>
                            )}
                        </Link>
                        {user && (
                            <Link
                                href="/wishlist"
                                aria-label={`Wishlist${props.wishlist?.count ? ` (${props.wishlist.count})` : ''}`}
                                className="relative inline-flex h-11 w-11 items-center justify-center rounded-md text-slate-600 transition-colors hover:bg-slate-100 hover:text-rose-500"
                            >
                                <Heart className="h-5 w-5" aria-hidden="true" />
                                {props.wishlist?.count > 0 && (
                                    <span className="absolute -right-0.5 -top-0.5 flex h-5 min-w-[20px] items-center justify-center rounded-full bg-rose-500 px-1 text-[10px] font-bold text-white">
                                        {props.wishlist.count}
                                    </span>
                                )}
                            </Link>
                        )}
                        <Link
                            href={user ? '/dashboard' : '/login'}
                            className="inline-flex h-10 items-center justify-center rounded-md border border-skillup-orange bg-transparent px-5 text-sm font-semibold text-orange-700 transition-colors hover:bg-orange-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-skillup-orange/40"
                        >
                            {user ? 'Dashboard' : 'Login'}
                        </Link>
                        <Link
                            href="/courses"
                            className="inline-flex h-10 min-w-[116px] items-center justify-center rounded-md bg-blue-900 px-5 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-blue-700 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-900/40"
                        >
                            Apply Now
                        </Link>
                    </div>

                    <button
                        type="button"
                        className="inline-flex h-11 w-11 items-center justify-center rounded-md text-slate-700 transition-colors hover:bg-slate-100 hover:text-skillup-blue md:hidden"
                        aria-label="Toggle navigation"
                        aria-expanded={open}
                        onClick={() => setOpen((value) => !value)}
                    >
                        {open ? <X className="h-6 w-6" /> : <Menu className="h-6 w-6" />}
                    </button>
                </div>

                <div
                    className={cn(
                        'md:hidden',
                        'overflow-hidden transition-all duration-300 ease-out',
                        open ? 'visible max-h-[85vh] overflow-y-auto opacity-100' : 'invisible max-h-0 opacity-0',
                    )}
                >
                    <div className="space-y-1 border-t border-slate-200 bg-white px-4 py-4 shadow-lg">
                        {groupedNavLinks.map((item) => {
                            if (item.items) {
                                return (
                                    <div key={item.label} className="pb-2">
                                        <p className="px-3 pt-2 text-xs font-bold uppercase tracking-wide text-slate-500">{item.label}</p>
                                        <div className="mt-1 space-y-1">
                                            {item.items.map((link) => (
                                                <PublicNavLink
                                                    key={`${item.label}-${link.label}-${link.href}`}
                                                    item={link}
                                                    active={isActiveLink(link, url)}
                                                    mobile
                                                    onClick={() => setOpen(false)}
                                                />
                                            ))}
                                        </div>
                                    </div>
                                );
                            }

                            return (
                                <PublicNavLink
                                    key={`${item.label}-${item.href}`}
                                    item={item}
                                    active={isActiveLink(item, url)}
                                    mobile
                                    onClick={() => setOpen(false)}
                                />
                            );
                        })}
                        <div className="grid gap-2 border-t border-slate-200 pt-4">
                            <Link
                                href={user ? '/dashboard' : '/login'}
                                className="inline-flex h-11 w-full items-center justify-center rounded-md border border-skillup-orange text-sm font-semibold text-orange-700 transition-all duration-300 hover:bg-skillup-orange hover:text-white"
                                onClick={() => setOpen(false)}
                            >
                                {user ? 'Dashboard' : 'Login'}
                            </Link>
                            <Link
                                href="/courses"
                                className="inline-flex h-11 w-full items-center justify-center rounded-md bg-blue-900 text-sm font-semibold text-white transition-colors hover:bg-blue-700"
                                onClick={() => setOpen(false)}
                            >
                                Apply Now
                            </Link>
                        </div>
                    </div>
                </div>
            </header>

            <main id="main" tabIndex={-1} className="focus:outline-none">{children}</main>

            <footer className="bg-skillup-light py-16">
                <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div className="grid gap-8 md:grid-cols-2 lg:grid-cols-4">
                        <div>
                            <Img src="/images/skillUp.png" alt="SkillUp Edtech" className="h-8 w-auto object-contain" />
                            <p className="mt-5 max-w-sm text-sm leading-6 text-slate-600">
                                A tech career academy helping African learners build practical skills, portfolio confidence, and access to opportunity.
                            </p>
                            <div className="mt-6 flex gap-4">
                                {socialLinks.map(({ label, href, Icon }) => (
                                    <a
                                        key={label}
                                        href={href}
                                        aria-label={label}
                                        target="_blank"
                                        rel="noreferrer"
                                        className="inline-flex h-11 w-11 items-center justify-center rounded-full text-slate-600 transition-colors hover:bg-white hover:text-skillup-blue"
                                    >
                                        <Icon className="h-5 w-5" />
                                    </a>
                                ))}
                            </div>
                        </div>

                        {footerColumns.map((column) => (
                            <div key={column.title}>
                                <h2 className="font-semibold text-slate-900">{column.title}</h2>
                                <div className="mt-4 space-y-2">
                                    {column.items.map((item) => (
                                        <Link
                                            key={`${column.title}-${item.label}`}
                                            href={item.href}
                                            className="block text-sm leading-7 text-slate-600 transition-colors hover:text-slate-900"
                                        >
                                            {item.label}
                                        </Link>
                                    ))}
                                </div>
                            </div>
                        ))}
                    </div>

                    <div className="mt-12 flex flex-col gap-4 border-t border-blue-200 pt-6 text-sm text-slate-600 sm:flex-row sm:items-center sm:justify-between">
                        <p>© {new Date().getFullYear()} SkillUp Edtech. All rights reserved.</p>
                        <div className="flex flex-wrap gap-x-6 gap-y-2">
                            {legalLinks.map((item) => (
                                <Link
                                    key={item.href}
                                    href={item.href}
                                    className="transition-colors hover:text-skillup-navy"
                                >
                                    {item.label}
                                </Link>
                            ))}
                        </div>
                    </div>
                </div>
            </footer>
        </div>
    );
}

function buildHeaderNavigation(links) {
    const taken = new Set();
    const takeMatchingLinks = (matcher) => links.filter((item, index) => {
        if (taken.has(index) || !matcher(item)) {
            return false;
        }

        taken.add(index);

        return true;
    });

    const home = takeMatchingLinks((item) => item.href === '/');
    const groups = headerGroups
        .map((group) => ({
            label: group.label,
            items: takeMatchingLinks((item) => group.matches.some((href) => item.href === href || item.href.startsWith(`${href}/`))),
        }))
        .filter((group) => group.items.length > 0);
    const uncategorized = takeMatchingLinks(() => true);

    return [
        ...home,
        ...groups,
        ...(uncategorized.length > 0 ? [{ label: 'More', items: uncategorized }] : []),
    ];
}

function isActiveLink(item, url) {
    if (!item.href || item.href.startsWith('http://') || item.href.startsWith('https://')) {
        return false;
    }

    return item.href === '/' ? url === '/' : url.startsWith(item.href);
}

function DesktopNavGroup({ group, active, url }) {
    return (
        <div className="group relative">
            <button
                type="button"
                className={cn(
                    "relative inline-flex items-center gap-1 whitespace-nowrap rounded-md px-3 py-2 text-sm font-medium transition-colors hover:text-blue-700 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-skillup-blue/30 after:absolute after:inset-x-3 after:-bottom-0.5 after:h-0.5 after:origin-left after:rounded-full after:bg-skillup-blue after:transition-transform after:duration-300 after:content-[''] group-hover:after:scale-x-100 group-focus-within:after:scale-x-100",
                    active ? 'text-blue-800 after:scale-x-100' : 'text-gray-700 after:scale-x-0',
                )}
                aria-haspopup="true"
            >
                {group.label}
                <ChevronDown className="h-4 w-4 transition-transform duration-300 group-hover:rotate-180 group-focus-within:rotate-180" />
            </button>
            <div className="invisible absolute left-1/2 top-full z-50 w-56 -translate-x-1/2 translate-y-1 pt-3 opacity-0 transition-all duration-200 ease-premium group-hover:visible group-hover:translate-y-0 group-hover:opacity-100 group-focus-within:visible group-focus-within:translate-y-0 group-focus-within:opacity-100">
                <div className="rounded-xl border border-slate-200 bg-white p-2 shadow-elevated">
                    {group.items.map((item) => (
                        <PublicNavLink
                            key={`${group.label}-${item.label}-${item.href}`}
                            item={item}
                            active={isActiveLink(item, url)}
                            variant="dropdown"
                        />
                    ))}
                </div>
            </div>
        </div>
    );
}

function PublicNavLink({ item, active, mobile = false, variant = 'default', onClick }) {
    const external = item.href.startsWith('http://') || item.href.startsWith('https://') || item.target === '_blank';
    const className = cn(
        mobile
            ? cn(
                'flex w-full items-center justify-between rounded-md px-3 py-2.5 text-base font-medium transition-colors hover:bg-gray-50 hover:text-blue-600',
                active ? 'bg-blue-50 text-blue-800' : 'text-gray-700',
            )
            : variant === 'dropdown'
                ? cn(
                    'block rounded-md px-3 py-2.5 text-sm font-medium transition-colors hover:bg-blue-50 hover:text-skillup-blue',
                    active ? 'bg-blue-50 text-skillup-blue' : 'text-slate-700',
                )
                : cn(
                    "relative whitespace-nowrap rounded-md px-3 py-2 text-sm font-medium transition-colors hover:text-blue-700 after:absolute after:inset-x-3 after:-bottom-0.5 after:h-0.5 after:origin-left after:rounded-full after:bg-skillup-blue after:transition-transform after:duration-300 after:content-[''] hover:after:scale-x-100",
                    active ? 'text-blue-800 after:scale-x-100' : 'text-gray-700 after:scale-x-0',
                ),
    );

    if (external) {
        return (
            <a
                href={item.href}
                target={item.target || '_blank'}
                rel={item.target === '_blank' ? 'noreferrer' : undefined}
                className={className}
                onClick={onClick}
            >
                {item.label}
            </a>
        );
    }

    return (
        <Link href={item.href} className={className} onClick={onClick} aria-current={active ? 'page' : undefined}>
            {item.label}
        </Link>
    );
}
