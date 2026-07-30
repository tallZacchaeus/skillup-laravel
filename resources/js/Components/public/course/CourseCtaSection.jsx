import { Link } from '@inertiajs/react';
import { ArrowRight, Briefcase } from 'lucide-react';
import { buttonVariants } from '@/Components/ui/button';
import { cn } from '@/lib/utils';

/** Closing conversion band shared by every course page. */
export default function CourseCtaSection({ track, primaryUrl = '#curriculum' }) {
    const isAnchor = primaryUrl.startsWith('#');
    const PrimaryTag = isAnchor ? 'a' : Link;

    return (
        <section className="bg-white px-4 py-12 sm:py-16">
            <div className="mx-auto flex w-full max-w-6xl flex-col items-center gap-6 overflow-hidden rounded-3xl bg-skillup-deep px-6 py-16 text-center sm:px-12">
                <h2 className="max-w-2xl text-3xl font-bold text-white sm:text-4xl">Ready to start your {track.title} journey?</h2>
                <p className="max-w-xl text-base leading-relaxed text-blue-50 sm:text-lg">
                    Join a cohort, build real projects, and earn a certificate — with mentor support the whole way.
                </p>
                <div className="flex flex-col gap-3 sm:flex-row">
                    <PrimaryTag href={primaryUrl} className={cn(buttonVariants({ size: 'lg' }), 'bg-white text-skillup-navy hover:bg-blue-50')}>
                        Enroll now
                        <ArrowRight className="h-4 w-4" aria-hidden="true" />
                    </PrimaryTag>
                    <Link href="/corporate" className={cn(buttonVariants({ variant: 'secondary', size: 'lg' }), 'bg-white/10 text-white ring-1 ring-inset ring-white/30 hover:bg-white/20')}>
                        <Briefcase className="h-4 w-4" aria-hidden="true" />
                        Corporate training
                    </Link>
                    <Link href="/contact" className={cn(buttonVariants({ variant: 'secondary', size: 'lg' }), 'bg-transparent text-white ring-1 ring-inset ring-white/30 hover:bg-white/10')}>
                        Contact an advisor
                    </Link>
                </div>
            </div>
        </section>
    );
}
