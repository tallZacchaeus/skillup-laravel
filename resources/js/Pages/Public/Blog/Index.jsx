import { useCallback, useEffect, useRef, useState } from 'react';
import { Head, Link, router } from '@inertiajs/react';
import { ArrowRight, Calendar, Clock, Search, X } from 'lucide-react';
import PublicLayout from '@/Components/public/PublicLayout';
import Img from '@/Components/Img';
import Breadcrumbs from '@/Components/Breadcrumbs';
import NewsletterCta from '@/Components/public/NewsletterCta';
import BlogPostCard from '@/Components/public/blog/BlogPostCard';
import BlogCardSkeleton from '@/Components/public/blog/BlogCardSkeleton';
import BlogEmptyState from '@/Components/public/blog/BlogEmptyState';
import { buttonVariants } from '@/Components/ui/button';
import { useRevealScope } from '@/lib/animations';
import { cn } from '@/lib/utils';

const EMPTY_SEO = { title: 'Blog & Insights', description: '', canonical: '', ogImage: '' };

export default function BlogIndex({
    posts = [],
    featuredPost = null,
    categories = [],
    filters = {},
    pagination = null,
    featuredCourses = [],
    seo = EMPTY_SEO,
}) {
    const scope = useRevealScope();
    const [search, setSearch] = useState(filters.search || '');
    const [loading, setLoading] = useState(false);
    const firstRender = useRef(true);
    const searchRef = useRef(null);
    const resultsRef = useRef(null);

    useEffect(() => {
        setSearch(filters.search || '');
    }, [filters.search]);

    const navigate = useCallback(
        (overrides = {}, { scrollToResults = false } = {}) => {
            const next = { ...filters, ...overrides };
            const params = {};
            Object.entries(next).forEach(([key, value]) => {
                if (value) params[key] = value;
            });
            router.get('/blog', params, {
                preserveScroll: true,
                preserveState: true,
                only: ['posts', 'featuredPost', 'categories', 'filters', 'pagination', 'seo'],
                onStart: () => setLoading(true),
                onFinish: () => setLoading(false),
                onSuccess: () => {
                    if (scrollToResults) resultsRef.current?.scrollIntoView({ behavior: 'smooth', block: 'start' });
                },
            });
        },
        [filters],
    );

    // Debounced live search, synced to the URL.
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
    const hasAnything = posts.length > 0 || featuredPost;
    const showToolbar = categories.length > 0 || activeFilters;
    const total = pagination?.total ?? posts.length;

    const clearSearch = () => {
        setSearch('');
        navigate({ search: undefined, page: undefined });
    };

    const clearAll = () => {
        setSearch('');
        router.get('/blog', {}, { preserveScroll: true, only: ['posts', 'featuredPost', 'categories', 'filters', 'pagination', 'seo'] });
    };

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
                {seo.ogImage && <meta head-key="twitter:image" name="twitter:image" content={seo.ogImage} />}
            </Head>

            <div ref={scope}>
                {/* ─── Hero ─────────────────────────────────────────── */}
                <section className="bg-skillup-navy pb-16 pt-28 text-white sm:pt-32">
                    <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                        <Breadcrumbs items={[{ label: 'Home', href: '/' }, { label: 'Blog' }]} tone="light" />
                        <span data-hero className="mt-5 inline-block text-xs font-bold uppercase tracking-[0.14em] text-blue-300">
                            SkillUp Blog
                        </span>
                        <h1 data-hero className="mt-3 max-w-3xl text-4xl font-bold leading-[1.1] tracking-tight sm:text-5xl">
                            Insights to help you learn, grow, and thrive in tech
                        </h1>
                        <p data-hero className="mt-5 max-w-2xl text-lg leading-8 text-blue-100">
                            Practical guides, career stories, and industry insights from the SkillUp team — written for
                            Africa’s next generation of tech talent.
                        </p>
                        <div data-hero className="mt-8 flex flex-col gap-3 sm:flex-row">
                            <a href="#articles" className={cn(buttonVariants({ size: 'lg' }), 'bg-white text-skillup-navy hover:bg-blue-50')}>
                                Browse articles
                            </a>
                            <Link href="/courses" className={cn(buttonVariants({ variant: 'secondary', size: 'lg' }), 'bg-white/10 text-white ring-1 ring-inset ring-white/30 hover:bg-white/20')}>
                                Explore courses
                            </Link>
                        </div>
                    </div>
                </section>

                {/* ─── Search + categories ──────────────────────────── */}
                {showToolbar && (
                    <section className="sticky top-[68px] z-20 border-b border-slate-200 bg-white/95 py-4 backdrop-blur">
                        <div className="mx-auto flex max-w-7xl flex-col gap-4 px-4 sm:px-6 lg:flex-row lg:items-center lg:justify-between lg:px-8">
                            <div className="relative w-full lg:max-w-sm">
                                <label htmlFor="blog-search" className="sr-only">Search articles</label>
                                <Search className="pointer-events-none absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" aria-hidden="true" />
                                <input
                                    id="blog-search"
                                    ref={searchRef}
                                    type="search"
                                    value={search}
                                    onChange={(e) => setSearch(e.target.value)}
                                    placeholder="Search articles..."
                                    className="h-11 w-full rounded-full border border-slate-300 bg-white pl-10 pr-10 text-sm text-slate-900 shadow-sm placeholder:text-slate-500 focus:border-skillup-blue focus:outline-none focus:ring-2 focus:ring-skillup-blue/20"
                                />
                                {search && (
                                    <button
                                        type="button"
                                        onClick={clearSearch}
                                        aria-label="Clear search"
                                        className="absolute right-2 top-1/2 inline-flex h-7 w-7 -translate-y-1/2 items-center justify-center rounded-full text-slate-400 hover:bg-slate-100 hover:text-slate-600 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-skillup-blue"
                                    >
                                        <X className="h-4 w-4" aria-hidden="true" />
                                    </button>
                                )}
                            </div>

                            <div className="flex flex-wrap items-center gap-2" role="group" aria-label="Filter by category">
                                <Chip active={!filters.category} onClick={() => navigate({ category: undefined, page: undefined })}>
                                    All
                                </Chip>
                                {categories.map((cat) => (
                                    <Chip key={cat.slug} active={filters.category === cat.slug} onClick={() => navigate({ category: cat.slug, page: undefined })}>
                                        {cat.name} <span className="text-current/60">({cat.count})</span>
                                    </Chip>
                                ))}
                            </div>
                        </div>
                    </section>
                )}

                {/* ─── Featured ─────────────────────────────────────── */}
                {featuredPost && (
                    <section className="bg-white py-12 sm:py-16" data-reveal>
                        <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                            <div className="grid gap-8 lg:grid-cols-12 lg:items-center">
                                <Link href={featuredPost.url} className="group lg:col-span-7">
                                    <div className="overflow-hidden rounded-2xl">
                                        <Img
                                            src={featuredPost.image}
                                            alt={featuredPost.title}
                                            className="aspect-[16/9] w-full object-cover shadow-md transition-transform duration-500 group-hover:scale-105"
                                            eager
                                        />
                                    </div>
                                </Link>
                                <div className="lg:col-span-5">
                                    <span className="inline-flex items-center rounded-full bg-skillup-blue/10 px-3 py-1 text-xs font-semibold text-skillup-blue">
                                        Featured{featuredPost.category ? ` · ${featuredPost.category.name}` : ''}
                                    </span>
                                    <h2 className="mt-4 text-3xl font-bold tracking-tight text-skillup-navy sm:text-4xl">
                                        <Link href={featuredPost.url} className="transition-colors hover:text-skillup-blue">{featuredPost.title}</Link>
                                    </h2>
                                    <div className="mt-3 flex items-center gap-3 text-sm text-slate-500">
                                        {featuredPost.dateLabel && (
                                            <span className="inline-flex items-center gap-1.5">
                                                <Calendar className="h-4 w-4" aria-hidden="true" />
                                                {featuredPost.dateLabel}
                                            </span>
                                        )}
                                        <span className="inline-flex items-center gap-1.5">
                                            <Clock className="h-4 w-4" aria-hidden="true" />
                                            {featuredPost.readingMinutes} min read
                                        </span>
                                    </div>
                                    <p className="mt-4 text-base leading-7 text-slate-600">{featuredPost.summary}</p>
                                    <Link href={featuredPost.url} className={cn(buttonVariants({ variant: 'default' }), 'mt-6')}>
                                        Read article
                                        <ArrowRight className="h-4 w-4" aria-hidden="true" />
                                    </Link>
                                </div>
                            </div>
                        </div>
                    </section>
                )}

                {/* ─── Articles ─────────────────────────────────────── */}
                <section id="articles" className="scroll-mt-24 bg-slate-50 py-16 sm:py-20">
                    <div ref={resultsRef} className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                        <p className="sr-only" role="status" aria-live="polite">
                            {loading ? 'Updating articles' : `${total} ${total === 1 ? 'article' : 'articles'}`}
                        </p>

                        {!hasAnything && !loading ? (
                            <BlogEmptyState filtered={activeFilters} onClear={clearAll} />
                        ) : (
                            <div className="lg:grid lg:grid-cols-[1fr_300px] lg:gap-10">
                                <div>
                                    <h2 className="mb-8 text-2xl font-bold text-skillup-navy">
                                        {filters.category
                                            ? categories.find((c) => c.slug === filters.category)?.name || 'Articles'
                                            : filters.search
                                                ? 'Search results'
                                                : 'Latest articles'}
                                    </h2>

                                    {loading ? (
                                        <div className="grid gap-8 sm:grid-cols-2" aria-busy="true">
                                            {Array.from({ length: 4 }).map((_, i) => <BlogCardSkeleton key={i} />)}
                                        </div>
                                    ) : posts.length === 0 ? (
                                        <BlogEmptyState filtered={activeFilters} onClear={clearAll} />
                                    ) : (
                                        <div className="grid gap-8 sm:grid-cols-2">
                                            {posts.map((post, i) => (
                                                <BlogPostCard key={post.id} post={post} priority={i < 2} />
                                            ))}
                                        </div>
                                    )}

                                    {!loading && pagination && pagination.lastPage > 1 && (
                                        <Pagination pagination={pagination} onGo={(page) => navigate({ page }, { scrollToResults: true })} />
                                    )}
                                </div>

                                {/* Sidebar */}
                                <aside className="mt-12 lg:mt-0">
                                    {categories.length > 0 && (
                                        <SidebarCard title="Categories">
                                            <ul className="space-y-1">
                                                {categories.map((cat) => (
                                                    <li key={cat.slug}>
                                                        <button
                                                            type="button"
                                                            onClick={() => navigate({ category: cat.slug, page: undefined })}
                                                            className="flex w-full items-center justify-between rounded-lg px-3 py-2 text-left text-sm text-slate-700 transition-colors hover:bg-slate-100 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-skillup-blue/40"
                                                        >
                                                            <span>{cat.name}</span>
                                                            <span className="text-xs font-medium text-slate-400">{cat.count}</span>
                                                        </button>
                                                    </li>
                                                ))}
                                            </ul>
                                        </SidebarCard>
                                    )}

                                    {featuredCourses.length > 0 && (
                                        <SidebarCard title="Featured courses" className="mt-6">
                                            <ul className="space-y-4">
                                                {featuredCourses.map((course) => (
                                                    <li key={course.url}>
                                                        <Link href={course.url} className="group flex gap-3">
                                                            <Img src={course.image} alt="" className="h-14 w-14 flex-shrink-0 rounded-lg object-cover" loading="lazy" />
                                                            <span className="min-w-0">
                                                                <span className="block truncate text-sm font-semibold text-skillup-navy group-hover:text-skillup-blue">{course.title}</span>
                                                                {course.price && <span className="block text-xs text-slate-500">{course.price}</span>}
                                                            </span>
                                                        </Link>
                                                    </li>
                                                ))}
                                            </ul>
                                            <Link href="/courses" className="mt-4 inline-flex items-center gap-1 text-sm font-semibold text-skillup-blue hover:underline">
                                                Browse all courses
                                                <ArrowRight className="h-4 w-4" aria-hidden="true" />
                                            </Link>
                                        </SidebarCard>
                                    )}
                                </aside>
                            </div>
                        )}
                    </div>
                </section>

                {/* ─── Newsletter ───────────────────────────────────── */}
                <NewsletterCta
                    heading="Get weekly tech insights"
                    description="Get practical guides and career tips delivered to your inbox. No spam, just useful insights for your tech journey."
                />
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

function SidebarCard({ title, children, className }) {
    return (
        <div className={cn('rounded-2xl border border-slate-200 bg-white p-5 shadow-card', className)}>
            <h3 className="mb-3 text-xs font-bold uppercase tracking-wide text-slate-500">{title}</h3>
            {children}
        </div>
    );
}

function Pagination({ pagination, onGo }) {
    const { currentPage, lastPage, from, to, total } = pagination;
    const pages = Array.from({ length: lastPage }, (_, i) => i + 1);

    return (
        <nav className="mt-12 flex flex-col items-center gap-4" aria-label="Pagination">
            <p className="text-sm text-slate-600">
                Showing <span className="font-semibold text-skillup-navy">{from}–{to}</span> of{' '}
                <span className="font-semibold text-skillup-navy">{total}</span> articles
            </p>
            <div className="flex items-center gap-2">
                <button type="button" onClick={() => onGo(currentPage - 1)} disabled={currentPage <= 1} className={cn(buttonVariants({ variant: 'outline', size: 'sm' }), 'disabled:opacity-40')}>
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
                <button type="button" onClick={() => onGo(currentPage + 1)} disabled={currentPage >= lastPage} className={cn(buttonVariants({ variant: 'outline', size: 'sm' }), 'disabled:opacity-40')}>
                    Next
                </button>
            </div>
        </nav>
    );
}
