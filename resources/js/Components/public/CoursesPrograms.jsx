import { useState } from 'react';
import Img from '@/Components/Img';
import { Link } from '@inertiajs/react';
import { ArrowRight, BadgeCheck, Bookmark, Clock, GraduationCap, Monitor } from 'lucide-react';
import { programs as staticPrograms, tracks as staticTracks } from '@/data/site';
import { cn } from '@/lib/utils';

// Fallback link map so the section still renders before anything is seeded.
const STATIC_PROGRAM_LINKS = {
    'SkillUp Plus': '/courses',
    'Tech Trybe Bootcamp': '/community',
};

export default function CoursesPrograms({ programCourses = [], courses = [] }) {
    const [activeTab, setActiveTab] = useState('programs');

    const hasProgramCourses = programCourses.length > 0;
    const hasCourses = courses.length > 0;

    return (
        <section className="bg-blue-50 py-20">
            <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div className="mb-12 flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between" data-reveal>
                    <div>
                        <h2 className="text-2xl font-bold leading-[120%] text-skillup-navy md:text-3xl lg:text-[40px]">
                            {activeTab === 'courses' ? 'Courses We Offer.' : 'Explore Our Programs.'}
                        </h2>
                        <p className="mt-3 max-w-xl text-base text-skillup-muted">
                            {activeTab === 'courses'
                                ? 'Practical, job-ready tracks you can enroll in today.'
                                : 'Cohort-based flagship experiences — including our seasonal Summer AI bootcamp for kids and teens.'}
                        </p>
                    </div>

                    <div
                        className="inline-flex w-full max-w-[320px] items-center gap-1 rounded-full bg-skillup-light p-1.5 lg:mx-0"
                        role="tablist"
                        aria-label="Courses or programs"
                    >
                        <TabButton active={activeTab === 'programs'} onClick={() => setActiveTab('programs')}>
                            Programs
                        </TabButton>
                        <TabButton active={activeTab === 'courses'} onClick={() => setActiveTab('courses')}>
                            Courses
                        </TabButton>
                    </div>
                </div>

                {activeTab === 'courses' ? (
                    <div className="grid gap-8 sm:grid-cols-2 lg:grid-cols-3" data-reveal-group>
                        {hasCourses
                            ? courses.map((course) => <CatalogCard key={course.slug} item={course} />)
                            : staticTracks.slice(0, 6).map((track) => <StaticCard key={track.slug} item={track} href={`/courses/${track.slug}`} cta="Enroll now" />)}
                    </div>
                ) : (
                    <div className="grid gap-8 sm:grid-cols-2 lg:grid-cols-3" data-reveal-group>
                        {hasProgramCourses
                            ? programCourses.map((course) => <CatalogCard key={course.slug} item={course} program />)
                            : staticPrograms.map((program) => (
                                <StaticCard
                                    key={program.title}
                                    item={{ ...program, price: program.duration }}
                                    href={STATIC_PROGRAM_LINKS[program.title] || '/programs'}
                                    cta="Learn more"
                                />
                            ))}
                    </div>
                )}

                <div className="mt-12 text-center" data-reveal>
                    <Link
                        href={activeTab === 'courses' ? '/courses' : '/programs'}
                        className="inline-flex items-center gap-2 rounded-md border-2 border-skillup-blue bg-white px-6 py-3 text-sm font-semibold text-skillup-navy transition-all hover:bg-skillup-blue hover:text-white"
                    >
                        {activeTab === 'courses' ? 'Browse all courses' : 'View all programs'}
                        <ArrowRight className="h-4 w-4" aria-hidden="true" />
                    </Link>
                </div>
            </div>
        </section>
    );
}

function TabButton({ active, onClick, children }) {
    return (
        <button
            type="button"
            role="tab"
            aria-selected={active}
            onClick={onClick}
            className={cn(
                'flex-1 rounded-full px-4 py-2.5 text-center text-sm font-semibold transition-all duration-300 ease-premium sm:text-base',
                active ? 'bg-blue-800 text-white shadow-sm' : 'text-blue-900/70 hover:text-blue-900',
            )}
        >
            {children}
        </button>
    );
}

/**
 * Unified course/program card. Price sits on its own row and the CTA is a
 * full-width button at the foot of the card (clear hierarchy, easy tap target).
 */
function CatalogCard({ item, program = false }) {
    const eyebrow = program ? item.program?.name : item.trackTitle;

    return (
        <article className="group flex flex-col overflow-hidden rounded-2xl bg-white shadow-card ring-1 ring-slate-100 transition-all duration-300 hover:-translate-y-1 hover:shadow-card-hover">
            <div className="relative overflow-hidden">
                <Img
                    src={item.image}
                    alt={item.title}
                    className="h-48 w-full object-cover transition-transform duration-500 group-hover:scale-105"
                    loading="lazy"
                />
                {program && (
                    <span className="absolute left-3 top-3 inline-flex items-center gap-1.5 rounded-full bg-skillup-navy px-3 py-1 text-xs font-semibold text-white shadow">
                        <GraduationCap className="h-3.5 w-3.5" aria-hidden="true" />
                        Program
                    </span>
                )}
            </div>
            <div className="flex flex-1 flex-col p-6">
                {eyebrow && <p className="text-xs font-semibold uppercase tracking-wide text-skillup-blue">{eyebrow}</p>}
                <h3 className="mt-1 text-xl font-bold text-skillup-ink">{item.title}</h3>
                <p className="mt-2 flex-1 text-sm leading-6 text-skillup-muted">{item.summary}</p>

                <div className="mt-4 flex flex-wrap items-center gap-2">
                    {item.level && <MetaPill icon={Bookmark}>{item.level}</MetaPill>}
                    {item.duration && item.duration !== 'TBA' && <MetaPill icon={Clock}>{item.duration}</MetaPill>}
                    <MetaPill icon={Monitor}>Online</MetaPill>
                    <MetaPill icon={BadgeCheck}>Certificate</MetaPill>
                </div>

                <div className="mt-5 flex items-baseline justify-between">
                    <span className="text-lg font-bold text-blue-900">{item.price}</span>
                    {item.rating?.count > 0 && (
                        <span className="text-xs font-medium text-slate-500">
                            ★ {item.rating.average} ({item.rating.count})
                        </span>
                    )}
                </div>

                <Link
                    href={item.url}
                    className={cn(
                        'mt-4 inline-flex h-11 w-full items-center justify-center gap-2 rounded-md text-sm font-semibold transition-all',
                        program
                            ? 'bg-skillup-blue text-white hover:bg-blue-700'
                            : 'border-2 border-skillup-blue bg-white text-skillup-navy hover:bg-skillup-blue hover:text-white',
                    )}
                >
                    {item.cta || (program ? 'Register' : 'View course')}
                    <ArrowRight className="h-4 w-4" aria-hidden="true" />
                </Link>
            </div>
        </article>
    );
}

function StaticCard({ item, href, cta }) {
    return (
        <article className="group flex flex-col overflow-hidden rounded-2xl bg-white shadow-card ring-1 ring-slate-100 transition-all duration-300 hover:-translate-y-1 hover:shadow-card-hover">
            <div className="overflow-hidden">
                <Img src={item.image} alt={item.title} className="h-48 w-full object-cover transition-transform duration-500 group-hover:scale-105" loading="lazy" />
            </div>
            <div className="flex flex-1 flex-col p-6">
                <h3 className="text-xl font-bold text-skillup-ink">{item.title}</h3>
                <p className="mb-4 mt-2 flex-1 text-sm leading-6 text-skillup-muted">{item.summary || item.description}</p>
                {item.level && (
                    <div className="mb-4 flex flex-wrap items-center gap-2">
                        <MetaPill icon={Bookmark}>{item.level}</MetaPill>
                    </div>
                )}
                <div className="mt-auto flex items-baseline justify-between">
                    <span className="text-lg font-bold text-blue-900">{item.price}</span>
                </div>
                <Link
                    href={href}
                    className="mt-4 inline-flex h-11 w-full items-center justify-center gap-2 rounded-md border-2 border-skillup-blue bg-white text-sm font-semibold text-skillup-navy transition-all hover:bg-skillup-blue hover:text-white"
                >
                    {cta}
                    <ArrowRight className="h-4 w-4" aria-hidden="true" />
                </Link>
            </div>
        </article>
    );
}

function MetaPill({ icon: Icon, children }) {
    return (
        <span className="flex min-h-9 items-center gap-2 rounded-full bg-slate-50 px-3 py-1.5 text-xs font-medium text-zinc-600">
            <Icon className="h-3.5 w-3.5 flex-shrink-0" aria-hidden="true" />
            {children}
        </span>
    );
}
