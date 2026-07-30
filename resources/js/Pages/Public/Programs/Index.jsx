import { useEffect, useMemo, useRef, useState } from 'react';
import { Head, Link } from '@inertiajs/react';
import { ArrowRight, Bell, BookOpen, Compass, FileText, MessagesSquare, Rocket, SearchX, Sparkles, Users } from 'lucide-react';
import PublicLayout from '@/Components/public/PublicLayout';
import Breadcrumbs from '@/Components/Breadcrumbs';
import StructuredData from '@/Components/StructuredData';
import Img from '@/Components/Img';
import FaqAccordion from '@/Components/public/FaqAccordion';
import NewsletterCta from '@/Components/public/NewsletterCta';
import ProgramCard from '@/Components/public/programs/ProgramCard';
import FeaturedProgram from '@/Components/public/programs/FeaturedProgram';
import ProgramFilters from '@/Components/public/programs/ProgramFilters';
import { useRevealScope } from '@/lib/animations';

const WHY_JOIN = [
    { icon: Rocket, title: 'Hands-on projects', text: 'Learn by building real things you can show off, not just by watching lessons.' },
    { icon: Users, title: 'Guidance from mentors', text: 'Facilitators and mentors support each cohort step by step.' },
    { icon: MessagesSquare, title: 'Community & networking', text: 'Learn alongside peers and grow a network that lasts beyond the programme.' },
    { icon: Sparkles, title: 'Portfolio-ready work', text: 'Finish with tangible work that strengthens your CV or university applications.' },
];

const RELATED = [
    { icon: BookOpen, title: 'Courses', text: 'Self-paced tracks across in-demand tech skills.', href: '/courses' },
    { icon: FileText, title: 'Blog', text: 'Guides, stories, and tips from the SkillUp team.', href: '/blog' },
    { icon: Compass, title: 'Resources', text: 'Free templates, guides, and toolkits to keep learning.', href: '/resources' },
    { icon: MessagesSquare, title: 'Community', text: 'Connect with fellow learners and mentors.', href: '/community' },
];

const FAQS = [
    { question: 'Who is eligible to join a programme?', answer: 'Each programme lists its age range and any requirements on its own page. Check the track details before registering to find the right fit.' },
    { question: 'How do I register?', answer: 'Choose a programme, complete the short registration form, confirm your email, and secure your seat with a payment. You can finish the remaining details afterwards.' },
    { question: 'Are scholarships available?', answer: 'Scholarship availability varies by programme. When a programme offers one, the details and how to apply appear on that programme’s page.' },
    { question: 'Will my child receive a certificate?', answer: 'Most programmes award a certificate of participation on completion. Each programme page confirms whether a certificate is included.' },
    { question: 'What format do programmes run in?', answer: 'Programmes may run in person, online, or hybrid. Every programme page shows its dates, schedule, and delivery mode so you know what to expect.' },
];

const readParam = (key, fallback = '') => {
    if (typeof window === 'undefined') return fallback;
    return new URLSearchParams(window.location.search).get(key) ?? fallback;
};

export default function Index({ programs = [], featuredSlug = null, statusFilters = [], tagFilters = [] }) {
    const scope = useRevealScope();
    const [query, setQuery] = useState(() => readParam('q'));
    const [active, setActive] = useState(() => readParam('filter', 'all'));
    const debounceRef = useRef(null);

    const chips = useMemo(
        () => [{ key: 'all', label: 'All' }, ...statusFilters, ...tagFilters],
        [statusFilters, tagFilters],
    );

    const featured = useMemo(
        () => (featuredSlug ? programs.find((p) => p.slug === featuredSlug) : null),
        [programs, featuredSlug],
    );

    // Debounced URL sync — keeps the address bar shareable without thrashing history.
    useEffect(() => {
        if (typeof window === 'undefined') return undefined;
        clearTimeout(debounceRef.current);
        debounceRef.current = setTimeout(() => {
            const params = new URLSearchParams(window.location.search);
            query.trim() ? params.set('q', query.trim()) : params.delete('q');
            active && active !== 'all' ? params.set('filter', active) : params.delete('filter');
            const qs = params.toString();
            window.history.replaceState({}, '', qs ? `${window.location.pathname}?${qs}` : window.location.pathname);
        }, 300);
        return () => clearTimeout(debounceRef.current);
    }, [query, active]);

    const filtered = useMemo(() => {
        const q = query.trim().toLowerCase();
        return programs.filter((p) => {
            const matchesChip =
                active === 'all' ||
                p.statusKey === active ||
                (p.tags ?? []).some((t) => t.toLowerCase().replace(/\s+/g, '-') === active);
            if (!matchesChip) return false;
            if (!q) return true;
            const haystack = [p.name, p.tagline, p.description, ...(p.tags ?? [])].filter(Boolean).join(' ').toLowerCase();
            return haystack.includes(q);
        });
    }, [programs, query, active]);

    const clearAll = () => {
        setQuery('');
        setActive('all');
    };

    const canonical = typeof window !== 'undefined' ? window.location.origin + window.location.pathname : 'https://skillup.com/programs';
    const origin = typeof window !== 'undefined' ? window.location.origin : 'https://skillup.com';
    const description = 'Explore SkillUp programmes — seasonal bootcamps, AI camps, youth academies, and hands-on learning experiences for the next generation of African innovators.';

    const schema = [
        {
            '@context': 'https://schema.org',
            '@type': 'CollectionPage',
            name: 'SkillUp Programs',
            description,
            url: canonical,
            mainEntity: {
                '@type': 'ItemList',
                itemListElement: programs.map((p, i) => ({
                    '@type': 'ListItem',
                    position: i + 1,
                    name: p.name,
                    url: `${origin}/programs/${p.slug}`,
                })),
            },
        },
        {
            '@context': 'https://schema.org',
            '@type': 'BreadcrumbList',
            itemListElement: [
                { '@type': 'ListItem', position: 1, name: 'Home', item: `${origin}/` },
                { '@type': 'ListItem', position: 2, name: 'Programs', item: `${origin}/programs` },
            ],
        },
    ];

    return (
        <PublicLayout>
            <Head title="SkillUp Programs — Bootcamps, AI Camps & Youth Academies">
                <meta head-key="description" name="description" content={description} />
                <link head-key="canonical" rel="canonical" href={canonical} />
                <meta head-key="og:type" property="og:type" content="website" />
                <meta head-key="og:title" property="og:title" content="SkillUp Programs" />
                <meta head-key="og:description" property="og:description" content={description} />
                <meta head-key="twitter:card" name="twitter:card" content="summary_large_image" />
                <meta head-key="twitter:title" name="twitter:title" content="SkillUp Programs" />
                <meta head-key="twitter:description" name="twitter:description" content={description} />
            </Head>

            <StructuredData data={schema} />

            <div ref={scope}>
                {/* Hero */}
                <section className="relative overflow-hidden bg-skillup-navy pt-[72px]">
                    {featured?.heroImagePath && (
                        <Img src={featured.heroImagePath} alt="" className="absolute inset-0 h-full w-full object-cover opacity-20" eager />
                    )}
                    <div className="relative z-10 mx-auto max-w-7xl px-4 py-20 sm:px-6 sm:py-24 lg:px-8">
                        <Breadcrumbs items={[{ label: 'Home', href: '/' }, { label: 'Programs' }]} tone="light" />
                        <span data-reveal className="mt-6 inline-block text-xs font-bold uppercase tracking-[0.14em] text-blue-300">
                            SkillUp programmes
                        </span>
                        <h1 data-reveal className="mt-3 max-w-3xl text-4xl font-bold leading-[1.1] tracking-tight text-white sm:text-5xl lg:text-6xl">
                            Limited-time learning experiences that build real skills
                        </h1>
                        <p data-reveal className="mt-5 max-w-2xl text-lg leading-8 text-blue-100">
                            Seasonal bootcamps, AI camps, youth academies, and innovation challenges — hands-on, mentor-led, and
                            designed for the next generation of African innovators.
                        </p>
                        <div data-reveal className="mt-8 flex flex-col gap-3 sm:flex-row sm:items-center">
                            <a
                                href="#all-programs"
                                className="inline-flex h-12 items-center justify-center gap-2 rounded-md bg-white px-6 text-base font-semibold text-skillup-navy shadow-sm transition-colors hover:bg-blue-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-white focus-visible:ring-offset-2 focus-visible:ring-offset-skillup-navy"
                            >
                                <Compass className="h-5 w-5" aria-hidden="true" />
                                Explore programs
                            </a>
                            <a
                                href="#alerts"
                                className="inline-flex h-12 items-center justify-center gap-2 rounded-md border border-white/30 px-6 text-base font-semibold text-white transition-colors hover:bg-white/10 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-white focus-visible:ring-offset-2 focus-visible:ring-offset-skillup-navy"
                            >
                                <Bell className="h-5 w-5" aria-hidden="true" />
                                Get program alerts
                            </a>
                        </div>
                    </div>
                </section>

                {/* Featured programme (hidden when none) */}
                <FeaturedProgram program={featured} />

                {/* Search + filters + grid */}
                <section id="all-programs" className="scroll-mt-24 bg-skillup-soft py-16 sm:py-20">
                    <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                        <div className="mb-8" data-reveal>
                            <h2 className="text-3xl font-bold tracking-tight text-skillup-navy sm:text-4xl">All programmes</h2>
                            <p className="mt-2 text-base text-gray-600">Search and filter to find the right experience.</p>
                        </div>

                        <div className="mb-10" data-reveal>
                            <ProgramFilters
                                query={query}
                                onQuery={setQuery}
                                chips={chips}
                                active={active}
                                onChip={(key) => setActive((cur) => (cur === key ? 'all' : key))}
                                resultCount={filtered.length}
                            />
                        </div>

                        {filtered.length > 0 ? (
                            <div className="grid gap-6 sm:grid-cols-2 lg:grid-cols-3" data-reveal>
                                {filtered.map((program) => (
                                    <ProgramCard key={program.slug} program={program} />
                                ))}
                            </div>
                        ) : (
                            <div className="rounded-2xl border border-dashed border-slate-300 bg-white p-12 text-center" data-reveal>
                                <SearchX className="mx-auto h-10 w-10 text-slate-400" aria-hidden="true" />
                                <h3 className="mt-4 text-lg font-bold text-skillup-navy">No programmes match your search</h3>
                                <p className="mx-auto mt-2 max-w-md text-sm leading-6 text-gray-600">
                                    Try a different keyword or clear your filters — and subscribe below so you never miss a new programme.
                                </p>
                                <button
                                    type="button"
                                    onClick={clearAll}
                                    className="mt-6 inline-flex h-11 items-center justify-center rounded-md bg-skillup-blue px-5 text-sm font-semibold text-white transition-colors hover:bg-blue-700 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-skillup-blue focus-visible:ring-offset-2"
                                >
                                    Clear search & filters
                                </button>
                            </div>
                        )}
                    </div>
                </section>

                {/* Why join */}
                <section className="bg-white py-16 sm:py-20" aria-labelledby="why-join">
                    <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                        <div className="mb-10 text-center" data-reveal>
                            <span className="text-xs font-bold uppercase tracking-[0.14em] text-skillup-blue">Why join</span>
                            <h2 id="why-join" className="mt-3 text-3xl font-bold tracking-tight text-skillup-navy sm:text-4xl">What makes SkillUp programmes different</h2>
                        </div>
                        <div className="grid gap-6 sm:grid-cols-2 lg:grid-cols-4" data-reveal-group>
                            {WHY_JOIN.map((item) => (
                                <div key={item.title} className="rounded-2xl border border-slate-200 bg-skillup-soft p-6 transition-all duration-300 hover:-translate-y-1 hover:shadow-card">
                                    <span className="mb-4 flex h-12 w-12 items-center justify-center rounded-xl bg-skillup-blue/10 text-skillup-blue">
                                        <item.icon className="h-6 w-6" aria-hidden="true" />
                                    </span>
                                    <h3 className="text-lg font-semibold text-skillup-navy">{item.title}</h3>
                                    <p className="mt-2 text-sm leading-6 text-gray-600">{item.text}</p>
                                </div>
                            ))}
                        </div>
                    </div>
                </section>

                {/* FAQ */}
                <section className="bg-skillup-soft py-16 sm:py-20" aria-labelledby="faq">
                    <div className="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
                        <div className="mb-10 text-center" data-reveal>
                            <span className="text-xs font-bold uppercase tracking-[0.14em] text-skillup-blue">FAQ</span>
                            <h2 id="faq" className="mt-3 text-3xl font-bold tracking-tight text-skillup-navy sm:text-4xl">Programme questions, answered</h2>
                        </div>
                        <div data-reveal>
                            <FaqAccordion items={FAQS} />
                        </div>
                    </div>
                </section>

                {/* Related content */}
                <section className="bg-white py-16 sm:py-20" aria-labelledby="related">
                    <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                        <div className="mb-10" data-reveal>
                            <h2 id="related" className="text-3xl font-bold tracking-tight text-skillup-navy sm:text-4xl">Keep exploring SkillUp</h2>
                        </div>
                        <div className="grid gap-6 sm:grid-cols-2 lg:grid-cols-4" data-reveal-group>
                            {RELATED.map((item) => (
                                <Link
                                    key={item.href}
                                    href={item.href}
                                    className="group flex h-full flex-col rounded-2xl border border-slate-200 bg-white p-6 shadow-card transition-all duration-300 hover:-translate-y-1 hover:border-skillup-blue/40 hover:shadow-card-hover focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-skillup-blue/40"
                                >
                                    <span className="mb-4 flex h-11 w-11 items-center justify-center rounded-xl bg-skillup-blue/10 text-skillup-blue">
                                        <item.icon className="h-5 w-5" aria-hidden="true" />
                                    </span>
                                    <h3 className="font-semibold text-skillup-navy group-hover:text-skillup-blue">{item.title}</h3>
                                    <p className="mt-1 flex-1 text-sm leading-6 text-gray-600">{item.text}</p>
                                    <span className="mt-4 inline-flex items-center gap-1 text-sm font-semibold text-skillup-blue">
                                        Visit
                                        <ArrowRight className="h-4 w-4 transition-transform duration-300 group-hover:translate-x-1" aria-hidden="true" />
                                    </span>
                                </Link>
                            ))}
                        </div>
                    </div>
                </section>

                {/* Newsletter — Never miss a programme */}
                <div id="alerts" className="scroll-mt-24">
                    <NewsletterCta
                        eyebrow="Programme alerts"
                        heading="Never miss a programme"
                        description="Be the first to hear when registration opens for a new bootcamp, AI camp, or youth academy. No spam — just programme announcements."
                    />
                </div>
            </div>
        </PublicLayout>
    );
}
