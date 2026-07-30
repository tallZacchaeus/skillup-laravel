import { Link } from '@inertiajs/react';
import { ArrowRight, CalendarDays, Clock, Monitor, PlayCircle } from 'lucide-react';
import Img from '@/Components/Img';

/**
 * Reusable event card. Equal-height flex column. Only renders metadata the event
 * actually has (seats show only when capacity is tracked). Past events with a
 * recording link to "Watch recording" instead of "Register".
 */
export default function EventCard({ event, priority = false }) {
    const isPast = event.status === 'completed';
    const meta = [event.dateLabel, event.timeLabel, event.duration, event.deliveryMode].filter(Boolean);

    return (
        <article className="group flex h-full flex-col overflow-hidden rounded-2xl bg-white shadow-card ring-1 ring-slate-100 transition-all duration-300 hover:-translate-y-1 hover:shadow-card-hover">
            <Link href={event.url} className="relative block aspect-[16/9] overflow-hidden bg-slate-100" tabIndex={-1} aria-hidden="true">
                <Img src={event.image} alt="" className="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105" eager={priority} />
                {event.category && (
                    <span className="absolute left-4 top-4 rounded-full bg-white/90 px-3 py-1 text-xs font-semibold text-skillup-navy shadow-sm backdrop-blur-sm">
                        {event.category.label}
                    </span>
                )}
            </Link>
            <div className="flex flex-1 flex-col p-6">
                <h3 className="line-clamp-2 text-lg font-bold leading-snug text-skillup-navy">
                    <Link href={event.url} className="transition-colors hover:text-skillup-blue focus-visible:underline focus-visible:outline-none">
                        {event.title}
                    </Link>
                </h3>
                {event.summary && <p className="mt-2 line-clamp-2 text-sm leading-6 text-slate-600">{event.summary}</p>}

                <dl className="mt-4 space-y-1.5 text-sm text-slate-600">
                    {event.dateLabel && (
                        <div className="flex items-center gap-2">
                            <CalendarDays className="h-4 w-4 flex-shrink-0 text-skillup-blue" aria-hidden="true" />
                            <dd>{event.dateLabel}{event.timeLabel ? ` · ${event.timeLabel}` : ''}</dd>
                        </div>
                    )}
                    <div className="flex flex-wrap items-center gap-x-4 gap-y-1 text-xs text-slate-500">
                        {event.duration && (
                            <span className="inline-flex items-center gap-1.5"><Clock className="h-3.5 w-3.5" aria-hidden="true" />{event.duration}</span>
                        )}
                        <span className="inline-flex items-center gap-1.5"><Monitor className="h-3.5 w-3.5" aria-hidden="true" />{event.deliveryMode}</span>
                        {event.seatsRemaining !== null && !isPast && (
                            <span className={event.isFull ? 'font-semibold text-red-600' : 'font-medium text-emerald-600'}>
                                {event.isFull ? 'Fully booked' : `${event.seatsRemaining} seats left`}
                            </span>
                        )}
                    </div>
                </dl>

                <div className="mt-5 flex-1" />

                {isPast && event.recordingUrl ? (
                    <a href={event.recordingUrl} target="_blank" rel="noreferrer" className="inline-flex items-center gap-1.5 border-t border-slate-100 pt-4 text-sm font-semibold text-skillup-blue">
                        <PlayCircle className="h-4 w-4" aria-hidden="true" />
                        Watch recording
                    </a>
                ) : (
                    <Link href={event.url} className="inline-flex items-center gap-1.5 border-t border-slate-100 pt-4 text-sm font-semibold text-skillup-blue" aria-label={`${isPast ? 'View' : 'Register for'} ${event.title}`}>
                        {isPast ? 'View details' : 'Register'}
                        <ArrowRight className="h-4 w-4 transition-transform group-hover:translate-x-0.5" aria-hidden="true" />
                    </Link>
                )}
            </div>
        </article>
    );
}
