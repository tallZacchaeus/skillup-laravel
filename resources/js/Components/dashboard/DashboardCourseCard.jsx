import { Link } from '@inertiajs/react';
import { ArrowRight, CalendarDays, Info, Lock } from 'lucide-react';
import Img from '@/Components/Img';
import { cn } from '@/lib/utils';

const STATUS_STYLES = {
    Active: 'bg-emerald-50 text-emerald-700 ring-emerald-200',
    Completed: 'bg-skillup-blue/10 text-skillup-blue ring-blue-200',
    Pending: 'bg-amber-50 text-amber-700 ring-amber-200',
    Partial: 'bg-amber-50 text-amber-700 ring-amber-200',
    Suspended: 'bg-slate-100 text-slate-600 ring-slate-200',
    Failed: 'bg-red-50 text-red-700 ring-red-200',
    Cancelled: 'bg-slate-100 text-slate-500 ring-slate-200',
};

/**
 * Enrolled-course card for the dashboard. Shows real enrolment data only
 * (status, enrol date). Inaccessible enrolments explain why instead of showing
 * a dead CTA. Progress bars are intentionally omitted — no per-lesson progress
 * data is tracked, so none is invented.
 */
export default function DashboardCourseCard({ course }) {
    const style = STATUS_STYLES[course.statusLabel] ?? STATUS_STYLES.Suspended;

    return (
        <article className="group flex h-full flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-card transition-all duration-300 hover:-translate-y-1 hover:shadow-card-hover">
            <div className="relative aspect-[16/9] overflow-hidden bg-slate-100">
                <Img
                    src={course.image}
                    alt=""
                    className={cn('h-full w-full object-cover transition-transform duration-500 group-hover:scale-105', !course.accessible && 'opacity-60')}
                    loading="lazy"
                />
                <span className={cn('absolute left-3 top-3 inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-bold ring-1', style)}>
                    {!course.accessible && <Lock className="h-3 w-3" aria-hidden="true" />}
                    {course.statusLabel}
                </span>
            </div>

            <div className="flex flex-1 flex-col p-5">
                {course.trackTitle && <p className="text-xs font-semibold uppercase tracking-wide text-skillup-blue">{course.trackTitle}</p>}
                <h3 className="mt-1 line-clamp-2 text-lg font-bold leading-snug text-skillup-navy">{course.title}</h3>

                {course.enrolledAt && (
                    <p className="mt-2 inline-flex items-center gap-1.5 text-xs text-slate-500">
                        <CalendarDays className="h-3.5 w-3.5" aria-hidden="true" />
                        Enrolled {course.enrolledAt}
                    </p>
                )}

                {course.accessible ? (
                    <p className="mt-2 line-clamp-2 flex-1 text-sm leading-6 text-slate-600">{course.summary}</p>
                ) : (
                    <p className="mt-3 flex flex-1 items-start gap-2 rounded-lg bg-amber-50 p-3 text-xs leading-5 text-amber-800">
                        <Info className="mt-0.5 h-4 w-4 flex-shrink-0" aria-hidden="true" />
                        {course.pendingReason}
                    </p>
                )}

                {course.accessible ? (
                    <Link
                        href={course.url}
                        className="mt-4 inline-flex h-11 items-center justify-center gap-2 rounded-md bg-skillup-blue px-4 text-sm font-semibold text-white transition-colors hover:bg-blue-700 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-skillup-blue focus-visible:ring-offset-2"
                    >
                        {course.statusLabel === 'Completed' ? 'Review course' : 'Continue learning'}
                        <ArrowRight className="h-4 w-4" aria-hidden="true" />
                    </Link>
                ) : (
                    <Link
                        href={course.url}
                        className="mt-4 inline-flex h-11 items-center justify-center gap-2 rounded-md border border-slate-300 px-4 text-sm font-semibold text-slate-700 transition-colors hover:bg-slate-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-skillup-blue/40"
                    >
                        View course details
                    </Link>
                )}
            </div>
        </article>
    );
}
