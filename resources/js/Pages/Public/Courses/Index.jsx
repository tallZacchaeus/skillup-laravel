import { lazy, Suspense, useCallback, useEffect, useMemo, useRef, useState } from 'react';
import { Head, router } from '@inertiajs/react';
import { AlertCircle, GraduationCap, RotateCw, Search, SlidersHorizontal, X } from 'lucide-react';
import PublicLayout from '@/Components/public/PublicLayout';
import Breadcrumbs from '@/Components/Breadcrumbs';
import CourseCard from '@/Components/public/courses/CourseCard';
import CourseCardSkeleton from '@/Components/public/courses/CourseCardSkeleton';
import FilterPanel from '@/Components/public/courses/FilterPanel';
import { buttonVariants } from '@/Components/ui/button';
import { cn } from '@/lib/utils';

const FilterDrawer = lazy(() => import('@/Components/public/courses/FilterDrawer'));

const EMPTY_OPTIONS = { categories: [], levels: [], deliveryModes: [], skills: [], priceBuckets: [], programs: [], sorts: [] };

// Which filter keys count toward "active filters" (search is shown separately as a chip).
const CHIP_KEYS = ['search', 'program', 'category', 'level', 'delivery', 'tags', 'price'];

export default function CoursesIndex({ products = [], filters = {}, options = EMPTY_OPTIONS, pagination = null }) {
    const [search, setSearch] = useState(filters.search || '');
    const [drawerOpen, setDrawerOpen] = useState(false);
    const [loading, setLoading] = useState(false);
    const [error, setError] = useState(false);

    const firstRender = useRef(true);
    const searchRef = useRef(null);
    const filtersButtonRef = useRef(null);
    const resultsRef = useRef(null);

    const total = pagination?.total ?? products.length;

    // Keep the search box in sync when the server sends new filter state (chip cleared, back/forward).
    useEffect(() => {
        setSearch(filters.search || '');
    }, [filters.search]);

    const navigate = useCallback(
        (overrides = {}, { scrollToResults = false } = {}) => {
            const next = { ...filters, ...overrides };
            const params = {};
            Object.entries(next).forEach(([key, value]) => {
                if (!value) return;
                if (key === 'sort' && value === 'featured') return;
                params[key] = value;
            });

            router.get('/courses', params, {
                preserveScroll: true,
                preserveState: true,
                only: ['products', 'filters', 'options', 'pagination', 'engine'],
                onStart: () => {
                    setLoading(true);
                    setError(false);
                },
                onFinish: () => setLoading(false),
                onError: () => setError(true),
                onSuccess: () => {
                    if (scrollToResults) {
                        resultsRef.current?.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    }
                },
            });
        },
        [filters],
    );

    // Debounced live search — one request once typing settles, synced to the URL.
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

    // Press "/" to focus search — unless the user is already typing in a field.
    useEffect(() => {
        const onKey = (event) => {
            if (event.key !== '/' || event.metaKey || event.ctrlKey || event.altKey) return;
            const tag = document.activeElement?.tagName;
            const editable = document.activeElement?.isContentEditable;
            if (tag === 'INPUT' || tag === 'TEXTAREA' || tag === 'SELECT' || editable) return;
            event.preventDefault();
            searchRef.current?.focus();
        };
        document.addEventListener('keydown', onKey);
        return () => document.removeEventListener('keydown', onKey);
    }, []);

    const toggle = (key, value) => navigate({ [key]: filters[key] === value ? '' : value, page: undefined });

    const activeChips = useMemo(() => {
        const chips = [];
        const find = (list, v) => list.find((o) => o.value === v);
        if (filters.search) chips.push({ key: 'search', label: `“${filters.search}”` });
        if (filters.program) chips.push({ key: 'program', label: find(options.programs, filters.program)?.label || filters.program });
        if (filters.category) chips.push({ key: 'category', label: find(options.categories, filters.category)?.label || filters.category });
        if (filters.level) chips.push({ key: 'level', label: find(options.levels, filters.level)?.label || filters.level });
        if (filters.delivery) chips.push({ key: 'delivery', label: find(options.deliveryModes, filters.delivery)?.label || filters.delivery });
        if (filters.tags) chips.push({ key: 'tags', label: filters.tags });
        if (filters.price) chips.push({ key: 'price', label: find(options.priceBuckets, filters.price)?.label || filters.price });
        return chips;
    }, [filters, options]);

    const activeCount = useMemo(() => CHIP_KEYS.filter((key) => filters[key]).length, [filters]);

    const clearChip = (key) => {
        if (key === 'search') setSearch('');
        navigate({ [key]: '', page: undefined });
    };

    const clearAll = () => {
        setSearch('');
        router.get('/courses', {}, {
            preserveScroll: true,
            only: ['products', 'filters', 'options', 'pagination', 'engine'],
            onStart: () => setLoading(true),
            onFinish: () => setLoading(false),
        });
    };

    const closeDrawer = () => {
        setDrawerOpen(false);
        // Return focus to the trigger for keyboard users.
        window.setTimeout(() => filtersButtonRef.current?.focus(), 0);
    };

    const countLabel = `${total} ${total === 1 ? 'course' : 'courses'} found`;

    return (
        <PublicLayout>
            <Head title="Courses" />

            {/* ─── Header ──────────────────────────────────────────── */}
            <section className="border-b border-slate-200 bg-skillup-navy pb-12 pt-28 text-white sm:pt-32">
                <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <Breadcrumbs items={[{ label: 'Home', href: '/' }, { label: 'Courses' }]} tone="light" />
                    <h1 className="mt-4 max-w-2xl text-3xl font-bold tracking-tight sm:text-4xl md:text-5xl">
                        Find the right course for your career
                    </h1>
                    <p className="mt-4 max-w-2xl text-base leading-7 text-blue-100 sm:text-lg">
                        Explore practical courses by skill, level, delivery format, and price.
                    </p>

                    <form
                        role="search"
                        onSubmit={(e) => {
                            e.preventDefault();
                            navigate({ search, page: undefined });
                        }}
                        className="relative mt-8 max-w-2xl"
                    >
                        <label htmlFor="course-search" className="sr-only">
                            Search courses
                        </label>
                        <Search className="pointer-events-none absolute left-4 top-1/2 h-5 w-5 -translate-y-1/2 text-slate-500" aria-hidden="true" />
                        <input
                            id="course-search"
                            ref={searchRef}
                            type="search"
                            value={search}
                            onChange={(e) => setSearch(e.target.value)}
                            placeholder="Search by course, skill, or career path..."
                            className="h-14 w-full rounded-full border-0 bg-white pl-12 pr-24 text-base text-slate-900 shadow-lg placeholder:text-slate-500 focus:outline-none focus:ring-2 focus:ring-skillup-blue"
                        />
                        {search && (
                            <button
                                type="button"
                                onClick={() => {
                                    setSearch('');
                                    searchRef.current?.focus();
                                }}
                                aria-label="Clear search"
                                className="absolute right-14 top-1/2 inline-flex h-9 w-9 -translate-y-1/2 items-center justify-center rounded-full text-slate-500 transition-colors hover:bg-slate-100 hover:text-slate-700 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-skillup-blue"
                            >
                                <X className="h-4 w-4" aria-hidden="true" />
                            </button>
                        )}
                        <kbd className="pointer-events-none absolute right-4 top-1/2 hidden -translate-y-1/2 rounded border border-slate-200 bg-slate-50 px-1.5 py-0.5 text-xs font-medium text-slate-400 sm:block">
                            /
                        </kbd>
                    </form>
                </div>
            </section>

            {/* ─── Catalogue ───────────────────────────────────────── */}
            <section className="bg-slate-50 py-8 lg:py-10">
                <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div className="lg:grid lg:grid-cols-[280px_1fr] lg:gap-8">
                        {/* Desktop sidebar */}
                        <aside className="hidden lg:block">
                            <div className="sticky top-24 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                                <div className="flex items-center justify-between border-b border-slate-200 px-5 py-4">
                                    <h2 className="flex items-center gap-2 text-base font-bold text-skillup-navy">
                                        <SlidersHorizontal className="h-4 w-4 text-skillup-blue" aria-hidden="true" />
                                        Filters
                                        {activeCount > 0 && (
                                            <span className="inline-flex h-5 min-w-[20px] items-center justify-center rounded-full bg-skillup-blue px-1.5 text-xs font-bold text-white">
                                                {activeCount}
                                            </span>
                                        )}
                                    </h2>
                                    {activeCount > 0 && (
                                        <button type="button" onClick={clearAll} className="text-xs font-semibold text-skillup-blue hover:underline focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-skillup-blue/40">
                                            Clear all
                                        </button>
                                    )}
                                </div>
                                <div className="px-5">
                                    <FilterPanel options={options} filters={filters} onToggle={toggle} />
                                </div>
                            </div>
                        </aside>

                        {/* Results */}
                        <div ref={resultsRef} className="scroll-mt-24">
                            {/* Toolbar */}
                            <div className="sticky top-[68px] z-20 -mx-4 mb-6 flex items-center justify-between gap-3 border-b border-slate-200 bg-slate-50/95 px-4 py-3 backdrop-blur sm:mx-0 sm:rounded-xl sm:border sm:px-4">
                                <p className="text-sm text-slate-600" aria-live="polite">
                                    <span className="font-semibold text-skillup-navy">{total}</span> {total === 1 ? 'course' : 'courses'}
                                    <span className="hidden sm:inline"> found</span>
                                </p>
                                <div className="flex items-center gap-2">
                                    <button
                                        ref={filtersButtonRef}
                                        type="button"
                                        onClick={() => setDrawerOpen(true)}
                                        className={cn(buttonVariants({ variant: 'secondary', size: 'sm' }), 'lg:hidden')}
                                        aria-haspopup="dialog"
                                    >
                                        <SlidersHorizontal className="h-4 w-4" aria-hidden="true" />
                                        Filters
                                        {activeCount > 0 && (
                                            <span className="inline-flex h-5 min-w-[20px] items-center justify-center rounded-full bg-skillup-blue px-1.5 text-xs font-bold text-white">
                                                {activeCount}
                                            </span>
                                        )}
                                    </button>
                                    <label className="flex items-center gap-2 text-sm text-slate-600">
                                        <span className="hidden sm:inline">Sort by</span>
                                        <span className="sr-only sm:hidden">Sort by</span>
                                        <select
                                            value={filters.sort || 'featured'}
                                            onChange={(e) => navigate({ sort: e.target.value, page: undefined })}
                                            className="rounded-md border-slate-300 py-1.5 pl-3 pr-8 text-sm font-medium text-skillup-navy focus:border-skillup-blue focus:ring-skillup-blue"
                                        >
                                            {options.sorts.map((s) => (
                                                <option key={s.value} value={s.value}>{s.label}</option>
                                            ))}
                                        </select>
                                    </label>
                                </div>
                            </div>

                            {/* Active chips */}
                            {activeChips.length > 0 && (
                                <div className="mb-6 flex flex-wrap items-center gap-2">
                                    {activeChips.map((chip) => (
                                        <button
                                            key={chip.key}
                                            type="button"
                                            onClick={() => clearChip(chip.key)}
                                            className="inline-flex min-h-9 items-center gap-1.5 rounded-full bg-skillup-blue/10 px-3 py-1 text-xs font-semibold text-skillup-blue transition-colors hover:bg-skillup-blue/20 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-skillup-blue/40"
                                        >
                                            {chip.label}
                                            <X className="h-3.5 w-3.5" aria-hidden="true" />
                                            <span className="sr-only">Remove filter</span>
                                        </button>
                                    ))}
                                    <button
                                        type="button"
                                        onClick={clearAll}
                                        className="min-h-9 rounded-full px-3 py-1 text-xs font-semibold text-slate-500 hover:text-slate-700 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-skillup-blue/40"
                                    >
                                        Clear all
                                    </button>
                                </div>
                            )}

                            {/* Screen-reader result announcement */}
                            <p className="sr-only" role="status" aria-live="polite">
                                {loading ? 'Updating results' : countLabel}
                            </p>

                            {/* States */}
                            {error ? (
                                <ErrorState onRetry={() => navigate({})} />
                            ) : loading ? (
                                <div className="grid gap-6 sm:grid-cols-2 xl:grid-cols-3" aria-busy="true">
                                    {Array.from({ length: pagination?.perPage || 9 }).map((_, i) => (
                                        <CourseCardSkeleton key={i} />
                                    ))}
                                </div>
                            ) : products.length === 0 ? (
                                <EmptyState onClear={clearAll} />
                            ) : (
                                <div className="grid gap-6 sm:grid-cols-2 xl:grid-cols-3">
                                    {products.map((product, index) => (
                                        <CourseCard
                                            key={`${product.trackSlug}-${product.slug}`}
                                            product={product}
                                            priority={index < 3}
                                        />
                                    ))}
                                </div>
                            )}

                            {!loading && !error && pagination && pagination.lastPage > 1 && (
                                <Pagination pagination={pagination} onGo={(page) => navigate({ page }, { scrollToResults: true })} />
                            )}
                        </div>
                    </div>
                </div>
            </section>

            {drawerOpen && (
                <Suspense fallback={null}>
                    <FilterDrawer
                        options={options}
                        filters={filters}
                        onToggle={toggle}
                        onClearAll={clearAll}
                        onClose={closeDrawer}
                        total={total}
                        activeCount={activeCount}
                    />
                </Suspense>
            )}
        </PublicLayout>
    );
}

function EmptyState({ onClear }) {
    return (
        <div className="rounded-2xl border border-dashed border-slate-300 bg-white p-12 text-center">
            <GraduationCap className="mx-auto h-10 w-10 text-slate-300" aria-hidden="true" />
            <h3 className="mt-4 text-lg font-semibold text-skillup-navy">No courses match your filters</h3>
            <p className="mt-2 text-sm text-slate-500">Try removing a filter or searching for another skill.</p>
            <div className="mt-6 flex flex-wrap items-center justify-center gap-3">
                <button type="button" onClick={onClear} className={cn(buttonVariants({ variant: 'default', size: 'sm' }))}>
                    Clear filters
                </button>
                <a href="/courses" className={cn(buttonVariants({ variant: 'outline', size: 'sm' }))}>
                    Browse all courses
                </a>
            </div>
        </div>
    );
}

function ErrorState({ onRetry }) {
    return (
        <div className="rounded-2xl border border-dashed border-red-200 bg-white p-12 text-center">
            <AlertCircle className="mx-auto h-10 w-10 text-red-300" aria-hidden="true" />
            <h3 className="mt-4 text-lg font-semibold text-skillup-navy">We could not load the courses</h3>
            <p className="mt-2 text-sm text-slate-500">Please check your connection and try again.</p>
            <button type="button" onClick={onRetry} className={cn(buttonVariants({ variant: 'default', size: 'sm' }), 'mt-6')}>
                <RotateCw className="h-4 w-4" aria-hidden="true" />
                Retry
            </button>
        </div>
    );
}

function Pagination({ pagination, onGo }) {
    const { currentPage, lastPage, from, to, total } = pagination;
    const pages = Array.from({ length: lastPage }, (_, i) => i + 1);

    return (
        <nav className="mt-10 flex flex-col items-center gap-4" aria-label="Pagination">
            <p className="text-sm text-slate-600">
                Showing <span className="font-semibold text-skillup-navy">{from}–{to}</span> of{' '}
                <span className="font-semibold text-skillup-navy">{total}</span> courses
            </p>
            <div className="flex items-center gap-2">
                <button
                    type="button"
                    onClick={() => onGo(currentPage - 1)}
                    disabled={currentPage <= 1}
                    className={cn(buttonVariants({ variant: 'outline', size: 'sm' }), 'disabled:opacity-40')}
                >
                    Prev
                </button>
                {pages.map((page) => (
                    <button
                        key={page}
                        type="button"
                        onClick={() => onGo(page)}
                        aria-current={page === currentPage ? 'page' : undefined}
                        className={cn(
                            'inline-flex h-9 min-w-9 items-center justify-center rounded-md px-2 text-sm font-semibold transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-skillup-blue/40',
                            page === currentPage ? 'bg-skillup-blue text-white' : 'bg-white text-slate-700 ring-1 ring-slate-200 hover:bg-slate-100',
                        )}
                    >
                        {page}
                    </button>
                ))}
                <button
                    type="button"
                    onClick={() => onGo(currentPage + 1)}
                    disabled={currentPage >= lastPage}
                    className={cn(buttonVariants({ variant: 'outline', size: 'sm' }), 'disabled:opacity-40')}
                >
                    Next
                </button>
            </div>
        </nav>
    );
}
