import { Head, Link } from '@inertiajs/react';
import { Award, Compass, FileText, Globe, Lightbulb, Monitor, Target, Users, Wrench } from 'lucide-react';
import PublicLayout from '@/Components/public/PublicLayout';
import Img from '@/Components/Img';
import Breadcrumbs from '@/Components/Breadcrumbs';
import SectionHeading from '@/Components/public/SectionHeading';
import StructuredData from '@/Components/StructuredData';
import MetricCard from '@/Components/public/about/MetricCard';
import ActivationCard from '@/Components/public/about/ActivationCard';
import IconCard from '@/Components/public/IconCard';
import PartnerLogoGrid from '@/Components/public/PartnerLogoGrid';
import CtaBanner from '@/Components/public/CtaBanner';
import { buttonVariants } from '@/Components/ui/button';
import { useRevealScope } from '@/lib/animations';
import { partnerLogos } from '@/data/site';
import { cn } from '@/lib/utils';

// Verified impact — derived from real in-person activations (384 + 117 + 327 = 828).
const metrics = [
    { value: 828, suffix: '+', label: 'Learners reached', description: 'Trained hands-on across our in-person activations.' },
    { value: 3, suffix: '', label: 'Communities activated', description: 'Warri, Port Harcourt, and Redemption Camp.' },
    { value: 4, suffix: '+', label: 'Years of impact', description: 'Growing practical tech talent since our first cohort.' },
];

const values = [
    { icon: Wrench, title: 'Practical over theory', text: 'We teach by building — every learner ships real projects, not just notes.' },
    { icon: Users, title: 'Community first', text: 'Learning happens together, with mentors, peers, and an alumni network.' },
    { icon: Globe, title: 'Access & opportunity', text: 'We meet learners where they are and open doors across Africa.' },
    { icon: Award, title: 'Excellence & mentorship', text: 'Expert facilitators and hands-on support at every step of the journey.' },
];

const reasons = [
    { icon: Monitor, title: 'Promoting digital education', text: 'Programs build foundational and advanced tech skills for the modern workplace.' },
    { icon: Lightbulb, title: 'Innovative workshops', text: 'Learners apply concepts through practical work, live sessions, and emerging-tech exposure.' },
    { icon: FileText, title: 'Online resource hub', text: 'The platform brings together resources, templates, insights, support, and guidance.' },
    { icon: Users, title: 'Pan-African network', text: 'Community and cohort support keep learners connected beyond individual lessons.' },
];

const activations = [
    { title: 'SkillUp Warri', location: 'Warri, Delta State', learners: '384', courses: '4', text: 'A flagship South-South initiative with hands-on digital skills and entrepreneurship training.', image: '/images/studentss.JPG' },
    { title: 'SkillUp Port Harcourt', location: 'Port Harcourt, Rivers State', learners: '117', courses: '4', text: 'An intensive learning activation for aspiring tech professionals and entrepreneurs in Rivers State.', image: '/images/consistent.jpg' },
    { title: 'SkillUp Redemption Camp', location: 'RCCG Redemption Camp', learners: '327', courses: '4', text: 'A practical program hosted at RECTEM Lecture Halls with mentors, resources, and industry exposure.', image: '/images/beginners.jpg' },
];

export default function About() {
    const scope = useRevealScope();
    const canonical = typeof window !== 'undefined' ? window.location.href : '/about';
    const description = 'SkillUp is building Africa’s next generation of tech talent through practical, mentor-led training — 828+ learners reached across in-person activations and a growing online academy.';

    const schema = [
        {
            '@context': 'https://schema.org',
            '@type': 'BreadcrumbList',
            itemListElement: [
                { '@type': 'ListItem', position: 1, name: 'Home', item: '/' },
                { '@type': 'ListItem', position: 2, name: 'About', item: '/about' },
            ],
        },
    ];

    return (
        <PublicLayout>
            <Head title="About SkillUp">
                <meta head-key="description" name="description" content={description} />
                <link head-key="canonical" rel="canonical" href={canonical} />
                <meta head-key="og:type" property="og:type" content="website" />
                <meta head-key="og:title" property="og:title" content="About SkillUp" />
                <meta head-key="og:description" property="og:description" content={description} />
                <meta head-key="twitter:card" name="twitter:card" content="summary_large_image" />
                <meta head-key="twitter:title" name="twitter:title" content="About SkillUp" />
                <meta head-key="twitter:description" name="twitter:description" content={description} />
            </Head>
            <StructuredData data={schema} />

            <div ref={scope}>
                {/* ─── Hero ─────────────────────────────────────────── */}
                <section className="bg-skillup-navy pb-16 pt-28 text-white sm:pt-32">
                    <div className="mx-auto grid max-w-7xl items-center gap-10 px-4 sm:px-6 lg:grid-cols-[1fr_0.85fr] lg:gap-14 lg:px-8">
                        <div>
                            <Breadcrumbs items={[{ label: 'Home', href: '/' }, { label: 'About' }]} tone="light" />
                            <span data-hero className="mt-5 inline-block text-xs font-bold uppercase tracking-[0.14em] text-blue-300">About SkillUp</span>
                            <h1 data-hero className="mt-3 max-w-2xl text-4xl font-bold leading-[1.1] tracking-tight sm:text-5xl">
                                Building Africa’s next generation of tech talent
                            </h1>
                            <p data-hero className="mt-5 max-w-xl text-lg leading-8 text-blue-100">
                                SkillUp combines expert-led training, hands-on projects, and career support to turn ambition
                                into practical, job-ready tech skills — wherever learners are starting from.
                            </p>
                            <div data-hero className="mt-8 flex flex-col gap-3 sm:flex-row">
                                <Link href="/courses" className={cn(buttonVariants({ size: 'lg' }), 'bg-white text-skillup-navy hover:bg-blue-50')}>Explore courses</Link>
                                <Link href="/community" className={cn(buttonVariants({ variant: 'secondary', size: 'lg' }), 'bg-white/10 text-white ring-1 ring-inset ring-white/30 hover:bg-white/20')}>Join our community</Link>
                            </div>
                        </div>
                        <div className="relative" data-hero>
                            <Img src="/images/Facilitators.jpg" alt="SkillUp learners collaborating on tech projects" className="aspect-[4/3] w-full rounded-2xl object-cover shadow-2xl" eager />
                        </div>
                    </div>
                </section>

                {/* ─── Impact metrics ───────────────────────────────── */}
                <section className="bg-white py-16 sm:py-20">
                    <div className="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
                        <SectionHeading data-reveal eyebrow="Our impact" title="Real outcomes, real communities" description="Every number here comes from learners we’ve trained on the ground." />
                        <div data-reveal-group className="mt-12 grid gap-6 sm:grid-cols-3">
                            {metrics.map((metric) => (
                                <MetricCard key={metric.label} {...metric} />
                            ))}
                        </div>
                    </div>
                </section>

                {/* ─── Our story ────────────────────────────────────── */}
                <section className="bg-slate-50 py-16 sm:py-20">
                    <div className="mx-auto grid max-w-7xl items-center gap-12 px-4 sm:px-6 lg:grid-cols-2 lg:gap-16 lg:px-8">
                        <div data-reveal>
                            <SectionHeading align="left" eyebrow="Our story" title="From local activations to one academy platform" />
                            <div className="mt-6 space-y-4 text-lg leading-8 text-slate-600">
                                <p>SkillUp began on the ground — running hands-on activations in communities across Nigeria, teaching practical digital skills to learners who were ready to build.</p>
                                <p>Those activations reached hundreds of people in Warri, Port Harcourt, and the Redemption Camp, proving that with the right training and mentorship, African talent can compete anywhere.</p>
                                <p>Today we’re bringing that experience into one integrated academy — expert-led courses, cohort learning, and career support, accessible online to learners across the continent.</p>
                            </div>
                        </div>
                        <div data-reveal>
                            <Img src="/images/skill_up.png" alt="A SkillUp learner celebrating progress" className="aspect-[4/3] w-full rounded-2xl object-cover shadow-xl" loading="lazy" />
                        </div>
                    </div>
                </section>

                {/* ─── Mission, vision & values ─────────────────────── */}
                <section className="bg-white py-16 sm:py-20">
                    <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                        <div data-reveal-group className="grid gap-6 lg:grid-cols-2">
                            <div className="rounded-2xl border border-skillup-blue/20 bg-skillup-soft p-8">
                                <div className="flex h-12 w-12 items-center justify-center rounded-xl bg-skillup-blue text-white"><Target className="h-6 w-6" aria-hidden="true" /></div>
                                <h2 className="mt-5 text-2xl font-bold text-skillup-navy">Our mission</h2>
                                <p className="mt-3 text-base leading-7 text-slate-600">To make practical, career-focused tech education accessible to every African learner — and connect that learning to real opportunity.</p>
                            </div>
                            <div className="rounded-2xl border border-skillup-blue/20 bg-skillup-soft p-8">
                                <div className="flex h-12 w-12 items-center justify-center rounded-xl bg-skillup-navy text-white"><Compass className="h-6 w-6" aria-hidden="true" /></div>
                                <h2 className="mt-5 text-2xl font-bold text-skillup-navy">Our vision</h2>
                                <p className="mt-3 text-base leading-7 text-slate-600">A continent where anyone can build a thriving career in tech, no matter where they start.</p>
                            </div>
                        </div>

                        <div className="mt-14">
                            <SectionHeading data-reveal eyebrow="What we value" title="The principles behind everything we build" />
                            <div data-reveal-group className="mt-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                                {values.map((value) => (
                                    <IconCard key={value.title} {...value} />
                                ))}
                            </div>
                        </div>
                    </div>
                </section>

                {/* ─── Why learn with us ────────────────────────────── */}
                <section className="bg-slate-50 py-16 sm:py-20">
                    <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                        <SectionHeading data-reveal eyebrow="Why SkillUp" title="Practical training, community, and a real platform path" />
                        <div data-reveal-group className="mt-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                            {reasons.map((reason) => (
                                <IconCard key={reason.title} {...reason} />
                            ))}
                        </div>
                    </div>
                </section>

                {/* ─── Past activations ─────────────────────────────── */}
                <section className="bg-white py-16 sm:py-20">
                    <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                        <SectionHeading data-reveal eyebrow="Past activations" title="Where we’ve made an impact" description="Real programs, real learners — the foundation the SkillUp platform is built on." />
                        <div data-reveal-group className="mt-10 grid gap-6 md:grid-cols-3">
                            {activations.map((activation) => (
                                <ActivationCard key={activation.title} activation={activation} />
                            ))}
                        </div>
                    </div>
                </section>

                {/* ─── Partners ─────────────────────────────────────── */}
                {partnerLogos.length > 0 && (
                    <section className="bg-slate-50 py-16 sm:py-20">
                        <div className="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
                            <SectionHeading data-reveal eyebrow="Partners" title="Organizations we’ve worked with" />
                            <div className="mt-10" data-reveal>
                                <PartnerLogoGrid logos={partnerLogos} />
                            </div>
                        </div>
                    </section>
                )}

                {/* ─── Final CTA ────────────────────────────────────── */}
                <CtaBanner
                    heading="Ready to start your tech journey?"
                    description="Explore practical, mentor-led courses built to take you from beginner to job-ready."
                    primary={{ label: 'Explore courses', href: '/courses' }}
                    secondary={{ label: 'Contact us', href: '/contact' }}
                />
            </div>
        </PublicLayout>
    );
}
