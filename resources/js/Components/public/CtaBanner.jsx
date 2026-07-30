import { Link } from '@inertiajs/react';
import { ArrowRight } from 'lucide-react';
import { buttonVariants } from '@/Components/ui/button';
import { cn } from '@/lib/utils';

/**
 * Generic closing conversion band. Reusable across marketing pages — pass a
 * heading, optional description, and primary/secondary { label, href } CTAs.
 */
export default function CtaBanner({ heading, description, primary, secondary }) {
    return (
        <section className="bg-white px-4 py-12 sm:py-16">
            <div className="mx-auto flex w-full max-w-6xl flex-col items-center gap-6 overflow-hidden rounded-3xl bg-skillup-deep px-6 py-16 text-center sm:px-12">
                <h2 className="max-w-2xl text-3xl font-bold text-white sm:text-4xl">{heading}</h2>
                {description && <p className="max-w-xl text-base leading-relaxed text-blue-50 sm:text-lg">{description}</p>}
                <div className="flex flex-col gap-3 sm:flex-row">
                    {primary && (
                        <Link href={primary.href} className={cn(buttonVariants({ size: 'lg' }), 'bg-white text-skillup-navy hover:bg-blue-50')}>
                            {primary.label}
                            <ArrowRight className="h-4 w-4" aria-hidden="true" />
                        </Link>
                    )}
                    {secondary && (
                        <Link href={secondary.href} className={cn(buttonVariants({ variant: 'secondary', size: 'lg' }), 'bg-white/10 text-white ring-1 ring-inset ring-white/30 hover:bg-white/20')}>
                            {secondary.label}
                        </Link>
                    )}
                </div>
            </div>
        </section>
    );
}
