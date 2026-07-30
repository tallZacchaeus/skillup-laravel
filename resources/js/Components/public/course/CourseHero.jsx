import { Link } from '@inertiajs/react';
import { ArrowRight, Award, Briefcase, Clock, Layers, Monitor } from 'lucide-react';
import Img from '@/Components/Img';
import Breadcrumbs from '@/Components/Breadcrumbs';
import { buttonVariants } from '@/Components/ui/button';
import { cn } from '@/lib/utils';

/**
 * Course landing hero. Data-driven — every track renders through it. The primary
 * CTA links to the enrollable product when one exists, otherwise scrolls to the
 * curriculum. Only shows metadata the track actually has.
 */
export default function CourseHero({ track, valueProp, primaryUrl = '#curriculum' }) {
    const meta = [
        { icon: Layers, label: 'Level', value: track.level },
        track.duration && track.duration !== 'TBA' && track.duration !== 'Coming soon'
            ? { icon: Clock, label: 'Duration', value: track.duration }
            : null,
        { icon: Monitor, label: 'Delivery', value: 'Online' },
        { icon: Award, label: 'Certificate', value: 'Included' },
    ].filter(Boolean);

    const isAnchor = primaryUrl.startsWith('#');
    const PrimaryTag = isAnchor ? 'a' : Link;

    return (
        <section className="bg-skillup-navy pb-16 pt-28 text-white sm:pt-32">
            <div className="mx-auto grid max-w-7xl items-center gap-10 px-4 sm:px-6 lg:grid-cols-[1fr_0.85fr] lg:gap-14 lg:px-8">
                <div>
                    <Breadcrumbs tone="light" items={[{ label: 'Courses', href: '/courses' }, { label: track.title }]} />
                    <span className="mt-5 inline-block text-xs font-bold uppercase tracking-[0.14em] text-blue-300">
                        {track.category}
                    </span>
                    <h1 className="mt-3 max-w-2xl text-4xl font-bold leading-[1.1] tracking-tight sm:text-5xl">
                        {track.title}
                    </h1>
                    {valueProp && <p className="mt-5 max-w-xl text-lg leading-8 text-blue-100">{valueProp}</p>}

                    <dl className="mt-8 grid max-w-lg grid-cols-2 gap-3 sm:grid-cols-4">
                        {meta.map((item) => (
                            <div key={item.label} className="rounded-xl bg-white/5 p-3 ring-1 ring-inset ring-white/10">
                                <item.icon className="h-5 w-5 text-blue-300" aria-hidden="true" />
                                <dt className="mt-2 text-[11px] font-medium uppercase tracking-wide text-blue-200/80">{item.label}</dt>
                                <dd className="text-sm font-semibold text-white">{item.value}</dd>
                            </div>
                        ))}
                    </dl>

                    <div className="mt-8 flex flex-col gap-3 sm:flex-row sm:items-center">
                        <PrimaryTag href={primaryUrl} className={cn(buttonVariants({ size: 'lg' }), 'bg-white text-skillup-navy hover:bg-blue-50')}>
                            Enroll now
                            <ArrowRight className="h-4 w-4" aria-hidden="true" />
                        </PrimaryTag>
                        <Link href="/corporate" className={cn(buttonVariants({ variant: 'secondary', size: 'lg' }), 'bg-white/10 text-white ring-1 ring-inset ring-white/30 hover:bg-white/20')}>
                            <Briefcase className="h-4 w-4" aria-hidden="true" />
                            Corporate training
                        </Link>
                        {track.price && (
                            <span className="text-sm text-blue-100">
                                From <span className="font-bold text-white">{track.price}</span>
                            </span>
                        )}
                    </div>
                </div>

                <div className="relative">
                    <Img src={track.image} alt={track.title} className="aspect-[4/3] w-full rounded-2xl object-cover shadow-2xl" eager />
                </div>
            </div>
        </section>
    );
}
