import { useEffect, useMemo, useRef, useState } from 'react';
import Img from '@/Components/Img';
import { Head, Link, useForm } from '@inertiajs/react';
import { ArrowRight, CalendarDays, CheckCircle2, Clock, Loader2, MapPin, Ticket, Users, X } from 'lucide-react';
import PublicLayout from '@/Components/public/PublicLayout';
import ContentBlocks from '@/Components/public/programs/ContentBlocks';
import { useHeroIntro, useRevealScope } from '@/lib/animations';

const REFERRAL_SOURCES = ['Instagram', 'Facebook', 'WhatsApp', 'Friend or family', "My child's school", 'Google search', 'Other'];

const formatDate = (value, opts) =>
    value ? new Date(value).toLocaleDateString(undefined, opts) : null;

export default function Show({ program, edition, tracks, archiveEditions = [] }) {
    const scope = useRevealScope();
    const heroScope = useHeroIntro();
    const formRef = useRef(null);
    const [selectedTrack, setSelectedTrack] = useState(null);

    const openForm = (track = null) => {
        setSelectedTrack(track);
        formRef.current?.scrollIntoView({ behavior: 'smooth', block: 'center' });
    };

    const startDate = formatDate(edition.startsOn, { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' });
    const startShort = formatDate(edition.startsOn, { day: 'numeric', month: 'short' });
    const endShort = formatDate(edition.endsOn, { day: 'numeric', month: 'short', year: 'numeric' });
    const deadlineDate = formatDate(edition.registrationDeadline, { day: 'numeric', month: 'long' });

    const ageSpan = useMemo(() => {
        const mins = tracks.map((t) => t.ageMin).filter((v) => v !== null);
        const maxs = tracks.map((t) => t.ageMax).filter((v) => v !== null);
        if (!mins.length || !maxs.length) return null;
        return `${Math.min(...mins)} – ${Math.max(...maxs)}`;
    }, [tracks]);

    // Lowest effective (post-discount) price across tracks, for "from" pricing.
    const fromPrice = useMemo(() => {
        const priced = tracks
            .filter((t) => t.amount !== null)
            .map((t) => ({ currency: t.currency, value: t.discountedAmount ?? t.amount }));
        if (!priced.length) return null;
        return priced.reduce((min, p) => (p.value < min.value ? p : min), priced[0]);
    }, [tracks]);

    const dateRange = startShort && endShort ? `${startShort} – ${endShort}` : startDate;

    return (
        <PublicLayout>
            <Head title={edition.seoTitle}>
                {edition.seoDescription && <meta name="description" content={edition.seoDescription} />}
                {edition.heroImagePath && <link rel="preload" as="image" href={edition.heroImagePath} />}
            </Head>

            <div ref={scope}>
                {/* Hero */}
                <section ref={heroScope} className="relative overflow-hidden bg-skillup-navy pt-[72px]">
                    {edition.heroImagePath && (
                        <Img src={edition.heroImagePath} alt="" className="absolute inset-0 h-full w-full object-cover opacity-30" eager />
                    )}
                    <div className="relative z-10 mx-auto flex max-w-5xl flex-col items-center px-4 py-20 text-center sm:py-28">
                        {ageSpan && (
                            <span data-hero className="mb-6 inline-flex items-center rounded-full bg-skillup-orange px-5 py-2 text-sm font-bold text-white">
                                For ages {ageSpan}
                            </span>
                        )}
                        <h1 data-hero className="text-4xl font-bold uppercase leading-tight tracking-tight text-white sm:text-6xl lg:text-7xl">
                            {edition.title}
                        </h1>
                        {edition.theme && (
                            <p data-hero className="mt-6 max-w-2xl text-lg text-blue-100 sm:text-xl">
                                {edition.theme}
                            </p>
                        )}

                        {/* Supporting value paragraph — admin copy, else a factual fallback. */}
                        <p data-hero className="mt-4 max-w-2xl text-base leading-7 text-blue-100/90">
                            {edition.seoDescription
                                ?? `A hands-on holiday programme where young people${ageSpan ? ` aged ${ageSpan}` : ''} learn real AI and Python by building projects of their own — guided by mentors, step by step.`}
                        </p>

                        {startDate && (
                            <p data-hero className="mt-5 text-sm font-semibold uppercase tracking-wide text-blue-200">
                                Classes start {startDate}{edition.scheduleText ? ` · ${edition.scheduleText}` : ''}
                            </p>
                        )}

                        {edition.acceptsRegistrations ? (
                            <>
                                <button
                                    data-hero
                                    type="button"
                                    onClick={() => openForm(null)}
                                    className="mt-8 inline-flex h-12 items-center justify-center gap-2 rounded-md bg-blue-600 px-8 text-base font-semibold text-white shadow-lg transition-colors hover:bg-blue-700 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-white focus-visible:ring-offset-2 focus-visible:ring-offset-skillup-navy"
                                >
                                    Register Now
                                    <ArrowRight className="h-5 w-5" aria-hidden="true" />
                                </button>

                                {/* Real urgency signals — each renders only when its data exists. */}
                                <div data-hero className="mt-8 flex flex-wrap items-center justify-center gap-x-6 gap-y-3 text-sm text-blue-100">
                                    {edition.seatsRemaining !== null && (
                                        <span className="inline-flex items-center gap-2">
                                            <Users className="h-4 w-4 text-blue-300" aria-hidden="true" />
                                            <span className="font-semibold text-white" data-count={edition.seatsRemaining}>0</span>
                                            {edition.seatsTotal ? ` of ${edition.seatsTotal}` : ''} seats remaining
                                        </span>
                                    )}
                                    {deadlineDate && (
                                        <span className="inline-flex items-center gap-2">
                                            <CalendarDays className="h-4 w-4 text-blue-300" aria-hidden="true" />
                                            Register by <span className="font-semibold text-white">{deadlineDate}</span>
                                        </span>
                                    )}
                                </div>

                                <Countdown target={edition.earlyBirdEndsOn} label="Early-bird pricing ends in" />
                            </>
                        ) : (
                            <p data-hero className="mt-8 rounded-full bg-white/10 px-6 py-2 text-sm font-semibold text-white">
                                {edition.status === 'sold_out' ? 'All seats are taken — waitlist only' : 'Registration is not open yet'}
                            </p>
                        )}
                    </div>
                </section>

                {/* Content blocks (admin-ordered) */}
                <ContentBlocks blocks={edition.content} edition={edition} tracks={tracks} onRegister={openForm} />

                {/* Registration */}
                {edition.acceptsRegistrations && (
                    <section ref={formRef} className="bg-white py-16" id="register">
                        <div className="mx-auto max-w-xl px-4 sm:px-6">
                            <div className="rounded-2xl border border-blue-200 bg-skillup-soft p-8 shadow-card" data-reveal>
                                <StepIndicator />
                                <h2 className="mt-6 text-2xl font-bold text-skillup-navy">Register your child</h2>
                                <p className="mt-2 text-sm leading-6 text-gray-600">
                                    Two minutes now — confirm your email, secure the seat, and finish the rest after payment. Ages are
                                    counted as of {formatDate(edition.ageReferenceDate, { day: 'numeric', month: 'long', year: 'numeric' })}.
                                </p>
                                <MicroForm program={program} tracks={tracks} selectedTrack={selectedTrack} onClearTrack={() => setSelectedTrack(null)} />
                                <p className="mt-4 text-xs leading-5 text-gray-500">
                                    By continuing you agree to our processing of your and your ward's details to run this programme.
                                    We only ask for what the programme needs, and you can request removal anytime.
                                </p>
                            </div>
                        </div>
                    </section>
                )}

                {archiveEditions.length > 0 && (
                    <section className="bg-skillup-soft py-12">
                        <div className="mx-auto max-w-7xl px-4 text-center sm:px-6 lg:px-8">
                            <h2 className="text-sm font-semibold uppercase tracking-wide text-gray-500">Past editions</h2>
                            <div className="mt-4 flex flex-wrap justify-center gap-3">
                                {archiveEditions.map((past) => (
                                    <Link
                                        key={past.slug}
                                        href={`/programs/${program.slug}/editions/${past.slug}`}
                                        className="rounded-full bg-white px-5 py-2 text-sm font-semibold text-skillup-navy shadow-sm transition hover:shadow"
                                    >
                                        {past.title}
                                    </Link>
                                ))}
                            </div>
                        </div>
                    </section>
                )}
            </div>

            {/* Desktop sticky conversion panel */}
            {edition.acceptsRegistrations && (
                <aside className="fixed bottom-6 right-6 z-40 hidden w-72 rounded-2xl border border-slate-200 bg-white/95 p-5 shadow-elevated backdrop-blur lg:block">
                    <p className="text-xs font-bold uppercase tracking-wide text-skillup-blue">{edition.title}</p>
                    <dl className="mt-3 space-y-2 text-sm">
                        {dateRange && (
                            <div className="flex items-center gap-2 text-gray-700">
                                <CalendarDays className="h-4 w-4 text-skillup-blue" aria-hidden="true" />
                                <dt className="sr-only">Dates</dt>
                                <dd>{dateRange}</dd>
                            </div>
                        )}
                        {fromPrice && (
                            <div className="flex items-center gap-2 text-gray-700">
                                <Ticket className="h-4 w-4 text-skillup-blue" aria-hidden="true" />
                                <dt className="sr-only">Fee</dt>
                                <dd>From {fromPrice.currency} {Number(fromPrice.value).toLocaleString()}</dd>
                            </div>
                        )}
                        {edition.seatsRemaining !== null && (
                            <div className="flex items-center gap-2 text-gray-700">
                                <Users className="h-4 w-4 text-skillup-blue" aria-hidden="true" />
                                <dt className="sr-only">Seats remaining</dt>
                                <dd>{edition.seatsRemaining} seats remaining</dd>
                            </div>
                        )}
                    </dl>
                    <button
                        type="button"
                        onClick={() => openForm(null)}
                        className="mt-4 flex h-11 w-full items-center justify-center gap-2 rounded-md bg-blue-900 text-sm font-semibold text-white transition-colors hover:bg-blue-700 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-skillup-blue focus-visible:ring-offset-2"
                    >
                        Register Now
                        <ArrowRight className="h-4 w-4" aria-hidden="true" />
                    </button>
                </aside>
            )}

            {/* Sticky mobile CTA */}
            {edition.acceptsRegistrations && (
                <div className="fixed inset-x-0 bottom-0 z-40 border-t border-slate-200 bg-white/95 p-3 backdrop-blur sm:hidden">
                    <button
                        type="button"
                        onClick={() => openForm(null)}
                        className="flex h-12 w-full items-center justify-center rounded-md bg-blue-900 text-base font-semibold text-white"
                    >
                        Register Now
                    </button>
                </div>
            )}
        </PublicLayout>
    );
}

/** Live early-bird countdown. Renders nothing unless the target date is real and still in the future. */
function Countdown({ target, label }) {
    const targetMs = target ? new Date(target).getTime() : null;
    const [remaining, setRemaining] = useState(() => (targetMs ? targetMs - Date.now() : -1));

    useEffect(() => {
        if (!targetMs) return undefined;
        const id = setInterval(() => setRemaining(targetMs - Date.now()), 1000);
        return () => clearInterval(id);
    }, [targetMs]);

    if (!targetMs || remaining <= 0) {
        return null;
    }

    const days = Math.floor(remaining / 86_400_000);
    const hours = Math.floor((remaining % 86_400_000) / 3_600_000);
    const mins = Math.floor((remaining % 3_600_000) / 60_000);
    const secs = Math.floor((remaining % 60_000) / 1000);
    const units = [{ v: days, l: 'days' }, { v: hours, l: 'hrs' }, { v: mins, l: 'min' }, { v: secs, l: 'sec' }];

    return (
        <div data-hero className="mt-6" role="timer" aria-live="off">
            <p className="text-xs font-semibold uppercase tracking-wide text-blue-200">{label}</p>
            <div className="mt-2 flex justify-center gap-2">
                {units.map((u) => (
                    <div key={u.l} className="min-w-[3.5rem] rounded-lg bg-white/10 px-3 py-2 text-center">
                        <span className="block text-xl font-bold tabular-nums text-white">{String(u.v).padStart(2, '0')}</span>
                        <span className="block text-[10px] uppercase tracking-wide text-blue-200">{u.l}</span>
                    </div>
                ))}
            </div>
        </div>
    );
}

function StepIndicator() {
    const steps = ['Your details', 'Verify email', 'Payment'];
    return (
        <ol className="flex items-center gap-2" aria-label="Registration steps">
            {steps.map((label, index) => {
                const active = index === 0;
                return (
                    <li key={label} className="flex flex-1 items-center gap-2">
                        <span
                            aria-current={active ? 'step' : undefined}
                            className={`flex h-7 w-7 flex-shrink-0 items-center justify-center rounded-full text-xs font-bold ${active ? 'bg-skillup-blue text-white' : 'bg-blue-100 text-blue-700'}`}
                        >
                            {index + 1}
                        </span>
                        <span className={`hidden text-xs font-semibold sm:block ${active ? 'text-skillup-navy' : 'text-gray-500'}`}>{label}</span>
                        {index < steps.length - 1 && <span className="h-0.5 flex-1 rounded bg-blue-100" aria-hidden="true" />}
                    </li>
                );
            })}
        </ol>
    );
}

function MicroForm({ program, tracks, selectedTrack, onClearTrack }) {
    const params = typeof window !== 'undefined' ? new URLSearchParams(window.location.search) : new URLSearchParams();
    const initialSrc = params.get('src') ?? '';
    const { data, setData, post, processing, errors, transform } = useForm({
        guardian_name: '',
        guardian_email: '',
        guardian_whatsapp: '',
        participant_name: '',
        participant_dob: '',
        program_edition_track_id: selectedTrack?.id ?? '',
        referral_source: '',
        src: initialSrc,
        utm_source: params.get('utm_source') ?? '',
        utm_medium: params.get('utm_medium') ?? '',
        utm_campaign: params.get('utm_campaign') ?? '',
    });

    const [clientErrors, setClientErrors] = useState({});
    const allErrors = { ...clientErrors, ...errors };

    if (selectedTrack && data.program_edition_track_id !== selectedTrack.id) {
        setData('program_edition_track_id', selectedTrack.id);
    }

    // A chosen referral option is the real acquisition source we persist.
    transform((payload) => ({ ...payload, src: payload.referral_source || payload.src || 'web' }));

    const validate = () => {
        const next = {};
        if (!data.guardian_name.trim()) next.guardian_name = 'Please enter your name.';
        if (!data.guardian_email.trim()) next.guardian_email = 'Please enter your email.';
        else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(data.guardian_email.trim())) next.guardian_email = 'Enter a valid email address.';
        if (!data.guardian_whatsapp.trim()) next.guardian_whatsapp = 'Please enter your WhatsApp number.';
        if (!data.participant_name.trim()) next.participant_name = "Please enter your child's name.";
        if (!data.participant_dob) next.participant_dob = "Please enter your child's date of birth.";
        else if (new Date(data.participant_dob) >= new Date()) next.participant_dob = 'Date of birth must be in the past.';
        return next;
    };

    const submit = (e) => {
        e.preventDefault();
        const found = validate();
        if (Object.keys(found).length > 0) {
            setClientErrors(found);
            window.requestAnimationFrame(() => document.getElementById(Object.keys(found)[0])?.focus());
            return;
        }
        setClientErrors({});
        post(`/programs/${program.slug}/register`, { preserveScroll: true });
    };

    return (
        <form onSubmit={submit} noValidate className="mt-6 space-y-4" aria-busy={processing}>
            {selectedTrack && (
                <div className="flex items-center justify-between rounded-lg bg-blue-100 px-4 py-2 text-sm font-semibold text-blue-900">
                    Track: {selectedTrack.name}
                    <button type="button" onClick={onClearTrack} aria-label="Let age choose the track instead" className="text-blue-700 hover:text-blue-900">
                        <X className="h-4 w-4" aria-hidden="true" />
                    </button>
                </div>
            )}

            <Field id="guardian_name" label="Your full name" error={allErrors.guardian_name}>
                <input id="guardian_name" type="text" required autoComplete="name" value={data.guardian_name}
                    onChange={(e) => setData('guardian_name', e.target.value)}
                    aria-invalid={allErrors.guardian_name ? 'true' : undefined}
                    aria-describedby={allErrors.guardian_name ? 'guardian_name-error' : undefined}
                    className={inputCls(allErrors.guardian_name)} />
            </Field>

            <Field id="guardian_email" label="Your email" error={allErrors.guardian_email} hint="We'll send a confirmation code here.">
                <input id="guardian_email" type="email" required autoComplete="email" value={data.guardian_email}
                    onChange={(e) => setData('guardian_email', e.target.value)}
                    aria-invalid={allErrors.guardian_email ? 'true' : undefined}
                    aria-describedby={allErrors.guardian_email ? 'guardian_email-error' : undefined}
                    className={inputCls(allErrors.guardian_email)} />
            </Field>

            <Field id="guardian_whatsapp" label="Your WhatsApp number" error={allErrors.guardian_whatsapp}>
                <input id="guardian_whatsapp" type="tel" required autoComplete="tel" inputMode="tel" value={data.guardian_whatsapp}
                    onChange={(e) => setData('guardian_whatsapp', e.target.value)}
                    aria-invalid={allErrors.guardian_whatsapp ? 'true' : undefined}
                    aria-describedby={allErrors.guardian_whatsapp ? 'guardian_whatsapp-error' : undefined}
                    className={inputCls(allErrors.guardian_whatsapp)} />
            </Field>

            <div className="grid gap-4 sm:grid-cols-2">
                <Field id="participant_name" label="Child's full name" error={allErrors.participant_name}>
                    <input id="participant_name" type="text" required value={data.participant_name}
                        onChange={(e) => setData('participant_name', e.target.value)}
                        aria-invalid={allErrors.participant_name ? 'true' : undefined}
                        aria-describedby={allErrors.participant_name ? 'participant_name-error' : undefined}
                        className={inputCls(allErrors.participant_name)} />
                </Field>
                <Field id="participant_dob" label="Child's date of birth" error={allErrors.participant_dob}>
                    <input id="participant_dob" type="date" required value={data.participant_dob}
                        onChange={(e) => setData('participant_dob', e.target.value)}
                        aria-invalid={allErrors.participant_dob ? 'true' : undefined}
                        aria-describedby={allErrors.participant_dob ? 'participant_dob-error' : undefined}
                        className={inputCls(allErrors.participant_dob)} />
                </Field>
            </div>

            <Field id="referral_source" label="How did you hear about us?" optional>
                <select id="referral_source" value={data.referral_source}
                    onChange={(e) => setData('referral_source', e.target.value)}
                    className={inputCls(false)}>
                    <option value="">Select an option…</option>
                    {REFERRAL_SOURCES.map((source) => <option key={source} value={source}>{source}</option>)}
                </select>
            </Field>

            {allErrors.program_edition_track_id && <p className="text-sm text-red-600">{allErrors.program_edition_track_id}</p>}

            <button
                type="submit"
                disabled={processing}
                className="flex h-12 w-full items-center justify-center gap-2 rounded-md bg-blue-900 text-base font-semibold text-white transition-colors hover:bg-blue-700 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-skillup-blue focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:bg-slate-400"
            >
                {processing ? (
                    <><Loader2 className="h-5 w-5 motion-safe:animate-spin" aria-hidden="true" />Submitting…</>
                ) : (
                    <><CheckCircle2 className="h-5 w-5" aria-hidden="true" />Continue — confirm email</>
                )}
            </button>
        </form>
    );
}

const inputCls = (hasError) =>
    `h-12 w-full rounded-md text-slate-900 focus:ring-skillup-blue ${hasError ? 'border-red-400 focus:border-red-500' : 'border-slate-300 focus:border-skillup-blue'}`;

function Field({ id, label, hint, error, optional, children }) {
    return (
        <div>
            <label htmlFor={id} className="mb-1 block text-sm font-semibold text-slate-800">
                {label}
                {optional && <span className="ml-1 text-xs font-normal text-slate-400">(optional)</span>}
            </label>
            {children}
            {hint && !error && <span className="mt-1 block text-xs text-gray-500">{hint}</span>}
            {error && <span id={`${id}-error`} className="mt-1 block text-sm text-red-600" role="alert">{error}</span>}
        </div>
    );
}
