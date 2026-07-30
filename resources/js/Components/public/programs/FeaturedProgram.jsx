import { Link } from '@inertiajs/react';
import { ArrowRight, CalendarDays, Clock, Monitor, Users } from 'lucide-react';
import Img from '@/Components/Img';
import StatusBadge from '@/Components/public/programs/StatusBadge';
import { dateLabel, deliveryLabel, durationLabel } from '@/Components/public/programs/programMeta';

/**
 * Flagship programme banner. Renders nothing when no featured programme is
 * supplied. Every detail row hides when its field is missing.
 */
export default function FeaturedProgram({ program }) {
    if (!program) return null;

    const href = `/programs/${program.slug}`;
    const dates = dateLabel(program.startsOn, program.endsOn);
    const duration = durationLabel(program.durationWeeks);
    const delivery = deliveryLabel(program.deliveryMode);

    const facts = [
        dates && { icon: CalendarDays, label: 'Dates', text: dates },
        duration && { icon: Clock, label: 'Duration', text: duration },
        program.audience && { icon: Users, label: 'For', text: program.audience },
        delivery && { icon: Monitor, label: 'Format', text: delivery },
    ].filter(Boolean);

    return (
        <section className="bg-white py-16 sm:py-20" aria-labelledby="featured-heading">
            <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <p className="mb-6 text-sm font-bold uppercase tracking-[0.14em] text-skillup-blue" data-reveal>Featured programme</p>
                <div className="grid items-stretch gap-8 overflow-hidden rounded-3xl border border-slate-200 bg-skillup-soft shadow-card lg:grid-cols-2" data-reveal>
                    {/* Banner */}
                    <div className="relative min-h-[240px] bg-skillup-navy lg:min-h-full">
                        {program.heroImagePath ? (
                            <Img src={program.heroImagePath} alt="" className="absolute inset-0 h-full w-full object-cover" eager />
                        ) : (
                            <div className="flex h-full w-full items-center justify-center p-8">
                                <span className="text-center text-4xl font-bold uppercase leading-tight tracking-tight text-white/90">{program.name}</span>
                            </div>
                        )}
                    </div>

                    {/* Detail */}
                    <div className="flex flex-col justify-center p-8 sm:p-10">
                        <div className="flex items-center gap-3">
                            <StatusBadge label={program.statusLabel} />
                            {program.year && <span className="text-sm font-semibold text-gray-500">{program.year}</span>}
                        </div>
                        <h2 id="featured-heading" className="mt-4 text-3xl font-bold text-skillup-navy sm:text-4xl">{program.name}</h2>
                        {(program.tagline || program.description) && (
                            <p className="mt-3 text-base leading-7 text-gray-600">{program.tagline || program.description}</p>
                        )}

                        {facts.length > 0 && (
                            <dl className="mt-6 grid grid-cols-2 gap-4">
                                {facts.map(({ icon: Icon, label, text }) => (
                                    <div key={label} className="flex items-start gap-3">
                                        <span className="flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-lg bg-white text-skillup-blue shadow-sm">
                                            <Icon className="h-4 w-4" aria-hidden="true" />
                                        </span>
                                        <div>
                                            <dt className="text-xs font-semibold uppercase tracking-wide text-gray-500">{label}</dt>
                                            <dd className="text-sm font-semibold text-skillup-navy">{text}</dd>
                                        </div>
                                    </div>
                                ))}
                            </dl>
                        )}

                        <div className="mt-8">
                            <Link
                                href={href}
                                className="inline-flex h-12 items-center justify-center gap-2 rounded-md bg-skillup-blue px-7 text-base font-semibold text-white shadow-sm transition-colors hover:bg-blue-700 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-skillup-blue focus-visible:ring-offset-2"
                            >
                                {program.acceptsRegistrations ? 'Register now' : 'View programme'}
                                <ArrowRight className="h-5 w-5" aria-hidden="true" />
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    );
}
