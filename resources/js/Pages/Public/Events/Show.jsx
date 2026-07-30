import { Head, Link, useForm } from '@inertiajs/react';
import { ArrowLeft, CalendarDays, CalendarPlus, CheckCircle2, Clock, Download, Loader2, Monitor, Send } from 'lucide-react';
import PublicLayout from '@/Components/public/PublicLayout';
import Breadcrumbs from '@/Components/Breadcrumbs';
import StructuredData from '@/Components/StructuredData';
import { buttonVariants } from '@/Components/ui/button';
import { downloadIcs, googleCalendarUrl } from '@/lib/calendar';
import { cn } from '@/lib/utils';

const inputClass =
    'mt-1 block h-11 w-full rounded-md border border-slate-300 px-3 text-sm text-slate-900 shadow-sm outline-none transition focus:border-skillup-blue focus:ring-2 focus:ring-skillup-blue/20';

export default function EventShow({ event, structuredData = null }) {
    const { data, setData, post, processing, errors, reset, wasSuccessful } = useForm({ name: '', email: '', phone: '' });

    const submitRegistration = (e) => {
        e.preventDefault();
        post(route('events.register', { slug: event.slug }), { onSuccess: () => reset() });
    };

    const canRegister = event.status === 'upcoming' && !event.isFull;
    const gcalUrl = googleCalendarUrl(event);
    const meta = [
        event.dateLabel ? { icon: CalendarDays, label: event.dateLabel + (event.timeLabel ? ` · ${event.timeLabel}` : '') } : null,
        event.duration ? { icon: Clock, label: event.duration } : null,
        { icon: Monitor, label: event.deliveryMode },
    ].filter(Boolean);

    return (
        <PublicLayout>
            <Head title={`${event.title} — SkillUp Events`}>
                <meta head-key="description" name="description" content={event.summary || ''} />
                <meta head-key="og:type" property="og:type" content="website" />
                <meta head-key="og:title" property="og:title" content={event.title} />
                <meta head-key="og:description" property="og:description" content={event.summary || ''} />
                <meta head-key="twitter:card" name="twitter:card" content="summary_large_image" />
            </Head>
            <StructuredData data={structuredData} />

            <section className="bg-skillup-navy pb-12 pt-28 text-white sm:pt-32">
                <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <Breadcrumbs items={[{ label: 'Home', href: '/' }, { label: 'Events', href: '/events' }, { label: event.category?.label || 'Event' }]} tone="light" />
                    <div className="mt-5 flex flex-wrap items-center gap-2">
                        {event.category && <span className="inline-flex items-center rounded-full bg-white/10 px-3 py-1 text-xs font-semibold text-blue-100">{event.category.label}</span>}
                        {event.status === 'live' && <span className="inline-flex items-center rounded-full bg-red-500 px-3 py-1 text-xs font-bold text-white motion-safe:animate-pulse">Live now</span>}
                    </div>
                    <h1 className="mt-4 max-w-3xl text-3xl font-bold leading-tight tracking-tight sm:text-4xl md:text-5xl">{event.title}</h1>
                    <dl className="mt-5 flex flex-wrap gap-x-6 gap-y-2 text-sm text-blue-100">
                        {meta.map((item) => (
                            <span key={item.label} className="inline-flex items-center gap-1.5">
                                <item.icon className="h-4 w-4" aria-hidden="true" />
                                {item.label}
                            </span>
                        ))}
                    </dl>
                </div>
            </section>

            <section className="bg-white py-12 sm:py-16">
                <div className="mx-auto grid max-w-7xl gap-12 px-4 sm:px-6 lg:grid-cols-12 lg:px-8">
                    <div className="lg:col-span-7">
                        {event.description && (
                            <div className="prose prose-slate max-w-none leading-8 text-slate-700" dangerouslySetInnerHTML={{ __html: event.description }} />
                        )}

                        <div className="mt-8 flex flex-wrap items-center gap-3 border-t border-slate-100 pt-6">
                            <span className="text-sm font-semibold text-slate-500">Add to calendar:</span>
                            {gcalUrl && (
                                <a href={gcalUrl} target="_blank" rel="noreferrer" className={cn(buttonVariants({ variant: 'outline', size: 'sm' }))}>
                                    <CalendarPlus className="h-4 w-4" aria-hidden="true" />
                                    Google Calendar
                                </a>
                            )}
                            <button type="button" onClick={() => downloadIcs(event)} className={cn(buttonVariants({ variant: 'outline', size: 'sm' }))}>
                                <Download className="h-4 w-4" aria-hidden="true" />
                                Download .ics
                            </button>
                        </div>

                        <div className="mt-8">
                            <Link href="/events" className="inline-flex items-center gap-2 text-sm font-semibold text-skillup-blue hover:underline">
                                <ArrowLeft className="h-4 w-4" aria-hidden="true" />
                                Back to all events
                            </Link>
                        </div>
                    </div>

                    <div className="lg:col-span-5">
                        <div className="sticky top-24 rounded-2xl border border-slate-200 bg-white p-6 shadow-card">
                            <h2 className="text-xl font-bold text-skillup-navy">Register for this event</h2>
                            {event.seatsRemaining !== null && (
                                <p className={cn('mt-1 text-sm font-medium', event.isFull ? 'text-red-600' : 'text-emerald-600')}>
                                    {event.isFull ? 'This event is fully booked.' : `${event.seatsRemaining} seats remaining`}
                                </p>
                            )}
                            <p className="mt-2 text-sm text-slate-600">Save your spot — you’ll get a confirmation email with the joining details.</p>

                            {wasSuccessful ? (
                                <div className="mt-6 flex items-start gap-3 rounded-xl border border-emerald-200 bg-emerald-50 p-4" role="status" aria-live="polite">
                                    <CheckCircle2 className="mt-0.5 h-5 w-5 flex-shrink-0 text-emerald-500" aria-hidden="true" />
                                    <p className="text-sm font-medium text-emerald-800">You’re registered! Check your inbox for the confirmation email.</p>
                                </div>
                            ) : (
                                <form onSubmit={submitRegistration} className="mt-6 space-y-4" noValidate>
                                    {errors.message && (
                                        <div className="rounded-lg border border-red-200 bg-red-50 p-3 text-sm font-medium text-red-800" role="alert" aria-live="assertive">{errors.message}</div>
                                    )}
                                    <div>
                                        <label htmlFor="reg-name" className="block text-sm font-semibold text-slate-700">Full name <span className="text-red-500" aria-hidden="true">*</span></label>
                                        <input id="reg-name" type="text" required autoComplete="name" value={data.name} onChange={(e) => setData('name', e.target.value)} placeholder="Jane Doe" className={inputClass} aria-invalid={errors.name ? 'true' : undefined} />
                                        {errors.name && <p className="mt-1 text-xs font-medium text-red-600">{errors.name}</p>}
                                    </div>
                                    <div>
                                        <label htmlFor="reg-email" className="block text-sm font-semibold text-slate-700">Email <span className="text-red-500" aria-hidden="true">*</span></label>
                                        <input id="reg-email" type="email" required autoComplete="email" value={data.email} onChange={(e) => setData('email', e.target.value)} placeholder="jane@example.com" className={inputClass} aria-invalid={errors.email ? 'true' : undefined} />
                                        {errors.email && <p className="mt-1 text-xs font-medium text-red-600">{errors.email}</p>}
                                    </div>
                                    <div>
                                        <label htmlFor="reg-phone" className="block text-sm font-semibold text-slate-700">Phone <span className="ml-1 text-xs font-normal text-slate-400">(optional)</span></label>
                                        <input id="reg-phone" type="tel" autoComplete="tel" value={data.phone} onChange={(e) => setData('phone', e.target.value)} placeholder="+234 800 000 0000" className={inputClass} />
                                        {errors.phone && <p className="mt-1 text-xs font-medium text-red-600">{errors.phone}</p>}
                                    </div>
                                    <button type="submit" disabled={processing || !canRegister} className="inline-flex h-11 w-full items-center justify-center gap-2 rounded-md bg-skillup-blue text-sm font-semibold text-white transition-colors hover:bg-blue-700 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-skillup-blue focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:bg-slate-300">
                                        {processing ? (
                                            <><Loader2 className="h-4 w-4 motion-safe:animate-spin" aria-hidden="true" />Registering…</>
                                        ) : canRegister ? (
                                            <><Send className="h-4 w-4" aria-hidden="true" />Register</>
                                        ) : event.isFull ? 'Fully booked' : 'Registrations closed'}
                                    </button>
                                </form>
                            )}
                        </div>
                    </div>
                </div>
            </section>
        </PublicLayout>
    );
}
