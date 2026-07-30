import { Link } from '@inertiajs/react';
import { ArrowRight, Users } from 'lucide-react';
import Img from '@/Components/Img';
import Breadcrumbs from '@/Components/Breadcrumbs';
import { useHeroIntro } from '@/lib/animations';

/**
 * Community landing hero. Two-column on desktop: value copy + CTAs on the left,
 * community imagery on the right. Content is passed in so the hero stays reusable.
 */
export default function CommunityHero({ eyebrow, heading, description, primary, secondary, image }) {
    const heroScope = useHeroIntro();

    return (
        <section ref={heroScope} className="relative overflow-hidden bg-skillup-navy pt-[72px] text-white">
            <div className="mx-auto grid max-w-7xl items-center gap-10 px-4 py-16 sm:px-6 sm:py-20 lg:grid-cols-2 lg:px-8">
                <div>
                    <Breadcrumbs items={[{ label: 'Home', href: '/' }, { label: 'Community' }]} tone="light" />
                    {eyebrow && (
                        <span data-hero className="mt-6 inline-flex items-center gap-2 rounded-full bg-white/10 px-4 py-1.5 text-xs font-bold uppercase tracking-[0.12em] text-blue-200 ring-1 ring-white/20">
                            <Users className="h-4 w-4" aria-hidden="true" />
                            {eyebrow}
                        </span>
                    )}
                    <h1 data-hero className="mt-4 text-4xl font-bold leading-[1.1] tracking-tight sm:text-5xl">{heading}</h1>
                    <p data-hero className="mt-5 max-w-xl text-lg leading-8 text-blue-100">{description}</p>
                    <div data-hero className="mt-8 flex flex-col gap-3 sm:flex-row sm:items-center">
                        <Link
                            href={primary.href}
                            className="inline-flex h-12 items-center justify-center gap-2 rounded-md bg-white px-6 text-base font-semibold text-skillup-navy shadow-sm transition-colors hover:bg-blue-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-white focus-visible:ring-offset-2 focus-visible:ring-offset-skillup-navy"
                        >
                            {primary.label}
                            <ArrowRight className="h-5 w-5" aria-hidden="true" />
                        </Link>
                        <Link
                            href={secondary.href}
                            className="inline-flex h-12 items-center justify-center gap-2 rounded-md border border-white/30 px-6 text-base font-semibold text-white transition-colors hover:bg-white/10 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-white focus-visible:ring-offset-2 focus-visible:ring-offset-skillup-navy"
                        >
                            {secondary.label}
                        </Link>
                    </div>
                </div>

                {image && (
                    <div data-hero className="relative">
                        <Img
                            src={image.src}
                            alt={image.alt}
                            className="aspect-[4/3] w-full rounded-2xl object-cover shadow-elevated ring-1 ring-white/10"
                            eager
                        />
                    </div>
                )}
            </div>
        </section>
    );
}
