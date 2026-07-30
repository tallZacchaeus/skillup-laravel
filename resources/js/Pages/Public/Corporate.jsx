import { Head, Link } from '@inertiajs/react';
import { ArrowRight, Award, BadgeCheck, Check, Globe, Layers, Target, UsersRound, Wrench } from 'lucide-react';
import PublicLayout from '@/Components/public/PublicLayout';
import Img from '@/Components/Img';
import Breadcrumbs from '@/Components/Breadcrumbs';
import SectionHeading from '@/Components/public/SectionHeading';
import FaqAccordion from '@/Components/public/FaqAccordion';
import CorporateQuoteForm from '@/Components/public/corporate/CorporateQuoteForm';
import { buttonVariants } from '@/Components/ui/button';
import { useRevealScope } from '@/lib/animations';
import { cn } from '@/lib/utils';

const benefits = [
    { icon: Target, title: 'Tailored curriculum', text: 'Programs built around your team’s roles, goals, and current skill levels.' },
    { icon: UsersRound, title: 'Expert facilitators', text: 'Learn from practitioners who have built products and led teams across African tech.' },
    { icon: Wrench, title: 'Practical, project-based', text: 'Every track is hands-on — your team builds real projects, not just theory.' },
    { icon: Globe, title: 'Flexible online delivery', text: 'Cohort-based training your team can join from anywhere, scheduled with you.' },
    { icon: Layers, title: 'Beginner to advanced', text: 'Structured levels from foundations to advanced, matched to each learner.' },
    { icon: Award, title: 'Certificates on completion', text: 'Learners earn a SkillUp certificate they can share and verify.' },
];

const offerings = [
    'Curriculum drawn from our practical tech tracks',
    'Role-based focus for your team',
    'Beginner to advanced skill levels',
    'Online, cohort-based delivery',
    'Scheduling arranged with your team',
    'Certificates on completion',
];

const process = [
    { title: 'Share your training needs', text: 'Tell us your team size, roles, and the skills you want to build.' },
    { title: 'Receive a tailored proposal', text: 'We prepare a curriculum, timeline, and quote matched to your goals.' },
    { title: 'Confirm curriculum & schedule', text: 'Refine the plan together and agree on delivery dates.' },
    { title: 'Onboard your learners', text: 'We enrol your team and get everyone set up to start.' },
    { title: 'Review progress & outcomes', text: 'Your dedicated contact reviews participation and results with you.' },
];

const faqs = [
    {
        question: 'Can the training be customized for our organization?',
        answer: 'Yes. We build each program around your team’s roles, goals, and skill levels, drawing on our practical tech tracks.',
    },
    {
        question: 'How is the training delivered?',
        answer: 'Training is online and cohort-based, so your team can join from anywhere. Specific scheduling is arranged with you as part of your proposal.',
    },
    {
        question: 'What team sizes can you accommodate?',
        answer: 'From small teams to large groups. Share your estimated learner numbers in the form and we’ll tailor a proposal to fit.',
    },
    {
        question: 'Can learners be trained at different skill levels?',
        answer: 'Yes. Our tracks run from beginner foundations through to advanced, so learners are matched to the right level.',
    },
    {
        question: 'Do you provide certificates?',
        answer: 'Yes. Learners earn a SkillUp certificate on completion that they can share and verify.',
    },
    {
        question: 'How does pricing work?',
        answer: 'Pricing is based on your team size, the tracks you choose, and program scope. Request a quote and we’ll prepare a tailored proposal.',
    },
];

// Descriptive, self-evident audiences for each track (positioning copy, not claims).
const trackAudience = {
    'product-management': 'Product, project & delivery teams',
    'software-development': 'Aspiring & junior developers',
    'product-design': 'Designers & product teams',
    'virtual-assistance': 'Operations & admin support',
    'data-analysis': 'Analysts, ops & finance teams',
};

export default function Corporate({ tracks = [] }) {
    const scope = useRevealScope();

    return (
        <PublicLayout>
            <Head title="Corporate Training — SkillUp" />

            <div ref={scope}>
                {/* ─── Hero ─────────────────────────────────────────── */}
                <section className="bg-skillup-navy pb-16 pt-28 text-white sm:pt-32">
                    <div className="mx-auto grid max-w-7xl items-center gap-10 px-4 sm:px-6 lg:grid-cols-2 lg:gap-16 lg:px-8">
                        <div>
                            <Breadcrumbs items={[{ label: 'Home', href: '/' }, { label: 'Corporate Training' }]} tone="light" />
                            <h1 className="mt-5 max-w-xl text-4xl font-bold leading-[1.1] tracking-tight sm:text-5xl">
                                Build the digital capabilities your team needs to perform and grow
                            </h1>
                            <p className="mt-5 max-w-xl text-lg leading-8 text-blue-100">
                                Customized technology training for organizations — with practical, project-based learning,
                                flexible online delivery, and structured support.
                            </p>
                            <div className="mt-8 flex flex-col gap-3 sm:flex-row">
                                <a href="#inquiry-form" className={cn(buttonVariants({ size: 'lg' }), 'bg-white text-skillup-navy hover:bg-blue-50')}>
                                    Request a corporate training quote
                                </a>
                                <a href="#tracks" className={cn(buttonVariants({ variant: 'secondary', size: 'lg' }), 'bg-white/10 text-white ring-1 ring-inset ring-white/30 hover:bg-white/20')}>
                                    Explore training tracks
                                </a>
                            </div>
                        </div>
                        <div className="relative">
                            <Img
                                src="/images/Facilitators.jpg"
                                alt="A team collaborating during a training session"
                                className="aspect-[4/3] w-full rounded-2xl object-cover shadow-2xl"
                                eager
                            />
                        </div>
                    </div>
                </section>

                {/* ─── Benefits ─────────────────────────────────────── */}
                <section className="bg-white py-16 sm:py-20">
                    <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                        <SectionHeading
                            data-reveal
                            eyebrow="Why SkillUp for teams"
                            title="Training that turns into on-the-job capability"
                            description="Everything is built around real outcomes for your organization — not generic courseware."
                        />
                        <div data-reveal-group className="mt-14 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                            {benefits.map((benefit) => (
                                <div
                                    key={benefit.title}
                                    className="group flex h-full flex-col rounded-2xl border border-slate-200 bg-white p-6 shadow-card transition-all duration-300 hover:-translate-y-1 hover:border-skillup-blue/40 hover:shadow-card-hover"
                                >
                                    <div className="flex h-12 w-12 items-center justify-center rounded-xl bg-skillup-blue/10 text-skillup-blue transition-transform duration-300 group-hover:scale-110">
                                        <benefit.icon className="h-6 w-6" aria-hidden="true" />
                                    </div>
                                    <h3 className="mt-5 text-lg font-bold text-skillup-navy">{benefit.title}</h3>
                                    <p className="mt-2 text-sm leading-6 text-slate-600">{benefit.text}</p>
                                </div>
                            ))}
                        </div>
                    </div>
                </section>

                {/* ─── Training tracks ──────────────────────────────── */}
                {tracks.length > 0 && (
                    <section id="tracks" className="scroll-mt-24 bg-skillup-soft py-16 sm:py-20">
                        <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                            <SectionHeading
                                data-reveal
                                eyebrow="Available tracks"
                                title="Training areas we can build your program around"
                                description="Every track is practical and project-based. We combine and tailor them to fit your team."
                            />
                            <div data-reveal-group className="mt-14 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                                {tracks.map((track) => (
                                    <article key={track.slug} className="flex h-full flex-col rounded-2xl border border-slate-200 bg-white p-6 shadow-card transition-all duration-300 hover:-translate-y-1 hover:shadow-card-hover">
                                        <h3 className="text-lg font-bold text-skillup-navy">{track.title}</h3>
                                        {trackAudience[track.slug] && (
                                            <p className="mt-1 text-xs font-semibold uppercase tracking-wide text-skillup-blue">
                                                {trackAudience[track.slug]}
                                            </p>
                                        )}
                                        {track.summary && <p className="mt-3 flex-1 text-sm leading-6 text-slate-600">{track.summary}</p>}
                                        {track.skills?.length > 0 && (
                                            <ul className="mt-4 flex flex-wrap gap-1.5">
                                                {track.skills.map((skill) => (
                                                    <li key={skill} className="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-medium text-slate-600">
                                                        {skill}
                                                    </li>
                                                ))}
                                            </ul>
                                        )}
                                        <Link
                                            href={track.url}
                                            className="mt-5 inline-flex items-center gap-1 text-sm font-semibold text-skillup-blue hover:underline focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-skillup-blue/40"
                                        >
                                            View track
                                            <ArrowRight className="h-4 w-4" aria-hidden="true" />
                                        </Link>
                                    </article>
                                ))}
                            </div>
                            <div className="mt-12 text-center" data-reveal>
                                <Link href="/courses" className={cn(buttonVariants({ variant: 'outline' }))}>
                                    Explore all training tracks
                                    <ArrowRight className="h-4 w-4" aria-hidden="true" />
                                </Link>
                            </div>
                        </div>
                    </section>
                )}

                {/* ─── Delivery & customization ─────────────────────── */}
                <section className="bg-white py-16 sm:py-20">
                    <div className="mx-auto grid max-w-7xl items-center gap-12 px-4 sm:px-6 lg:grid-cols-2 lg:gap-16 lg:px-8">
                        <div data-reveal>
                            <SectionHeading
                                align="left"
                                eyebrow="Delivery & customization"
                                title="A program shaped around your organization"
                                description="We tailor the essentials to your team, then agree scope, schedule, and focus together as part of your quote."
                            />
                        </div>
                        <ul data-reveal-group className="grid gap-3 sm:grid-cols-2">
                            {offerings.map((item) => (
                                <li key={item} className="flex items-start gap-3 rounded-xl border border-slate-200 bg-slate-50 p-4">
                                    <span className="mt-0.5 flex h-6 w-6 flex-shrink-0 items-center justify-center rounded-full bg-emerald-100 text-emerald-600">
                                        <Check className="h-4 w-4" aria-hidden="true" />
                                    </span>
                                    <span className="text-sm font-medium text-skillup-navy">{item}</span>
                                </li>
                            ))}
                        </ul>
                    </div>
                </section>

                {/* ─── How it works ─────────────────────────────────── */}
                <section className="bg-skillup-soft py-16 sm:py-20">
                    <div className="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
                        <SectionHeading
                            data-reveal
                            eyebrow="How it works"
                            title="From first conversation to measurable outcomes"
                            description="A simple, structured path to getting your team trained."
                        />
                        <ol data-reveal-group className="mt-14 grid gap-6 sm:grid-cols-2 lg:grid-cols-5">
                            {process.map((step, index) => (
                                <li key={step.title} className="relative rounded-2xl border border-slate-200 bg-white p-6 shadow-card">
                                    <span className="flex h-10 w-10 items-center justify-center rounded-full bg-skillup-blue text-sm font-bold text-white">
                                        {index + 1}
                                    </span>
                                    <h3 className="mt-4 text-base font-bold text-skillup-navy">{step.title}</h3>
                                    <p className="mt-2 text-sm leading-6 text-slate-600">{step.text}</p>
                                </li>
                            ))}
                        </ol>
                    </div>
                </section>

                {/* ─── Corporate FAQ ────────────────────────────────── */}
                <section className="bg-white py-16 sm:py-20">
                    <div className="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
                        <SectionHeading
                            data-reveal
                            eyebrow="Corporate FAQ"
                            title="Questions from L&D and HR teams"
                        />
                        <div className="mt-12" data-reveal>
                            <FaqAccordion items={faqs} />
                        </div>
                    </div>
                </section>

                {/* ─── Quote form ───────────────────────────────────── */}
                <section id="inquiry-form" className="scroll-mt-24 border-t border-slate-200 bg-slate-50 py-16 sm:py-20">
                    <div className="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
                        <div className="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
                            <div className="text-center">
                                <span className="inline-flex items-center gap-1.5 rounded-full bg-skillup-blue/10 px-3 py-1 text-xs font-semibold text-skillup-blue">
                                    <BadgeCheck className="h-3.5 w-3.5" aria-hidden="true" />
                                    Corporate training inquiry
                                </span>
                                <h2 className="mt-4 text-2xl font-bold text-skillup-navy sm:text-3xl">Request a corporate training quote</h2>
                                <p className="mx-auto mt-2 max-w-lg text-sm leading-6 text-slate-600">
                                    Share your team size and training objectives, and we’ll prepare a tailored proposal.
                                </p>
                            </div>
                            <div className="mt-8">
                                <CorporateQuoteForm tracks={tracks} />
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </PublicLayout>
    );
}
