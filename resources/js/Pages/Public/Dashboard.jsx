import { Head, Link, usePage } from '@inertiajs/react';
import {
    ArrowRight, Award, BadgeCheck, BookOpen, CalendarClock, CheckCircle2, GraduationCap,
    Heart, MessagesSquare, ShieldCheck, Sparkles,
} from 'lucide-react';
import DashboardLayout from '@/Layouts/DashboardLayout';
import DashboardHero from '@/Components/dashboard/DashboardHero';
import MetricCard from '@/Components/dashboard/MetricCard';
import DashboardCourseCard from '@/Components/dashboard/DashboardCourseCard';
import NotificationWidget from '@/Components/dashboard/NotificationWidget';
import CourseCard from '@/Components/public/courses/CourseCard';
import EventCard from '@/Components/public/events/EventCard';
import Img from '@/Components/Img';
import { useRevealScope } from '@/lib/animations';

export default function Dashboard({
    courses = [],
    metrics = {},
    certificates = [],
    events = [],
    recommendations = [],
    featuredProgram = null,
    notifications = [],
    unreadNotifications = 0,
}) {
    const scope = useRevealScope();
    const user = usePage().props.auth?.user;
    const firstName = (user?.name || 'there').split(' ')[0];

    const primaryCourse = courses.find((c) => c.accessible) ?? null;
    const hasCourses = courses.length > 0;

    // Only real, supported metrics become cards.
    const metricCards = [
        { key: 'active', icon: BookOpen, value: metrics.activeCourses ?? 0, label: 'Active courses', accent: 'blue', show: true },
        { key: 'completed', icon: CheckCircle2, value: metrics.completedCourses ?? 0, label: 'Completed', accent: 'green', show: (metrics.completedCourses ?? 0) > 0 },
        { key: 'wishlist', icon: Heart, value: metrics.wishlist ?? 0, label: 'Wishlist', href: '/wishlist', accent: 'amber', show: true },
        { key: 'certs', icon: Award, value: metrics.certificates ?? 0, label: 'Certificates', href: '#certificates', accent: 'purple', show: (metrics.certificates ?? 0) > 0 },
        { key: 'events', icon: CalendarClock, value: metrics.upcomingEvents ?? 0, label: 'Upcoming events', accent: 'blue', show: (metrics.upcomingEvents ?? 0) > 0 },
    ].filter((m) => m.show);

    return (
        <DashboardLayout notificationsCount={unreadNotifications}>
            <Head title="My learning — SkillUp" />

            <div ref={scope}>
                <DashboardHero name={firstName} primaryCourse={primaryCourse} progress={null} />

                <div className="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
                    {/* Metrics */}
                    <div className="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4" data-reveal-group>
                        {metricCards.map((m) => (
                            <MetricCard key={m.key} icon={m.icon} value={m.value} label={m.label} href={m.href} accent={m.accent} />
                        ))}
                    </div>

                    <div className="mt-10 grid gap-8 lg:grid-cols-[1.9fr_1fr]">
                        {/* Main column */}
                        <div className="space-y-10">
                            {/* My courses */}
                            <section id="my-courses" className="scroll-mt-24" aria-labelledby="my-courses-heading">
                                <div className="mb-5 flex items-center justify-between" data-reveal>
                                    <h2 id="my-courses-heading" className="text-2xl font-bold text-skillup-navy">My courses</h2>
                                    {hasCourses && <Link href="/courses" className="text-sm font-semibold text-skillup-blue hover:underline">Find more →</Link>}
                                </div>

                                {hasCourses ? (
                                    <div className="grid gap-6 sm:grid-cols-2" data-reveal>
                                        {courses.map((course) => <DashboardCourseCard key={course.id} course={course} />)}
                                    </div>
                                ) : (
                                    <EmptyCourses recommendations={recommendations} />
                                )}
                            </section>

                            {/* Upcoming events (reuses EventCard) — hidden when none */}
                            {events.length > 0 && (
                                <section aria-labelledby="upcoming-heading">
                                    <div className="mb-5 flex items-center justify-between" data-reveal>
                                        <h2 id="upcoming-heading" className="text-2xl font-bold text-skillup-navy">Upcoming</h2>
                                        <Link href="/events" className="text-sm font-semibold text-skillup-blue hover:underline">All events →</Link>
                                    </div>
                                    <div className="grid gap-6 sm:grid-cols-2" data-reveal>
                                        {events.map((event) => <EventCard key={event.id} event={event} />)}
                                    </div>
                                </section>
                            )}

                            {/* Certificates — hidden when none */}
                            {certificates.length > 0 && (
                                <section id="certificates" className="scroll-mt-24" aria-labelledby="certs-heading">
                                    <div className="mb-5 flex items-center gap-2" data-reveal>
                                        <BadgeCheck className="h-6 w-6 text-skillup-blue" aria-hidden="true" />
                                        <h2 id="certs-heading" className="text-2xl font-bold text-skillup-navy">Your certificates</h2>
                                    </div>
                                    <div className="grid gap-4 sm:grid-cols-2" data-reveal>
                                        {certificates.map((cert) => (
                                            <div key={cert.serial} className="flex items-center justify-between gap-4 rounded-2xl border border-slate-200 bg-white p-5 shadow-card">
                                                <div className="min-w-0">
                                                    <p className="truncate font-semibold text-skillup-navy">{cert.programName}</p>
                                                    <p className="mt-0.5 text-xs text-slate-500">Serial {cert.serial}{cert.issuedOn ? ` · ${cert.issuedOn}` : ''}</p>
                                                    <a href={cert.verifyUrl} className="mt-1 inline-block text-xs font-semibold text-skillup-blue hover:underline">Verify</a>
                                                </div>
                                                <a href={cert.showUrl} target="_blank" rel="noreferrer" className="inline-flex h-10 flex-shrink-0 items-center gap-1.5 rounded-md bg-skillup-blue px-4 text-sm font-semibold text-white hover:bg-blue-700">
                                                    View
                                                </a>
                                            </div>
                                        ))}
                                    </div>
                                </section>
                            )}

                            {/* Recommendations (reuses CourseCard) — shown alongside courses when the learner has some */}
                            {hasCourses && recommendations.length > 0 && (
                                <section aria-labelledby="recs-heading">
                                    <div className="mb-5 flex items-center gap-2" data-reveal>
                                        <Sparkles className="h-6 w-6 text-skillup-blue" aria-hidden="true" />
                                        <h2 id="recs-heading" className="text-2xl font-bold text-skillup-navy">Recommended for you</h2>
                                    </div>
                                    <div className="grid gap-6 sm:grid-cols-2 xl:grid-cols-3" data-reveal>
                                        {recommendations.slice(0, 3).map((product) => <CourseCard key={product.id} product={product} />)}
                                    </div>
                                </section>
                            )}
                        </div>

                        {/* Sidebar */}
                        <aside className="space-y-6">
                            <div data-reveal>
                                <NotificationWidget notifications={notifications} />
                            </div>

                            {featuredProgram && (
                                <section className="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-card" data-reveal aria-labelledby="program-heading">
                                    <div className="relative h-28 bg-skillup-navy">
                                        {featuredProgram.heroImagePath && (
                                            <Img src={featuredProgram.heroImagePath} alt="" className="h-full w-full object-cover opacity-70" loading="lazy" />
                                        )}
                                        <span className="absolute left-4 top-4 inline-flex items-center gap-1.5 rounded-full bg-white/90 px-3 py-1 text-xs font-bold text-skillup-navy">
                                            <GraduationCap className="h-3.5 w-3.5" aria-hidden="true" /> Programme
                                        </span>
                                    </div>
                                    <div className="p-5">
                                        <h2 id="program-heading" className="font-bold text-skillup-navy">{featuredProgram.name}</h2>
                                        {featuredProgram.tagline && <p className="mt-1 line-clamp-2 text-sm leading-6 text-slate-600">{featuredProgram.tagline}</p>}
                                        {featuredProgram.startsOn && <p className="mt-2 text-xs text-slate-500">Starts {featuredProgram.startsOn}</p>}
                                        <Link href={`/programs/${featuredProgram.slug}`} className="mt-4 inline-flex items-center gap-1.5 text-sm font-semibold text-skillup-blue hover:underline">
                                            Learn more <ArrowRight className="h-4 w-4" aria-hidden="true" />
                                        </Link>
                                    </div>
                                </section>
                            )}

                            {/* Community prompt — real destination, not fabricated activity */}
                            <section className="rounded-2xl border border-slate-200 bg-skillup-soft p-6" data-reveal aria-labelledby="community-heading">
                                <MessagesSquare className="h-7 w-7 text-skillup-blue" aria-hidden="true" />
                                <h2 id="community-heading" className="mt-3 font-bold text-skillup-navy">Join the conversation</h2>
                                <p className="mt-1 text-sm leading-6 text-slate-600">Ask questions, share your work, and learn alongside peers and mentors.</p>
                                <Link href="/community" className="mt-4 inline-flex items-center gap-1.5 text-sm font-semibold text-skillup-blue hover:underline">
                                    Explore the community <ArrowRight className="h-4 w-4" aria-hidden="true" />
                                </Link>
                            </section>

                            <div className="flex items-start gap-3 rounded-2xl border border-slate-200 bg-white p-5" data-reveal>
                                <ShieldCheck className="h-6 w-6 flex-shrink-0 text-emerald-500" aria-hidden="true" />
                                <p className="text-sm leading-6 text-slate-600">Need a hand? Visit the <Link href="/contact" className="font-semibold text-skillup-blue hover:underline">help centre</Link> — we’re here to support your learning.</p>
                            </div>
                        </aside>
                    </div>
                </div>
            </div>
        </DashboardLayout>
    );
}

function EmptyCourses({ recommendations }) {
    return (
        <div data-reveal>
            <div className="rounded-2xl border border-dashed border-slate-300 bg-white p-10 text-center">
                <span className="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-skillup-blue/10 text-skillup-blue">
                    <GraduationCap className="h-8 w-8" aria-hidden="true" />
                </span>
                <h3 className="mt-4 text-xl font-bold text-skillup-navy">Your learning starts here</h3>
                <p className="mx-auto mt-2 max-w-md text-sm leading-6 text-slate-600">You’re not enrolled in any courses yet. Browse the catalogue and enrol in one that fits your goals.</p>
                <Link href="/courses" className="mt-6 inline-flex h-11 items-center justify-center gap-2 rounded-md bg-skillup-blue px-6 text-sm font-semibold text-white transition-colors hover:bg-blue-700 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-skillup-blue focus-visible:ring-offset-2">
                    Browse courses <ArrowRight className="h-4 w-4" aria-hidden="true" />
                </Link>
            </div>

            {recommendations.length > 0 && (
                <div className="mt-8">
                    <h3 className="mb-5 text-lg font-bold text-skillup-navy">Popular right now</h3>
                    <div className="grid gap-6 sm:grid-cols-2 xl:grid-cols-3">
                        {recommendations.slice(0, 3).map((product) => <CourseCard key={product.id} product={product} />)}
                    </div>
                </div>
            )}
        </div>
    );
}
