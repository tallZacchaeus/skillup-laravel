import { Link } from '@inertiajs/react';
import { ArrowRight, BellRing, CalendarDays, Users } from 'lucide-react';
import { buttonVariants } from '@/Components/ui/button';
import { cn } from '@/lib/utils';

/**
 * Designed empty state for the events hub — shown when nothing is scheduled, or
 * when a search/filter returns nothing. Never a raw system message.
 */
export default function EventEmptyState({ filtered = false, onClear }) {
    return (
        <div className="mx-auto max-w-xl rounded-3xl border border-dashed border-slate-300 bg-white px-6 py-16 text-center">
            <div className="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-skillup-blue/10 text-skillup-blue">
                <CalendarDays className="h-8 w-8" aria-hidden="true" />
            </div>
            <h2 className="mt-6 text-2xl font-bold text-skillup-navy">
                {filtered ? 'No events match your search' : 'No events scheduled just yet'}
            </h2>
            <p className="mx-auto mt-3 max-w-md text-base leading-7 text-slate-600">
                {filtered
                    ? 'Try a different keyword or clear your filters to see everything on the calendar.'
                    : 'New webinars and workshops are on the way. Get notified, keep learning, and join the community in the meantime.'}
            </p>
            <div className="mt-8 flex flex-col items-center justify-center gap-3 sm:flex-row">
                {filtered && onClear ? (
                    <button type="button" onClick={onClear} className={cn(buttonVariants({ variant: 'default' }))}>
                        Clear search
                    </button>
                ) : (
                    <>
                        <a href="#alerts" className={cn(buttonVariants({ variant: 'default' }))}>
                            <BellRing className="h-4 w-4" aria-hidden="true" />
                            Get event alerts
                        </a>
                        <Link href="/courses" className={cn(buttonVariants({ variant: 'outline' }))}>
                            Browse courses
                            <ArrowRight className="h-4 w-4" aria-hidden="true" />
                        </Link>
                        <Link href="/community" className={cn(buttonVariants({ variant: 'outline' }))}>
                            <Users className="h-4 w-4" aria-hidden="true" />
                            Join the community
                        </Link>
                    </>
                )}
            </div>
        </div>
    );
}
