import { Link } from '@inertiajs/react';
import { ArrowRight, Sparkles } from 'lucide-react';
import Img from '@/Components/Img';
import { useHeroIntro } from '@/lib/animations';

function greeting(date = new Date()) {
    const h = date.getHours();
    if (h < 12) return 'Good morning';
    if (h < 18) return 'Good afternoon';
    return 'Good evening';
}

const ENCOURAGERS = [
    'Small steps every day add up to big results.',
    'Consistency beats intensity — keep the momentum going.',
    'Every lesson moves you closer to your goal.',
    'Show up today; your future self will thank you.',
];

/**
 * Personalised, time-aware dashboard hero. Highlights the learner's current
 * course with a Continue-learning CTA. Progress percentage is shown ONLY when
 * real progress data is supplied — it is never fabricated.
 */
export default function DashboardHero({ name, primaryCourse, progress = null }) {
    const heroScope = useHeroIntro();
    // A stable encourager derived from the day, so it doesn't flicker per render.
    const encourager = ENCOURAGERS[new Date().getDate() % ENCOURAGERS.length];

    return (
        <section ref={heroScope} className="relative overflow-hidden bg-skillup-navy pt-[88px] text-white" aria-labelledby="dash-greeting">
            <div className="mx-auto grid max-w-7xl items-center gap-8 px-4 pb-12 pt-4 sm:px-6 lg:grid-cols-[1.4fr_1fr] lg:px-8">
                <div>
                    <p data-hero className="inline-flex items-center gap-2 text-sm font-semibold text-blue-200">
                        <Sparkles className="h-4 w-4" aria-hidden="true" />
                        {greeting()}
                    </p>
                    <h1 id="dash-greeting" data-hero className="mt-2 text-3xl font-bold tracking-tight sm:text-4xl">
                        Welcome back, {name}
                    </h1>
                    <p data-hero className="mt-3 max-w-xl text-base leading-7 text-blue-100">{encourager}</p>
                </div>

                {primaryCourse ? (
                    <div data-hero className="rounded-2xl bg-white/10 p-5 ring-1 ring-white/15 backdrop-blur">
                        <p className="text-xs font-semibold uppercase tracking-wide text-blue-200">Jump back in</p>
                        <div className="mt-3 flex gap-4">
                            <Img src={primaryCourse.image} alt="" className="h-16 w-24 flex-shrink-0 rounded-lg object-cover" />
                            <div className="min-w-0">
                                {primaryCourse.trackTitle && <p className="truncate text-xs font-semibold text-blue-200">{primaryCourse.trackTitle}</p>}
                                <p className="line-clamp-2 font-bold leading-snug">{primaryCourse.title}</p>
                            </div>
                        </div>

                        {typeof progress === 'number' && (
                            <div className="mt-4">
                                <div className="flex items-center justify-between text-xs text-blue-100">
                                    <span>Progress</span>
                                    <span className="font-semibold text-white">{progress}%</span>
                                </div>
                                <div className="mt-1.5 h-2 w-full overflow-hidden rounded-full bg-white/15" role="progressbar" aria-valuenow={progress} aria-valuemin={0} aria-valuemax={100} aria-label={`${primaryCourse.title} progress`}>
                                    <span className="block h-full rounded-full bg-white transition-[width] duration-700" style={{ width: `${progress}%` }} />
                                </div>
                            </div>
                        )}

                        <Link
                            href={primaryCourse.url}
                            className="mt-4 inline-flex h-11 w-full items-center justify-center gap-2 rounded-md bg-white text-sm font-semibold text-skillup-navy transition-colors hover:bg-blue-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-white focus-visible:ring-offset-2 focus-visible:ring-offset-skillup-navy"
                        >
                            Continue learning
                            <ArrowRight className="h-4 w-4" aria-hidden="true" />
                        </Link>
                    </div>
                ) : (
                    <div data-hero className="rounded-2xl bg-white/10 p-6 text-center ring-1 ring-white/15 backdrop-blur">
                        <p className="font-semibold">Ready to start learning?</p>
                        <p className="mt-1 text-sm text-blue-100">Browse the catalogue and enrol in your first course.</p>
                        <Link href="/courses" className="mt-4 inline-flex h-11 items-center justify-center gap-2 rounded-md bg-white px-5 text-sm font-semibold text-skillup-navy hover:bg-blue-50">
                            Explore courses
                            <ArrowRight className="h-4 w-4" aria-hidden="true" />
                        </Link>
                    </div>
                )}
            </div>
        </section>
    );
}
