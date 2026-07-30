import { Link } from '@inertiajs/react';
import { ArrowRight, CalendarDays, Clock, MapPin, Monitor, Users } from 'lucide-react';
import Img from '@/Components/Img';
import StatusBadge from '@/Components/public/programs/StatusBadge';
import { dateLabel, deliveryLabel, durationLabel } from '@/Components/public/programs/programMeta';

/**
 * Reusable programme card. Every metadata row hides itself when its field is
 * absent, so the card looks complete whether a programme has full details or
 * only a title. Cards flex to equal height inside a grid row.
 */
export default function ProgramCard({ program }) {
    const href = `/programs/${program.slug}`;
    const delivery = deliveryLabel(program.deliveryMode);
    const duration = durationLabel(program.durationWeeks);
    const dates = dateLabel(program.startsOn, program.endsOn);
    const cta = program.acceptsRegistrations ? 'Register now' : 'View programme';

    const meta = [
        dates && { icon: CalendarDays, text: dates },
        duration && { icon: Clock, text: duration },
        program.venueName && { icon: MapPin, text: program.venueName },
        delivery && { icon: Monitor, text: delivery },
        program.audience && { icon: Users, text: program.audience },
    ].filter(Boolean);

    return (
        <article className="group flex h-full flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-card transition-all duration-300 hover:-translate-y-1.5 hover:border-skillup-blue/40 hover:shadow-card-hover focus-within:ring-2 focus-within:ring-skillup-blue/40">
            {/* Cover — real image, else a branded tile (never a stock placeholder). */}
            <div className="relative aspect-[16/9] overflow-hidden bg-skillup-navy">
                {program.heroImagePath ? (
                    <Img
                        src={program.heroImagePath}
                        alt=""
                        className="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105"
                    />
                ) : (
                    <div className="flex h-full w-full items-center justify-center bg-skillup-navy">
                        <span className="text-3xl font-bold uppercase tracking-tight text-white/90">{program.name}</span>
                    </div>
                )}
                <div className="absolute left-3 top-3">
                    <StatusBadge label={program.statusLabel} />
                </div>
            </div>

            <div className="flex flex-1 flex-col p-6">
                <h3 className="text-xl font-bold text-skillup-navy">
                    <Link href={href} className="rounded outline-none after:absolute after:inset-0 focus-visible:ring-2 focus-visible:ring-skillup-blue/40 group-hover:text-skillup-blue">
                        {program.name}
                    </Link>
                </h3>
                {(program.tagline || program.description) && (
                    <p className="mt-2 line-clamp-2 text-sm leading-6 text-gray-600">{program.tagline || program.description}</p>
                )}

                {meta.length > 0 && (
                    <ul className="mt-4 space-y-2 text-sm text-gray-600">
                        {meta.map(({ icon: Icon, text }, index) => (
                            <li key={index} className="flex items-center gap-2">
                                <Icon className="h-4 w-4 flex-shrink-0 text-skillup-blue" aria-hidden="true" />
                                <span className="truncate">{text}</span>
                            </li>
                        ))}
                    </ul>
                )}

                <span className="relative z-10 mt-6 inline-flex items-center gap-2 pt-2 text-sm font-semibold text-skillup-blue">
                    {cta}
                    <ArrowRight className="h-4 w-4 transition-transform duration-300 group-hover:translate-x-1" aria-hidden="true" />
                </span>
            </div>
        </article>
    );
}
