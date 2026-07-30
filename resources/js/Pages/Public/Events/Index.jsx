import { useCallback, useEffect, useRef, useState } from 'react';
import { Head, Link, router } from '@inertiajs/react';
import { ArrowRight, CalendarDays, CalendarPlus, Clock, Download, Monitor, Search, X } from 'lucide-react';
import PublicLayout from '@/Components/public/PublicLayout';
import Img from '@/Components/Img';
import Breadcrumbs from '@/Components/Breadcrumbs';
import NewsletterCta from '@/Components/public/NewsletterCta';
import EventCard from '@/Components/public/events/EventCard';
import EventEmptyState from '@/Components/public/events/EventEmptyState';
import { buttonVariants } from '@/Components/ui/button';
import { useRevealScope } from '@/lib/animations';
import { googleCalendarUrl, downloadIcs } from '@/lib/calendar';
import { cn } from '@/lib/utils';

const EMPTY_SEO = { title: 'Events & Webinars', description: '', canonical: '', ogImage: '' };

export default function EventsIndex({
    events = [],
    featuredEvent = null,
    categories = [],
    filters = {},
    hasPast = false,
    pagination = null,
    seo = EMPTY_SEO,
}) {
    const scope = useRevealScope();
    const [search, setSearch] = useState(filters.search || '');
    const [loading, setLoading] = useState(false);
    const firstRender = useRef(true);
    const resultsRef = useRef(null);

    const view = filters.view || 'upcoming';

    useEffect(() => {
        setSearch(filters.search || '');
    }, [filters.search]);

    const navigate = useCallback(
        (overrides = {}, { scrollToResults = false } = {}) => {
            const next = { ...filters, ...overrides };
            const params = {};
            Object.entries(next).forEach(([key, value]) => {
                if (key === 'view' && value === 'upcoming') return;
                if (value) params[key] = value;
            });
            router.get('/events', params, {
                preserveScroll: true,
                preserveState: true,
                only: ['events', 'featuredEvent', 'categories', 'filters', 'hasPast', 'pagination', 'seo'],
                onStart: () => setLoading(true),
                onFinish: () => setLoading(false),
                onSuccess: () => {
                    if (scrollToResults) resultsRef.current?.scrollIntoView({ behavior: 'smooth', block: 'start' });
                },
            });
        },
        [filters],
    );

    useEffect(() => {
        if (firstRender.current) {
            firstRender.current = false;
            return;
        }
        const id = setTimeout(() => {
            if ((filters.search || '') !== search) navigate({ search, page: undefined });
        }, 350);
        return () => clearTimeout(id);
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [search]);

    const activeFilters = Boolean(filters.search || filters.category);
    const hasAnything = events.length > 0 || featuredEvent;
    const showToolbar = categories.length > 0 || activeFilters || hasPast;

    const clearAll = () => {
        setSearch('');
        router.get('/events', {}, { preserveScroll: true, only: ['events', 'featuredEvent', 'categories', 'filters', 'hasPast', 'pagination', 'seo'] });
    };

    const gcalUrl = featuredEvent ? googleCalendarUrl(featuredEvent) : null;

    return (
        <PublicLayout>
            <Head title={seo.title}>
                <meta head-key="description" name="description" content={seo.description} />
                {seo.canonical && <link head-key="canonical" rel="canonical" href={seo.canonical} />}
                <meta head-key="og:type" property="og:type" content="website" />
                <meta head-key="og:title" property="og:title" content={seo.title} />
                <meta head-key="og:description" property="og:description" content={seo.description} />
                {seo.canonical && <meta head-key="og:url" property="og:url" content={seo.canonical} />}
                {seo.ogImage && <meta head-key="og:image" property="og:image" content={seo.ogImage} />}
                <meta head-key="twitter:card" name="twitter:card" content="summary_large_image" />
                <meta head-key="twitter:title" name="twitter:title" content={seo.title} />
                <meta head-key="twitter:description" name="twitter:description" content={seo.description} />
            </Head>

            <div ref={scope}>
                {/* ─── Hero ─────────────────────────────────────────── */}
                <section className="bg-skillup-navy pb-16 pt-28 text-white sm:pt-32">
                    <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                        <Breadcrumbs items={[{ label: 'Home', href: '/' }, { label: 'Events' }]} tone="light" />
                        <span data-hero className="mt-5 inline-block text-xs font-bold uppercase tracking-[0.14em] text-blue-300">
                            Events & webinars
                        </span>
                        <h1 data-hero className="mt-3 max-w-3xl text-4xl font-bold leading-[1.1] tracking-tight sm:text-5xl">
                            Live, practical learning — webinars, workshops & info sessions
                        </h1>
                        <p data-hero className="mt-5 max-w-2xl text-lg leading-8 text-blue-100">
                            Learn from practitioners in real time, ask your questions, and level up your tech skills with SkillUp.
                        </p>
                        <div data-hero className="mt-8 flex flex-col gap-3 sm:flex-row">
                            <a href="#events" className={cn(buttonVariants({ size: 'lg' }), 'bg-white text-skillup-navy hover:bg-blue-50')}>
                                View upcoming events
                            </a>
                            <a href="#alerts" className={cn(buttonVariants({ variant: 'secondary', size: 'lg' }), 'bg-white/10 text-white ring-1 ring-inset ring-white/30 hover:bg-white/20')}>
                                Get event alerts
                            </a>
                        </div>
                    </div>
                </section>

                {/* ─── Featured event ───────────────────────────────── */}
                {featuredEvent && (
                    <section className="bg-white py-12 sm:py-16" data-reveal>
                        <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                            <div className="grid items-center gap-8 rounded-3xl border border-slate-200 bg-slate-50 p-6 lg:grid-cols-2 lg:gap-12 lg:p-10">
                                <Link href={featuredEvent.url} className="group block overflow-hidden rounded-2xl">
                                    <Img src={featuredEvent.image} alt="" className="aspect-[16/9] w-full object-cover shadow-md transition-transform duration-500 group-hover:scale-105" eager />
                                </Link>
                                <div>
                                    <span className="inline-flex items-center gap-1.5 rounded-full bg-skillup-blue/10 px-3 py-1 text-xs font-semibold text-skillup-blue">
                                        Featured{featuredEvent.category ? ` · ${featuredEvent.category.label}` : ''}
                                    </span>
                                    <h2 className="mt-4 text-2xl font-bold tracking-tight text-skillup-navy sm:text-3xl">
                                        <Link href={featuredEvent.url} className="transition-colors hover:text-skillup-blue">{featuredEvent.title}</Link>
                                    </h2>
                                    {featuredEvent.summary && <p className="mt-3 text-base leading-7 text-slate-600">{featuredEvent.summary}</p>}
                                    <dl className="mt-4 flex flex-wrap gap-x-5 gap-y-2 text-sm text-slate-600">
                                        {featuredEvent.dateLabel && <span className="inline-flex items-center gap-1.5"><CalendarDays className="h-4 w-4 text-skillup-blue" aria-hidden="true" />{featuredEvent.dateLabel}{featuredEvent.timeLabel ? ` · ${featuredEvent.timeLabel}` : ''}</span>}
                                        {featuredEvent.duration && <span className="inline-flex items-center gap-1.5"><Clock className="h-4 w-4 text-skillup-blue" aria-hidden="true" />{featuredEvent.duration}</span>}
                                        <span className="inline-flex items-center gap-1.5"><Monitor className="h-4 w-4 text-skillup-blue" aria-hidden="true" />{featuredEvent.deliveryMode}</span>
                                    </dl>
                                    <div className="mt-6 flex flex-wrap items-center gap-3">
                                        <Link href={featuredEvent.url} className={cn(buttonVariants({ variant: 'default' }))}>
                                            Register
                                            <ArrowRight className="h-4 w-4" aria-hidden="true" />
                                        </Link>
                                        {gcalUrl && (
                                            <a href={gcalUrl} target="_blank" rel="noreferrer" className={cn(buttonVariants({ variant: 'outline', size: 'sm' }))}>
                                                <CalendarPlus className="h-4 w-4" aria-hidden="true" />
                                                Add to Google Calendar
                                            </a>
                                        )}
                                        <button type="button" onClick={() => downloadIcs(featuredEvent)} className={cn(buttonVariants({ variant: 'outline', size: 'sm' }))}>
                                            <Download className="h-4 w-4" aria-hidden="true" />
                                            Download .ics
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>
                )}

                {/* ─── Toolbar: search + view + categories ──────────── */}
                {showToolbar && (
                    <section className="sticky top-[68px] z-20 border-y border-slate-200 bg-white/95 py-4 backdrop-blur">
                        <div className="mx-auto flex max-w-7xl flex-col gap-4 px-4 sm:px-6 lg:flex-row lg:items-center lg:justify-between lg:px-8">
                            <div className="flex flex-wrap items-center gap-3">
                                <div className="inline-flex rounded-full bg-slate-100 p-1" role="tablist" aria-label="Event view">
                                    <ViewTab active={view === 'upcoming'} onClick={() => navigate({ view: 'upcoming', page: undefined })}>Upcoming</ViewTab>
                                    {hasPast && <ViewTab active={view === 'past'} onClick={() => navigate({ view: 'past', page: undefined })}>Past</ViewTab>}
                                </div>
                                <div className="relative w-full sm:w-64">
                                    <label htmlFor="event-search" className="sr-only">Search events</label>
                                    <Search className="pointer-events-none absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" aria-hidden="true" />
                                    <input
                                        id="event-search"
                                        type="search"
                                        value={search}
                                        onChange={(e) => setSearch(e.target.value)}
                                        placeholder="Search events..."
                                        className="h-11 w-full rounded-full border border-slate-300 bg-white pl-10 pr-10 text-sm text-slate-900 shadow-sm placeholder:text-slate-500 focus:border-skillup-blue focus:outline-none focus:ring-2 focus:ring-skillup-blue/20"
                                    />
                                    {search && (
                                        <button type="button" onClick={() => { setSearch(''); navigate({ search: undefined, page: undefined }); }} aria-label="Clear search" className="absolute right-2 top-1/2 inline-flex h-7 w-7 -translate-y-1/2 items-center justify-center rounded-full text-slate-400 hover:bg-slate-100 hover:text-slate-600 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-skillup-blue">
                                            <X className="h-4 w-4" aria-hidden="true" />
                                        </button>
                                    )}
                                </div>
                            </div>
                            {categories.length > 0 && (
                                <div className="flex flex-wrap items-center gap-2" role="group" aria-label="Filter by type">
                                    <Chip active={!filters.category} onClick={() => navigate({ category: undefined, page: undefined })}>All</Chip>
                                    {categories.map((cat) => (
                                        <Chip key={cat.value} active={filters.category === cat.value} onClick={() => navigate({ category: cat.value, page: undefined })}>
                                            {cat.label} <span className="text-current/60">({cat.count})</span>
                                        </Chip>
                                    ))}
                                </div>
                            )}
                        </div>
                    </section>
                )}

                {/* ─── Grid ─────────────────────────────────────────── */}
                <section id="events" className="scroll-mt-24 bg-slate-50 py-16 sm:py-20">
                    <div ref={resultsRef} className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                        <p className="sr-only" role="status" aria-live="polite">
                            {loading ? 'Updating events' : `${events.length} ${events.length === 1 ? 'event' : 'events'}`}
                        </p>

                        {!hasAnything && !loading ? (
                            <EventEmptyState filtered={activeFilters} onClear={clearAll} />
                        ) : events.length === 0 && !loading ? (
                            <EventEmptyState filtered={activeFilters} onClear={clearAll} />
                        ) : (
                            <>
                                <h2 className="mb-8 text-2xl font-bold text-skillup-navy">
                                    {view === 'past' ? 'Past events' : filters.category ? `${categories.find((c) => c.value === filters.category)?.label || ''} events` : 'Upcoming events'}
                                </h2>
                                <div className="grid gap-6 sm:grid-cols-2 lg:grid-cols-3" data-reveal-group>
                                    {events.map((event, i) => (
                                        <EventCard key={event.id} event={event} priority={i < 3} />
                                    ))}
                                </div>
                                {pagination && pagination.lastPage > 1 && (
                                    <Pagination pagination={pagination} onGo={(page) => navigate({ page }, { scrollToResults: true })} />
                                )}
                            </>
                        )}
                    </div>
                </section>

                {/* ─── Newsletter / alerts ──────────────────────────── */}
                <div id="alerts" className="scroll-mt-24">
                    <NewsletterCta
                        eyebrow="Event alerts"
                        heading="Be the first to know about new events"
                        description="Get notified about upcoming webinars, workshops, and masterclasses — straight to your inbox."
                    />
                </div>
            </div>
        </PublicLayout>
    );
}

function Chip({ active, onClick, children }) {
    return (
        <button
            type="button"
            onClick={onClick}
            aria-pressed={active}
            className={cn(
                'inline-flex min-h-9 items-center rounded-full px-4 py-1.5 text-sm font-semibold transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-skillup-blue/40',
                active ? 'bg-skillup-blue text-white' : 'bg-white text-slate-700 ring-1 ring-slate-200 hover:bg-slate-100',
            )}
        >
            {children}
        </button>
    );
}

function ViewTab({ active, onClick, children }) {
    return (
        <button
            type="button"
            role="tab"
            aria-selected={active}
            onClick={onClick}
            className={cn(
                'rounded-full px-4 py-1.5 text-sm font-semibold transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-skillup-blue/40',
                active ? 'bg-white text-skillup-navy shadow-sm' : 'text-slate-600 hover:text-skillup-navy',
            )}
        >
            {children}
        </button>
    );
}

function Pagination({ pagination, onGo }) {
    const { currentPage, lastPage, from, to, total } = pagination;
    const pages = Array.from({ length: lastPage }, (_, i) => i + 1);

    return (
        <nav className="mt-12 flex flex-col items-center gap-4" aria-label="Pagination">
            <p className="text-sm text-slate-600">
                Showing <span className="font-semibold text-skillup-navy">{from}–{to}</span> of{' '}
                <span className="font-semibold text-skillup-navy">{total}</span> events
            </p>
            <div className="flex items-center gap-2">
                <button type="button" onClick={() => onGo(currentPage - 1)} disabled={currentPage <= 1} className={cn(buttonVariants({ variant: 'outline', size: 'sm' }), 'disabled:opacity-40')}>Prev</button>
                {pages.map((page) => (
                    <button key={page} type="button" onClick={() => onGo(page)} aria-current={page === currentPage ? 'page' : undefined} className={cn('inline-flex h-9 min-w-9 items-center justify-center rounded-md px-2 text-sm font-semibold transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-skillup-blue/40', page === currentPage ? 'bg-skillup-blue text-white' : 'bg-white text-slate-700 ring-1 ring-slate-200 hover:bg-slate-100')}>{page}</button>
                ))}
                <button type="button" onClick={() => onGo(currentPage + 1)} disabled={currentPage >= lastPage} className={cn(buttonVariants({ variant: 'outline', size: 'sm' }), 'disabled:opacity-40')}>Next</button>
            </div>
        </nav>
    );
}
