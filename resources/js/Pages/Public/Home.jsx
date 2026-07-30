import { useState } from 'react';
import { Head, Link, router } from '@inertiajs/react';
import {
    ArrowRight,
    Award,
    Briefcase,
    Building2,
    Globe,
    GraduationCap,
    Layers,
    LifeBuoy,
    Rocket,
    Search,
    Users,
} from 'lucide-react';
import PublicLayout from '@/Components/public/PublicLayout';
import Img from '@/Components/Img';
import NewsletterCta from '@/Components/public/NewsletterCta';
import CoursesPrograms from '@/Components/public/CoursesPrograms';
import FaqAccordion from '@/Components/public/FaqAccordion';
import TestimonialsMarquee from '@/Components/public/TestimonialsMarquee';
import SectionHeading from '@/Components/public/SectionHeading';
import StatCounter from '@/Components/public/StatCounter';
import { useHeroIntro, useParallax, useRevealScope, useWordRotate } from '@/lib/animations';
import { faqs as staticFaqs, partnerLogos as staticPartners } from '@/data/site';
import { heroStats, howItWorks, popularSearches, statsBand } from '@/data/homeContent';

const heroWords = ['Career Path', 'Employability', 'Talent'];

const stepIcons = { search: Search, graduationCap: GraduationCap, briefcase: Briefcase };
const statIcons = { users: Users, layers: Layers, building2: Building2, award: Award };

const whyChoose = [
    {
        icon: Rocket,
        title: 'Industry-Relevant Training',
        text: 'Learn from experts with hands-on experience in top industries, gaining the skills global and African employers actually want.',
    },
    {
        icon: LifeBuoy,
        title: 'Career-Focused Support',
        text: 'From CV makeovers to interview prep, we provide personalised support to help you secure roles in tech locally and globally.',
    },
    {
        icon: Globe,
        title: 'Pan-African Network',
        text: 'Join a vibrant community of learners, mentors, and employers across Africa, creating opportunities to grow and make an impact.',
    },
];

const setsUsApart = [
    {
        image: '/images/Facilitators.jpg',
        alt: 'Expert facilitators collaborating at a desk',
        title: 'Expert Facilitators',
        text: "Our trainers aren't just teachers — they're industry veterans who've built products, led teams, and solved real business challenges across Africa and beyond. They bring hands-on knowledge, mentorship, and insider insights.",
    },
    {
        image: '/images/abj.png',
        alt: 'The SkillUp learning community',
        title: 'Vast Online Community',
        text: "Learning doesn't stop at the classroom. Join a pan-African network of thousands of learners, alumni, and employers collaborating, sharing opportunities, and building solutions for the continent.",
    },
    {
        image: '/images/whygood.jpg',
        alt: 'Focused learning environment',
        title: 'Focused Learning Tracks',
        text: 'From software development to data analytics, product design, project management, and AI — our tracks are designed to take you from beginner to hire-ready with the tools to compete on a global stage.',
    },
];

const staticPosts = [
    {
        id: 'static-1',
        title: 'How to Stay Consistent with Learning (Even on Busy Days)',
        summary:
            'Life gets busy but your learning goals don’t have to suffer. Discover practical methods for balancing work, study, and personal life while thriving in your tech training journey.',
        featured_image: '/images/consistent.jpg',
        category: 'Learning Tips',
        date: 'Jul 12, 2026',
        readMinutes: 5,
        author: 'SkillUp Team',
        href: '/blog',
    },
    {
        id: 'static-2',
        title: 'How to Choose the Right Course for Your Career Goals',
        summary:
            'Picking the right course shouldn’t feel like guesswork. Learn how to align your studies with your career ambitions and choose programs that open doors in Africa’s growing tech industry.',
        featured_image: '/images/right_course.jpg',
        category: 'Career',
        date: 'Jun 28, 2026',
        readMinutes: 6,
        author: 'SkillUp Team',
        href: '/blog',
    },
    {
        id: 'static-3',
        title: 'Mastering Remote Work as a Tech Professional in Africa',
        summary:
            'Remote work is now the norm in tech. Learn how African tech talent can build productive routines, collaborate across time zones, and thrive in global teams.',
        featured_image: '/images/remote.jpg',
        category: 'Remote Work',
        date: 'Jun 15, 2026',
        readMinutes: 7,
        author: 'SkillUp Team',
        href: '/blog',
    },
    {
        id: 'static-4',
        title: 'From Beginner to Pro: Your Roadmap to Learning Tech in Africa',
        summary:
            'Breaking into tech can feel overwhelming — but it doesn’t have to be. A step-by-step guide to choosing a career path, building your portfolio, and landing your first job.',
        featured_image: '/images/beginners.jpg',
        category: 'Roadmap',
        date: 'May 30, 2026',
        readMinutes: 8,
        author: 'SkillUp Team',
        href: '/blog',
    },
];

export default function Home({ faqs = [], testimonials = [], partners = [], recentPosts = [], programs = [], programCourses = [], featuredCourses = [] }) {
    const scope = useRevealScope();
    const heroScope = useHeroIntro();
    const heroBg = useParallax({ amount: 6 });
    const wordRef = useWordRotate(heroWords);

    const [searchQuery, setSearchQuery] = useState('');

    const activeFaqs = faqs.length > 0 ? faqs : staticFaqs;
    const activePartners = partners.length > 0
        ? partners.map((p) => ({ src: p.logo_path.startsWith('/') ? p.logo_path : `/storage/${p.logo_path}`, alt: p.name }))
        : staticPartners;
    const uniquePartners = Array.from(new Map(activePartners.map((p) => [p.alt, p])).values());
    const activePosts = recentPosts.length > 0 ? recentPosts : staticPosts;

    const handleSearch = (e) => {
        e.preventDefault();
        router.get('/courses', searchQuery ? { search: searchQuery } : {});
    };

    return (
        <PublicLayout>
            <Head title="SkillUp Edtech - Learning Simplified">
                <link rel="preload" as="image" href="/images/hero.webp" type="image/webp" />
            </Head>

            <div ref={scope}>
                {/* ─── Hero ─────────────────────────────────────────────── */}
                <section
                    ref={heroScope}
                    className="relative flex min-h-svh items-center justify-center overflow-hidden bg-skillup-navy"
                >
                    <div ref={heroBg} className="absolute inset-0 scale-125">
                        <Img src="/images/hero.jpg" alt="" className="h-full w-full object-cover" eager />
                    </div>
                    <div className="hero-scrim absolute inset-0" aria-hidden="true" />

                    <div className="relative z-10 mx-auto w-full max-w-4xl px-4 pb-16 pt-28 text-center">
                        <h1
                            data-hero
                            className="mx-auto mb-6 max-w-headline text-4xl font-bold leading-[1.1] tracking-tight text-white sm:text-5xl md:text-6xl"
                        >
                            Up your skills to advance your{' '}
                            <span className="relative inline-block whitespace-nowrap text-blue-300">
                                <span ref={wordRef} className="inline-block">
                                    {heroWords[0]}
                                </span>
                                <span
                                    className="absolute -bottom-1 left-0 right-0 h-1 rounded-full bg-blue-400 motion-safe:animate-pulse"
                                    aria-hidden="true"
                                />
                            </span>
                        </h1>

                        <p data-hero className="mx-auto mb-8 max-w-xl text-base leading-8 text-blue-50/90 sm:mb-10 sm:text-lg md:text-xl">
                            Practical, mentor-led tech training that takes you from beginner to job-ready — built for
                            Africa&apos;s digital future.
                        </p>

                        <form data-hero onSubmit={handleSearch} className="relative mx-auto max-w-xl" role="search">
                            <label htmlFor="hero-search" className="sr-only">
                                What do you want to learn?
                            </label>
                            <Search className="pointer-events-none absolute left-5 top-1/2 h-5 w-5 -translate-y-1/2 text-slate-400" aria-hidden="true" />
                            <input
                                id="hero-search"
                                type="search"
                                value={searchQuery}
                                onChange={(e) => setSearchQuery(e.target.value)}
                                placeholder="Search courses, skills or career paths..."
                                className="h-14 w-full rounded-full border-0 bg-white/95 pl-12 pr-16 text-base text-slate-900 shadow-elevated backdrop-blur-sm placeholder:text-slate-500 focus:outline-none focus:ring-2 focus:ring-skillup-blue sm:text-lg"
                            />
                            <button
                                type="submit"
                                aria-label="Search courses"
                                className="absolute right-2 top-1/2 flex h-10 w-10 -translate-y-1/2 items-center justify-center rounded-full bg-skillup-blue text-white shadow-lg transition-transform hover:scale-105 hover:bg-blue-700 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-white sm:h-11 sm:w-11"
                            >
                                <Search className="h-5 w-5" aria-hidden="true" />
                            </button>
                        </form>

                        <div data-hero className="mt-5 flex flex-wrap items-center justify-center gap-2 text-sm">
                            <span className="text-blue-100/80">Popular:</span>
                            {popularSearches.map((term) => (
                                <button
                                    key={term}
                                    type="button"
                                    onClick={() => router.get('/courses', { search: term })}
                                    className="inline-flex min-h-9 items-center rounded-full border border-white/25 bg-white/10 px-3 py-1 font-medium text-white backdrop-blur-sm transition-colors hover:bg-white/20 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-white/60"
                                >
                                    {term}
                                </button>
                            ))}
                        </div>

                        <dl data-hero className="mx-auto mt-12 grid max-w-2xl grid-cols-2 gap-x-8 gap-y-6 border-t border-white/15 pt-8 sm:grid-cols-4">
                            {heroStats.map((stat) => (
                                <div key={stat.key} className="text-center">
                                    <dt className="text-2xl font-bold text-white sm:text-3xl">{stat.value}</dt>
                                    <dd className="mt-1 text-xs font-medium uppercase tracking-wide text-blue-100/80">{stat.label}</dd>
                                </div>
                            ))}
                        </dl>
                    </div>
                </section>

                {/* ─── Partner logos ────────────────────────────────────── */}
                <section className="border-b border-slate-100 bg-white py-14 sm:py-16" aria-label="Partner organizations">
                    <div className="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
                        <p data-reveal className="mb-10 text-center text-sm font-semibold uppercase tracking-[0.14em] text-slate-500">
                            Trusted by organizations across Africa
                        </p>
                        <div data-reveal-group className="grid grid-cols-2 items-center gap-x-8 gap-y-10 sm:grid-cols-4">
                            {uniquePartners.map((logo) => (
                                <div key={logo.alt} className="flex items-center justify-center">
                                    <Img
                                        src={logo.src}
                                        alt={logo.alt}
                                        className="logo-muted h-10 w-auto max-w-[150px] object-contain sm:h-12"
                                        loading="lazy"
                                    />
                                </div>
                            ))}
                        </div>
                    </div>
                </section>

                {/* ─── How it works ─────────────────────────────────────── */}
                <section className="bg-skillup-soft py-16 sm:py-20">
                    <div className="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
                        <SectionHeading
                            data-reveal
                            eyebrow="How it works"
                            title="Your path from curious to hired"
                            description="Three simple steps take you from choosing a track to launching a career in tech."
                        />
                        <div data-reveal-group className="mt-14 grid gap-6 md:grid-cols-3">
                            {howItWorks.map((step) => {
                                const Icon = stepIcons[step.icon];
                                return (
                                    <div
                                        key={step.step}
                                        className="relative overflow-hidden rounded-2xl border border-slate-200 bg-white p-8 shadow-card transition-all duration-300 hover:-translate-y-1 hover:shadow-card-hover"
                                    >
                                        <span className="absolute right-6 top-4 text-6xl font-bold text-slate-100" aria-hidden="true">
                                            {step.step}
                                        </span>
                                        <div className="relative flex h-14 w-14 items-center justify-center rounded-2xl bg-skillup-blue/10 text-skillup-blue">
                                            <Icon className="h-7 w-7" aria-hidden="true" />
                                        </div>
                                        <h3 className="relative mt-6 text-xl font-bold text-skillup-navy">{step.title}</h3>
                                        <p className="relative mt-3 leading-7 text-slate-600">{step.text}</p>
                                    </div>
                                );
                            })}
                        </div>
                    </div>
                </section>

                {/* ─── Why choose SkillUp ───────────────────────────────── */}
                <section className="bg-white py-16 sm:py-20 md:py-24">
                    <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                        <div className="grid items-center gap-12 lg:grid-cols-2 lg:gap-16">
                            <div className="relative order-2 lg:order-1" data-reveal>
                                <div className="relative mx-auto h-72 w-72 sm:h-80 sm:w-80 md:h-96 md:w-96 lg:h-[460px] lg:w-[460px]">
                                    <div className="relative mx-auto h-full w-full overflow-hidden rounded-full border border-skillup-blue">
                                        <div className="absolute left-4 top-4 h-full w-full rounded-full bg-skillup-blue sm:left-5 sm:top-5 md:left-6 md:top-6" />
                                        <Img
                                            src="/images/skill_up.png"
                                            alt="SkillUp learner giving a thumbs up"
                                            className="absolute left-12 top-16 h-56 w-48 rounded-full object-cover sm:left-16 sm:top-20 sm:h-64 sm:w-56 md:left-20 md:top-24 md:h-72 md:w-64 lg:left-[60px] lg:top-[76px] lg:h-[384px] lg:w-[340px]"
                                            loading="lazy"
                                        />
                                    </div>
                                    <div className="absolute left-2 top-2 hidden h-8 w-8 rounded-full bg-skillup-blue sm:block md:h-10 md:w-10" aria-hidden="true" />
                                    <div className="absolute right-2 top-6 hidden h-8 w-8 rounded-full bg-skillup-blue sm:block md:right-4 md:h-10 md:w-10" aria-hidden="true" />
                                    <div className="absolute bottom-12 left-6 hidden h-8 w-8 rounded-full bg-skillup-blue sm:block md:h-10 md:w-10" aria-hidden="true" />
                                </div>
                            </div>

                            <div className="order-1 lg:order-2">
                                <SectionHeading
                                    data-reveal
                                    align="left"
                                    eyebrow="Why SkillUp"
                                    title="Why Choose SkillUp Edtech?"
                                    description="Our programs help you gain in-demand digital skills, connect with real job opportunities, and thrive in the global digital economy."
                                />
                                <div className="mt-8 space-y-4" data-reveal-group>
                                    {whyChoose.map((feature) => (
                                        <FeatureCard key={feature.title} {...feature} />
                                    ))}
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                {/* ─── Courses & Programs ───────────────────────────────── */}
                <CoursesPrograms programs={programs} programCourses={programCourses} courses={featuredCourses} />

                {/* ─── What sets us apart (alternating rows) ────────────── */}
                <section className="bg-white py-16 sm:py-20 md:py-24">
                    <div className="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
                        <SectionHeading
                            data-reveal
                            eyebrow="What sets us apart"
                            title="A learning experience built for outcomes"
                            description="At SkillUp Edtech, we are redefining how Africans learn, work, and thrive in the digital age."
                        />
                        <div className="mt-16 space-y-16 md:space-y-24">
                            {setsUsApart.map((row, index) => (
                                <AltFeatureRow key={row.title} {...row} reversed={index % 2 === 1} />
                            ))}
                        </div>
                    </div>
                </section>

                {/* ─── Statistics band ──────────────────────────────────── */}
                <section className="surface-brand py-16 sm:py-20">
                    <div className="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
                        <SectionHeading
                            data-reveal
                            eyebrow="Our impact"
                            title="Real momentum for African tech talent"
                            description="Numbers that reflect a growing community of learners, mentors, and hiring partners."
                        />
                        <div data-reveal-group className="mt-14 grid grid-cols-2 gap-8 md:grid-cols-4">
                            {statsBand.map((stat) => (
                                <StatCounter
                                    key={stat.key}
                                    value={stat.value}
                                    suffix={stat.suffix}
                                    label={stat.label}
                                    icon={statIcons[stat.icon]}
                                />
                            ))}
                        </div>
                    </div>
                </section>

                {/* ─── Testimonials ─────────────────────────────────────── */}
                <TestimonialsMarquee testimonials={testimonials} />

                {/* ─── FAQ ──────────────────────────────────────────────── */}
                <section className="bg-white py-16 sm:py-20">
                    <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                        <SectionHeading
                            data-reveal
                            eyebrow="FAQ"
                            title="Frequently asked questions"
                            description="Quick answers about our digital skills bootcamp, tech training, and how we empower tech talent in Africa."
                        />
                        <div className="mx-auto mt-12 max-w-3xl" data-reveal>
                            <FaqAccordion items={activeFaqs} />
                        </div>
                    </div>
                </section>

                {/* ─── Blog ─────────────────────────────────────────────── */}
                <section className="bg-skillup-soft py-16 sm:py-20">
                    <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                        <div className="mb-12 flex flex-col justify-between gap-6 md:flex-row md:items-end" data-reveal>
                            <SectionHeading
                                align="left"
                                eyebrow="From the blog"
                                title="Read. Learn. Grow."
                                description="Expert tips, inspiring stories, and practical strategies to help you succeed in the fast-paced world of technology."
                            />
                            <Link
                                href="/blog"
                                className="inline-flex h-11 flex-shrink-0 items-center justify-center gap-2 rounded-md bg-skillup-blue px-5 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-blue-700"
                            >
                                Read more blogs
                                <ArrowRight className="h-4 w-4" aria-hidden="true" />
                            </Link>
                        </div>

                        <div className="grid items-stretch gap-8 md:grid-cols-2" data-reveal-group>
                            {activePosts.slice(0, 4).map((post) => (
                                <BlogCard key={post.id} post={post} />
                            ))}
                        </div>
                    </div>
                </section>

                {/* ─── Newsletter ───────────────────────────────────────── */}
                <NewsletterCta />
            </div>
        </PublicLayout>
    );
}

function FeatureCard({ icon: Icon, title, text }) {
    return (
        <div className="group rounded-2xl border border-slate-200 bg-white p-5 shadow-card transition-all duration-300 hover:-translate-y-1 hover:border-skillup-blue/40 hover:shadow-card-hover">
            <div className="flex items-start gap-4">
                <div className="flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-xl bg-skillup-blue/10 text-skillup-blue transition-transform duration-300 group-hover:scale-110 group-hover:rotate-3">
                    <Icon className="h-6 w-6" aria-hidden="true" />
                </div>
                <div>
                    <h3 className="text-lg font-semibold text-skillup-navy">{title}</h3>
                    <p className="mt-1.5 text-sm leading-6 text-slate-600">{text}</p>
                </div>
            </div>
        </div>
    );
}

function AltFeatureRow({ image, alt, title, text, reversed }) {
    return (
        <div className="grid items-center gap-8 md:grid-cols-2 md:gap-12 lg:gap-16" data-reveal>
            <div className={reversed ? 'md:order-2' : ''}>
                <div className="group overflow-hidden rounded-2xl shadow-card">
                    <Img
                        src={image}
                        alt={alt}
                        className="aspect-[4/3] w-full object-cover transition-transform duration-500 group-hover:scale-105"
                        loading="lazy"
                    />
                </div>
            </div>
            <div className={reversed ? 'md:order-1' : ''}>
                <h3 className="text-2xl font-bold text-skillup-navy sm:text-3xl">{title}</h3>
                <p className="mt-4 text-base leading-8 text-slate-600">{text}</p>
            </div>
        </div>
    );
}

function estimateRead(summary) {
    const words = (summary || '').trim().split(/\s+/).filter(Boolean).length;
    return Math.max(3, Math.round(words / 40) + 3);
}

function BlogCard({ post }) {
    const href = post.slug ? `/blog/${post.slug}` : post.href || '/blog';
    const category = post.category?.name || post.category || 'Insights';
    const date = post.date || (post.published_at ? new Date(post.published_at).toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' }) : null);
    const readMinutes = post.readMinutes || post.read_minutes || estimateRead(post.summary);
    const author = post.author?.name || post.author || 'SkillUp Team';

    return (
        <article className="group flex h-full flex-col overflow-hidden rounded-2xl bg-white shadow-card ring-1 ring-slate-100 transition-all duration-300 hover:-translate-y-1 hover:shadow-card-hover">
            <div className="relative overflow-hidden">
                <Img
                    src={post.featured_image || '/images/consistent.jpg'}
                    alt=""
                    className="h-56 w-full object-cover transition-transform duration-500 group-hover:scale-105"
                    loading="lazy"
                />
                <span className="absolute left-4 top-4 rounded-full bg-white/90 px-3 py-1 text-xs font-semibold text-skillup-navy shadow-sm backdrop-blur-sm">
                    {category}
                </span>
            </div>
            <div className="flex flex-1 flex-col p-6">
                <div className="flex items-center gap-2 text-xs text-slate-500">
                    {date && (
                        <>
                            <span>{date}</span>
                            <span aria-hidden="true">·</span>
                        </>
                    )}
                    <span>{readMinutes} min read</span>
                </div>
                <h3 className="mt-3 text-xl font-semibold text-skillup-navy">
                    <Link href={href} className="transition-colors hover:text-skillup-blue">
                        {post.title}
                    </Link>
                </h3>
                <p className="mt-3 flex-1 text-sm leading-6 text-slate-600">{post.summary}</p>
                <div className="mt-5 flex items-center justify-between border-t border-slate-100 pt-4">
                    <span className="text-xs font-medium text-slate-500">By {author}</span>
                    <Link href={href} className="inline-flex items-center gap-1 text-sm font-semibold text-skillup-blue">
                        Read
                        <ArrowRight className="h-4 w-4 transition-transform group-hover:translate-x-0.5" aria-hidden="true" />
                    </Link>
                </div>
            </div>
        </article>
    );
}
