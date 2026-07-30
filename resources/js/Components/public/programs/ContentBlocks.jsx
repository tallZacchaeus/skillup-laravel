import {
    Award, BookOpen, Brain, CheckCircle2, Code2, Cpu, Gift, Laptop, MapPin,
    Quote, Rocket, Sparkles, Star, Trophy, Users,
} from 'lucide-react';
import Img from '@/Components/Img';
import FaqAccordion from '@/Components/public/FaqAccordion';

/**
 * Renders a program edition's `content` JSON — an ordered array of typed
 * blocks edited in Filament. One component per block type; unknown types
 * are skipped so old pages never crash on new block vocabularies.
 *
 * Blocks that depend on admin-entered data (testimonials, logos, stats,
 * gallery) render nothing when empty — the page never shows a placeholder or
 * invented content.
 */
export default function ContentBlocks({ blocks = [], edition, tracks, onRegister }) {
    return blocks.map((block, index) => {
        const Component = BLOCKS[block.type];

        if (!Component) {
            return null;
        }

        return <Component key={`${block.type}-${index}`} data={block.data ?? {}} edition={edition} tracks={tracks} onRegister={onRegister} />;
    });
}

const BLOCKS = {
    quick_facts: QuickFacts,
    overview: Overview,
    stats: Stats,
    why: WhyGrid,
    tracks: TracksGrid,
    journey: Journey,
    includes: Includes,
    team: Team,
    testimonials: Testimonials,
    gallery: Gallery,
    logos: Logos,
    faqs: Faqs,
    venue: Venue,
    event: EventLink,
    cta: ClosingCta,
};

// ---------- shared helpers ----------

function SectionHeading({ eyebrow, title, subtitle }) {
    return (
        <div className="mb-10 text-center" data-reveal>
            {eyebrow && <p className="mb-2 text-sm font-semibold uppercase tracking-wide text-skillup-blue">{eyebrow}</p>}
            <h2 className="text-3xl font-bold text-skillup-navy sm:text-4xl">{title}</h2>
            {subtitle && <p className="mx-auto mt-3 max-w-2xl text-base text-gray-600 sm:text-lg">{subtitle}</p>}
        </div>
    );
}

const money = (currency, n) => `${currency ? `${currency} ` : ''}${Number(n).toLocaleString()}`;

// Numbers embedded in a fact/stat string get the count-up treatment; anything
// non-numeric is shown verbatim.
function CountValue({ value, className }) {
    const match = typeof value === 'string' ? value.match(/^(\D*)([\d,]+)(\D*)$/) : null;
    if (!match) {
        return <span className={className}>{value}</span>;
    }
    const [, prefix, digits, suffix] = match;
    return (
        <span className={className}>
            {prefix}
            <span data-count={digits.replace(/,/g, '')}>0</span>
            {suffix}
        </span>
    );
}

const INCLUDE_ICONS = [
    [/certificat|award|badge/i, Award],
    [/laptop|device|computer|kit|hardware/i, Laptop],
    [/project|build|portfolio/i, Rocket],
    [/mentor|support|facilitat|tutor|guid/i, Users],
    [/material|resource|book|curriculum|note/i, BookOpen],
    [/showcase|demo|graduat|prize|trophy/i, Trophy],
    [/gift|snack|meal|lunch|refresh|swag/i, Gift],
    [/code|python|ai|coding/i, Code2],
];

function includeIcon(text) {
    const found = INCLUDE_ICONS.find(([re]) => re.test(String(text)));
    return found ? found[1] : CheckCircle2;
}

const TRACK_ICONS = [Sparkles, Rocket, Cpu, Brain];

// ---------- blocks ----------

function QuickFacts({ data }) {
    const facts = data.items ?? [];

    return (
        <section className="bg-white py-10">
            <div className="mx-auto grid max-w-7xl grid-cols-2 gap-4 px-4 sm:px-6 md:grid-cols-3 lg:grid-cols-6 lg:px-8" data-reveal-group>
                {facts.map((fact) => (
                    <div key={fact.label} className="rounded-xl bg-skillup-soft p-4 text-center transition-transform duration-300 hover:-translate-y-1">
                        <div className="text-xs font-semibold uppercase tracking-wide text-gray-500">{fact.label}</div>
                        <CountValue value={fact.value} className="mt-1 block text-sm font-bold leading-5 text-skillup-navy" />
                    </div>
                ))}
            </div>
        </section>
    );
}

function Overview({ data }) {
    return (
        <section className="bg-white py-16">
            <div className="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
                <SectionHeading title={data.title ?? 'Programme Overview'} />
                <p className="text-lg leading-8 text-gray-600" data-reveal>
                    {data.body}
                </p>
            </div>
        </section>
    );
}

// Big animated programme statistics — only when an admin has entered them.
function Stats({ data }) {
    const items = data.items ?? [];
    if (!items.length) {
        return null;
    }

    return (
        <section className="bg-skillup-deep py-16">
            <div className="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
                {data.title && (
                    <h2 className="mb-10 text-center text-3xl font-bold text-white sm:text-4xl" data-reveal>{data.title}</h2>
                )}
                <div className="grid grid-cols-2 gap-6 lg:grid-cols-4" data-reveal-group>
                    {items.map((item) => (
                        <div key={item.label} className="text-center">
                            <CountValue value={item.value} className="block text-4xl font-bold text-white sm:text-5xl" />
                            <div className="mt-2 text-sm font-medium text-blue-200">{item.label}</div>
                        </div>
                    ))}
                </div>
            </div>
        </section>
    );
}

function WhyGrid({ data }) {
    return (
        <section className="bg-skillup-soft py-16">
            <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <SectionHeading title={data.title ?? 'Why This Programme'} subtitle={data.subtitle} />
                <div className="grid gap-6 sm:grid-cols-2 lg:grid-cols-3" data-reveal-group>
                    {(data.items ?? []).map((item) => (
                        <div key={item.title} className="rounded-2xl bg-white p-6 shadow-card transition-all duration-300 hover:-translate-y-1 hover:shadow-card-hover">
                            <span className="mb-4 flex h-12 w-12 items-center justify-center rounded-xl bg-skillup-blue/10 text-skillup-blue">
                                <CheckCircle2 className="h-6 w-6" aria-hidden="true" />
                            </span>
                            <h3 className="mb-2 text-lg font-semibold text-gray-900">{item.title}</h3>
                            <p className="text-sm leading-6 text-gray-600">{item.text}</p>
                        </div>
                    ))}
                </div>
            </div>
        </section>
    );
}

function TracksGrid({ data, tracks, edition, onRegister }) {
    return (
        <section className="bg-white py-16" id="tracks">
            <div className="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
                <SectionHeading title={data.title ?? 'Choose a Track'} subtitle={data.subtitle} />
                <div className="grid gap-8 md:grid-cols-2" data-reveal-group>
                    {tracks.map((track, index) => (
                        <TrackCard key={track.id} track={track} index={index} edition={edition} onRegister={onRegister} />
                    ))}
                </div>
            </div>
        </section>
    );
}

function TrackCard({ track, index, edition, onRegister }) {
    const Icon = TRACK_ICONS[index % TRACK_ICONS.length];
    const hasDiscount = track.amount !== null && track.discountedAmount !== null && track.discountedAmount < track.amount;
    const savingsPct = hasDiscount ? Math.round(((track.amount - track.discountedAmount) / track.amount) * 100) : 0;

    const cap = track.capacity;
    const rem = track.seatsRemaining;
    const filledPct = cap && rem !== null ? Math.min(100, Math.round(((cap - rem) / cap) * 100)) : null;
    const lowSeats = rem !== null && !track.isFull && rem <= 10;

    return (
        <div className="group relative flex flex-col rounded-2xl border border-blue-200 bg-skillup-soft p-8 shadow-card transition-all duration-300 hover:-translate-y-1.5 hover:border-skillup-blue hover:shadow-card-hover">
            {hasDiscount && (
                <span className="absolute -top-3 right-6 inline-flex items-center gap-1 rounded-full bg-green-600 px-3 py-1 text-xs font-bold text-white shadow-sm">
                    <Star className="h-3.5 w-3.5" aria-hidden="true" />
                    Save {savingsPct}%
                </span>
            )}

            <div className="mb-4 flex items-center justify-between gap-3">
                <span className="flex h-12 w-12 items-center justify-center rounded-xl bg-skillup-blue text-white transition-transform duration-300 group-hover:scale-110">
                    <Icon className="h-6 w-6" aria-hidden="true" />
                </span>
                <span className="inline-flex items-center rounded-full bg-skillup-orange px-4 py-1 text-sm font-bold text-white">
                    Ages {track.ageMin}–{track.ageMax}
                </span>
            </div>

            <h3 className="mb-2 text-2xl font-bold text-skillup-navy">{track.name}</h3>
            <p className="mb-6 flex-1 text-sm leading-6 text-gray-600">{track.summary}</p>

            {/* Seat availability — real capacity vs. remaining */}
            {rem !== null && cap && (
                <div className="mb-6">
                    <div className="mb-1.5 flex items-center justify-between text-xs font-semibold">
                        <span className={track.isFull ? 'text-red-600' : lowSeats ? 'text-amber-600' : 'text-gray-600'}>
                            {track.isFull ? 'Track full — waitlist open' : lowSeats ? `Only ${rem} seats left` : `${rem} of ${cap} seats open`}
                        </span>
                        <span className="text-gray-400">{cap - rem}/{cap} filled</span>
                    </div>
                    <div className="h-2 w-full overflow-hidden rounded-full bg-blue-100" role="progressbar" aria-valuenow={filledPct} aria-valuemin={0} aria-valuemax={100} aria-label={`${track.name} seats filled`}>
                        <span
                            className={`block h-full rounded-full ${track.isFull ? 'bg-red-500' : lowSeats ? 'bg-amber-500' : 'bg-skillup-blue'}`}
                            style={{ width: `${filledPct ?? 0}%` }}
                        />
                    </div>
                </div>
            )}

            <div className="flex items-end justify-between gap-4">
                {track.amount !== null && (
                    <div>
                        {hasDiscount ? (
                            <>
                                <div className="flex items-baseline gap-2">
                                    <span className="text-2xl font-bold text-blue-900">{money(track.currency, track.discountedAmount)}</span>
                                    <span className="text-sm text-gray-500 line-through">{money(track.currency, track.amount)}</span>
                                </div>
                                <span className="text-xs font-semibold text-green-700">You save {money(track.currency, track.amount - track.discountedAmount)}</span>
                            </>
                        ) : (
                            <span className="text-2xl font-bold text-blue-900">{money(track.currency, track.amount)}</span>
                        )}
                    </div>
                )}
                {edition.acceptsRegistrations && (
                    <button
                        type="button"
                        onClick={() => onRegister(track)}
                        className="rounded-md bg-blue-900 px-5 py-2.5 text-sm font-semibold text-white transition-colors hover:bg-blue-700 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-skillup-blue focus-visible:ring-offset-2"
                    >
                        {track.isFull ? 'Join waitlist' : 'Register'}
                    </button>
                )}
            </div>
        </div>
    );
}

function Journey({ data }) {
    const items = data.items ?? [];

    return (
        <section className="bg-skillup-soft py-16">
            <div className="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
                <SectionHeading title={data.title ?? 'The Journey'} subtitle={data.subtitle} />
                <ol className="relative space-y-5 before:absolute before:bottom-10 before:left-[47px] before:top-10 before:w-0.5 before:bg-blue-200" data-reveal-group>
                    {items.map((item, index) => (
                        <li key={item.week} className="relative flex gap-5 rounded-2xl bg-white p-6 shadow-card transition-all duration-300 hover:-translate-y-1 hover:shadow-card-hover">
                            <span className="relative z-10 flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full bg-skillup-blue text-lg font-bold text-white ring-4 ring-skillup-soft">
                                {index + 1}
                            </span>
                            <div className="pt-1">
                                <h3 className="font-bold text-skillup-navy">{item.week}</h3>
                                <p className="mt-1 text-sm leading-6 text-gray-600">{item.focus}</p>
                            </div>
                            {index === items.length - 1 && (
                                <Trophy className="ml-auto hidden h-6 w-6 flex-shrink-0 self-center text-skillup-orange sm:block" aria-hidden="true" />
                            )}
                        </li>
                    ))}
                </ol>
            </div>
        </section>
    );
}

function Includes({ data }) {
    return (
        <section className="bg-white py-16">
            <div className="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
                <SectionHeading title={data.title ?? "What's Included"} subtitle={data.subtitle} />
                <ul className="grid gap-4 sm:grid-cols-2" data-reveal-group>
                    {(data.items ?? []).map((item) => {
                        const ItemIcon = includeIcon(item);
                        return (
                            <li key={item} className="flex items-start gap-4 rounded-xl bg-skillup-soft p-5 transition-all duration-300 hover:-translate-y-1 hover:shadow-card">
                                <span className="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-lg bg-skillup-blue/10 text-skillup-blue">
                                    <ItemIcon className="h-5 w-5" aria-hidden="true" />
                                </span>
                                <span className="pt-1.5 text-sm leading-6 text-gray-700">{item}</span>
                            </li>
                        );
                    })}
                </ul>
            </div>
        </section>
    );
}

function Team({ data }) {
    return (
        <section className="bg-skillup-soft py-16">
            <div className="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
                <SectionHeading title={data.title ?? 'Programme Team'} />
                <div className="space-y-3" data-reveal-group>
                    {(data.items ?? []).map((item) => (
                        <div key={item.role} className="flex items-start gap-4 rounded-xl bg-white p-5 shadow-card transition-all duration-300 hover:-translate-y-1 hover:shadow-card-hover">
                            <Users className="mt-1 h-6 w-6 flex-shrink-0 text-skillup-blue" aria-hidden="true" />
                            <div>
                                <h3 className="font-semibold text-gray-900">{item.role}</h3>
                                <p className="mt-1 text-sm leading-6 text-gray-600">{item.text}</p>
                            </div>
                        </div>
                    ))}
                </div>
            </div>
        </section>
    );
}

// Parent & student testimonials — only when an admin has entered them.
function Testimonials({ data }) {
    const items = data.items ?? [];
    if (!items.length) {
        return null;
    }

    return (
        <section className="bg-white py-16">
            <div className="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
                <SectionHeading eyebrow={data.eyebrow ?? 'What families say'} title={data.title ?? 'Loved by parents and students'} subtitle={data.subtitle} />
                <div className="grid gap-6 md:grid-cols-2 lg:grid-cols-3" data-reveal-group>
                    {items.map((item, index) => (
                        <figure key={index} className="flex h-full flex-col rounded-2xl border border-slate-200 bg-skillup-soft p-6 shadow-card transition-all duration-300 hover:-translate-y-1 hover:shadow-card-hover">
                            <Quote className="h-8 w-8 text-skillup-blue/40" aria-hidden="true" />
                            <blockquote className="mt-3 flex-1 text-sm leading-6 text-gray-700">“{item.quote}”</blockquote>
                            <figcaption className="mt-4 border-t border-slate-200 pt-4">
                                <span className="block font-semibold text-skillup-navy">{item.name}</span>
                                {item.role && <span className="block text-xs text-gray-500">{item.role}</span>}
                            </figcaption>
                        </figure>
                    ))}
                </div>
            </div>
        </section>
    );
}

function Gallery({ data }) {
    const images = data.images ?? [];

    if (!images.length) {
        return null;
    }

    return (
        <section className="bg-white py-16">
            <div className="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
                {data.title && <SectionHeading title={data.title} subtitle={data.subtitle} />}
                <div className="grid grid-cols-2 gap-4 md:grid-cols-3" data-reveal-group>
                    {images.map((image, index) => (
                        <Img
                            key={`${image.src}-${index}`}
                            src={image.src}
                            alt={image.alt ?? ''}
                            loading="lazy"
                            className="h-48 w-full rounded-xl object-cover shadow-card transition-transform duration-300 hover:scale-[1.03] md:h-56"
                        />
                    ))}
                </div>
            </div>
        </section>
    );
}

// Partner / organisation logos — only when an admin has entered them.
function Logos({ data }) {
    const items = data.items ?? [];
    if (!items.length) {
        return null;
    }

    return (
        <section className="bg-skillup-soft py-14">
            <div className="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
                {(data.title ?? 'In partnership with') && (
                    <p className="mb-8 text-center text-sm font-semibold uppercase tracking-wide text-gray-500" data-reveal>
                        {data.title ?? 'In partnership with'}
                    </p>
                )}
                <div className="flex flex-wrap items-center justify-center gap-x-10 gap-y-6" data-reveal-group>
                    {items.map((item, index) => (
                        <Img
                            key={`${item.src ?? item.alt}-${index}`}
                            src={item.src}
                            alt={item.alt ?? 'Partner logo'}
                            loading="lazy"
                            className="h-9 w-auto opacity-70 grayscale transition duration-300 hover:opacity-100 hover:grayscale-0 sm:h-10"
                        />
                    ))}
                </div>
            </div>
        </section>
    );
}

function Faqs({ data }) {
    return (
        <section className="bg-white py-16">
            <div className="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
                <SectionHeading title={data.title ?? 'Frequently Asked Questions'} />
                <div data-reveal>
                    <FaqAccordion items={data.items ?? []} />
                </div>
            </div>
        </section>
    );
}

function Venue({ data, edition }) {
    return (
        <section className="bg-skillup-soft py-16">
            <div className="mx-auto max-w-4xl px-4 text-center sm:px-6 lg:px-8" data-reveal>
                <MapPin className="mx-auto mb-4 h-10 w-10 text-skillup-orange" aria-hidden="true" />
                <h2 className="text-3xl font-bold text-skillup-navy">{data.title ?? 'Venue'}</h2>
                <p className="mt-3 text-lg font-semibold text-gray-800">{edition.venueName}</p>
                <p className="mt-1 text-gray-600">{edition.venueAddress}</p>
                {data.note && <p className="mx-auto mt-3 max-w-xl text-sm text-gray-500">{data.note}</p>}
                {edition.venueMapUrl && (
                    <a
                        href={edition.venueMapUrl}
                        target="_blank"
                        rel="noreferrer"
                        className="mt-5 inline-flex items-center gap-2 rounded-md border-2 border-skillup-blue px-5 py-2.5 text-sm font-semibold text-slate-900 transition-colors hover:bg-blue-900 hover:text-white focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-skillup-blue focus-visible:ring-offset-2"
                    >
                        Open in Maps
                    </a>
                )}
            </div>
        </section>
    );
}

function EventLink({ data }) {
    if (!data.href) {
        return null;
    }

    return (
        <section className="bg-skillup-soft px-4 py-12">
            <div className="mx-auto flex max-w-4xl flex-col items-center gap-4 rounded-2xl border border-blue-200 bg-white p-8 text-center shadow-card" data-reveal>
                <h2 className="text-2xl font-bold text-skillup-navy">{data.title ?? 'Showcase Day'}</h2>
                {data.subtitle && <p className="max-w-xl text-sm leading-6 text-gray-600">{data.subtitle}</p>}
                <a
                    href={data.href}
                    className="inline-flex h-11 items-center justify-center rounded-md bg-blue-900 px-6 text-sm font-semibold text-white transition-colors hover:bg-blue-700 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-skillup-blue focus-visible:ring-offset-2"
                >
                    {data.cta ?? 'View event details'}
                </a>
            </div>
        </section>
    );
}

function ClosingCta({ data, edition, onRegister }) {
    return (
        <section className="px-4 py-12">
            <div className="mx-auto flex max-w-[1296px] flex-col items-center gap-6 rounded-2xl bg-skillup-deep px-6 py-16 text-center" data-reveal>
                <h2 className="text-3xl font-bold text-white sm:text-4xl">{data.title ?? 'Secure a seat'}</h2>
                {data.subtitle && <p className="max-w-2xl text-lg text-blue-100">{data.subtitle}</p>}
                {edition.acceptsRegistrations && (
                    <button
                        type="button"
                        onClick={() => onRegister(null)}
                        className="inline-flex h-12 items-center justify-center rounded-md bg-white px-8 text-base font-semibold text-blue-900 shadow-sm transition hover:bg-blue-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-white focus-visible:ring-offset-2 focus-visible:ring-offset-skillup-deep"
                    >
                        {data.cta ?? 'Register Now'}
                    </button>
                )}
            </div>
        </section>
    );
}
