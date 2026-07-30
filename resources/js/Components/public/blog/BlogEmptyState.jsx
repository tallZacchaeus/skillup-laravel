import { Link } from '@inertiajs/react';
import { ArrowRight, Newspaper, Users } from 'lucide-react';
import { buttonVariants } from '@/Components/ui/button';
import { cn } from '@/lib/utils';

/**
 * Designed empty state for the blog — shown when no articles are published yet,
 * or when a search/filter returns nothing. Never exposes a raw system message.
 */
export default function BlogEmptyState({ filtered = false, onClear }) {
    return (
        <div className="mx-auto max-w-xl rounded-3xl border border-dashed border-slate-300 bg-white px-6 py-16 text-center">
            <div className="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-skillup-blue/10 text-skillup-blue">
                <Newspaper className="h-8 w-8" aria-hidden="true" />
            </div>
            <h2 className="mt-6 text-2xl font-bold text-skillup-navy">
                {filtered ? 'No articles match your search' : 'Fresh insights are on the way'}
            </h2>
            <p className="mx-auto mt-3 max-w-md text-base leading-7 text-slate-600">
                {filtered
                    ? 'Try a different keyword or clear your filters to see everything we’ve published.'
                    : 'We’re busy writing practical guides, career stories, and tech insights. In the meantime, keep building your skills with SkillUp.'}
            </p>
            <div className="mt-8 flex flex-col items-center justify-center gap-3 sm:flex-row">
                {filtered && onClear ? (
                    <button type="button" onClick={onClear} className={cn(buttonVariants({ variant: 'default' }))}>
                        Clear search
                    </button>
                ) : (
                    <Link href="/courses" className={cn(buttonVariants({ variant: 'default' }))}>
                        Explore courses
                        <ArrowRight className="h-4 w-4" aria-hidden="true" />
                    </Link>
                )}
                <Link href="/community" className={cn(buttonVariants({ variant: 'outline' }))}>
                    <Users className="h-4 w-4" aria-hidden="true" />
                    Join the community
                </Link>
            </div>
        </div>
    );
}
