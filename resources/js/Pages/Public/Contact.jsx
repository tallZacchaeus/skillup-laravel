import { Head, Link } from '@inertiajs/react';
import { ArrowRight, Building2, GraduationCap, Mail, MapPin, MessageSquare, Phone, Users } from 'lucide-react';
import PublicLayout from '@/Components/public/PublicLayout';
import Breadcrumbs from '@/Components/Breadcrumbs';
import ContactForm from '@/Components/public/ContactForm';
import ContactCard from '@/Components/public/ContactCard';
import FaqAccordion from '@/Components/public/FaqAccordion';
import StructuredData from '@/Components/StructuredData';
import { useRevealScope } from '@/lib/animations';

// lucide-react in this project ships no brand icons, so social marks are inline SVGs.
function FacebookIcon(props) {
    return (
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" aria-hidden="true" {...props}>
            <path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z" />
        </svg>
    );
}
function YoutubeIcon(props) {
    return (
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" aria-hidden="true" {...props}>
            <path d="M2.5 17a24.12 24.12 0 0 1 0-10 2 2 0 0 1 1.4-1.4 49.56 49.56 0 0 1 16.2 0A2 2 0 0 1 21.5 7a24.12 24.12 0 0 1 0 10 2 2 0 0 1-1.4 1.4 49.55 49.55 0 0 1-16.2 0A2 2 0 0 1 2.5 17" />
            <path d="m10 15 5-3-5-3z" />
        </svg>
    );
}
function LinkedinIcon(props) {
    return (
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" aria-hidden="true" {...props}>
            <path d="M16 8a6 6 0 0 1 6 6v7h-4v-7a2 2 0 0 0-2-2 2 2 0 0 0-2 2v7h-4V8h4v2.5" />
            <rect width="4" height="12" x="2" y="9" />
            <circle cx="4" cy="4" r="2" />
        </svg>
    );
}

const OFFICE_ADDRESS = 'Dare Adeboye Innovation Hub, Abiona Road, Redemption City, Mowe, Ogun State';
const MAPS_URL = `https://www.google.com/maps/search/?api=1&query=${encodeURIComponent(OFFICE_ADDRESS)}`;

const socials = [
    { label: 'SkillUp on Facebook', href: 'https://www.facebook.com/skillupedtech', icon: FacebookIcon },
    { label: 'SkillUp on YouTube', href: 'https://youtube.com/@theskillupedtech', icon: YoutubeIcon },
    { label: 'SkillUp on LinkedIn', href: 'https://www.linkedin.com/company/theskillupglobal', icon: LinkedinIcon },
];

// Internal destinations only — every link points to a page that exists.
const quickActions = [
    { icon: Building2, title: 'Corporate training', description: 'Upskill your team with a tailored programme and quote.', href: '/corporate' },
    { icon: GraduationCap, title: 'Explore courses', description: 'Browse the full catalogue and find your next skill.', href: '/courses' },
    { icon: Users, title: 'Join the community', description: 'Connect with fellow learners and mentors.', href: '/community' },
];

// Answers reflect verified platform behaviour only.
const faqs = [
    {
        question: 'How do I enrol in a course?',
        answer: 'Browse the catalogue, add a course to your cart, and complete a secure checkout. Once payment is confirmed you gain access and can start learning.',
    },
    {
        question: 'Do you offer corporate or team training?',
        answer: 'Yes. Visit our Corporate Training page to share your team’s needs and request a tailored quote — our team will follow up with a proposal.',
    },
    {
        question: 'Will I receive a certificate?',
        answer: 'Yes. You earn a SkillUp certificate on successful completion of a course, which you can download and share.',
    },
    {
        question: 'How do payments work?',
        answer: 'Payments are processed securely through Paystack. Where an installment option is available for a course, it is shown at checkout.',
    },
    {
        question: 'How long do courses take to complete?',
        answer: 'It varies by course. Each course page shows its structure and, where applicable, cohort dates so you know what to expect before you enrol.',
    },
];

export default function Contact() {
    const scope = useRevealScope();
    const canonical = typeof window !== 'undefined' ? window.location.href : 'https://skillup.com/contact';
    const description = 'Get in touch with SkillUp about courses, corporate training, or partnerships. Email us, call us, or send a message and our team will respond.';

    const schema = [
        {
            '@context': 'https://schema.org',
            '@type': 'ContactPage',
            name: 'Contact SkillUp',
            description,
            url: canonical,
        },
        {
            '@context': 'https://schema.org',
            '@type': 'BreadcrumbList',
            itemListElement: [
                { '@type': 'ListItem', position: 1, name: 'Home', item: 'https://skillup.com/' },
                { '@type': 'ListItem', position: 2, name: 'Contact', item: 'https://skillup.com/contact' },
            ],
        },
    ];

    return (
        <PublicLayout>
            <Head title="Contact SkillUp — Courses, Corporate Training & Support">
                <meta head-key="description" name="description" content={description} />
                <link head-key="canonical" rel="canonical" href={canonical} />
                <meta head-key="og:type" property="og:type" content="website" />
                <meta head-key="og:title" property="og:title" content="Contact SkillUp" />
                <meta head-key="og:description" property="og:description" content={description} />
                <meta head-key="twitter:card" name="twitter:card" content="summary_large_image" />
                <meta head-key="twitter:title" name="twitter:title" content="Contact SkillUp" />
                <meta head-key="twitter:description" name="twitter:description" content={description} />
            </Head>

            <StructuredData data={schema} />

            <div ref={scope}>
                {/* Hero */}
                <section className="bg-skillup-navy pb-16 pt-28 text-white sm:pt-32">
                    <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                        <Breadcrumbs items={[{ label: 'Home', href: '/' }, { label: 'Contact' }]} tone="light" />
                        <span className="mt-5 inline-block text-xs font-bold uppercase tracking-[0.14em] text-blue-300">Contact us</span>
                        <h1 className="mt-3 max-w-2xl text-4xl font-bold leading-[1.1] tracking-tight sm:text-5xl">Let’s talk</h1>
                        <p className="mt-5 max-w-2xl text-lg leading-8 text-blue-100">
                            Questions about our courses, corporate training, or partnerships? Send us a message and our team will
                            get back to you.
                        </p>
                        <div className="mt-8 flex flex-col gap-3 sm:flex-row sm:items-center">
                            <a
                                href="#contact-form"
                                className="inline-flex h-12 items-center justify-center gap-2 rounded-md bg-white px-6 text-base font-semibold text-skillup-navy shadow-sm transition-colors hover:bg-blue-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-white focus-visible:ring-offset-2 focus-visible:ring-offset-skillup-navy"
                            >
                                <MessageSquare className="h-5 w-5" aria-hidden="true" />
                                Send a message
                            </a>
                            <Link
                                href="/courses"
                                className="inline-flex h-12 items-center justify-center gap-2 rounded-md border border-white/30 px-6 text-base font-semibold text-white transition-colors hover:bg-white/10 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-white focus-visible:ring-offset-2 focus-visible:ring-offset-skillup-navy"
                            >
                                Browse courses
                                <ArrowRight className="h-5 w-5" aria-hidden="true" />
                            </Link>
                        </div>
                    </div>
                </section>

                {/* Contact cards */}
                <section className="bg-slate-50 py-16 sm:py-20">
                    <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                        <div className="grid gap-6 sm:grid-cols-2 lg:grid-cols-3" data-reveal-group>
                            <ContactCard
                                icon={Mail}
                                title="Email us"
                                value="skilluplimited@gmail.com"
                                description="For general enquiries and support."
                                href="mailto:skilluplimited@gmail.com"
                            />
                            <ContactCard
                                icon={Phone}
                                title="Call us"
                                value="+234 704 030 9594"
                                description="Speak with our team directly."
                                href="tel:+2347040309594"
                            />
                            <ContactCard icon={MapPin} title="Visit us" value={OFFICE_ADDRESS}>
                                <a
                                    href={MAPS_URL}
                                    target="_blank"
                                    rel="noreferrer"
                                    className="mt-3 inline-flex items-center gap-1 text-sm font-semibold text-skillup-blue hover:underline focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-skillup-blue/40"
                                >
                                    Get directions
                                    <ArrowRight className="h-4 w-4" aria-hidden="true" />
                                </a>
                            </ContactCard>
                        </div>
                    </div>
                </section>

                {/* Form + quick actions */}
                <section className="bg-white pb-16 sm:pb-20">
                    <div className="mx-auto grid max-w-7xl gap-8 px-4 sm:px-6 lg:grid-cols-[1.15fr_0.85fr] lg:gap-12 lg:px-8">
                        {/* Form */}
                        <div id="contact-form" className="scroll-mt-28 rounded-2xl border border-slate-200 bg-white p-6 shadow-card sm:p-8">
                            <h2 className="text-2xl font-bold text-skillup-navy">Send us a message</h2>
                            <p className="mt-2 text-sm leading-6 text-slate-600">
                                Fill in the form below and we’ll be in touch as soon as we can. Fields marked with * are required.
                            </p>
                            <div className="mt-6">
                                <ContactForm />
                            </div>
                        </div>

                        {/* Quick actions + social */}
                        <aside className="space-y-6" data-reveal>
                            <div className="rounded-2xl border border-slate-200 bg-slate-50 p-6 sm:p-7">
                                <h2 className="text-lg font-bold text-skillup-navy">Quick links</h2>
                                <p className="mt-1 text-sm leading-6 text-slate-600">Prefer to explore first? Jump straight to what you need.</p>
                                <ul className="mt-5 space-y-3">
                                    {quickActions.map((action) => (
                                        <li key={action.href}>
                                            <Link
                                                href={action.href}
                                                className="group flex items-start gap-4 rounded-xl border border-slate-200 bg-white p-4 transition-all duration-300 hover:-translate-y-0.5 hover:border-skillup-blue/40 hover:shadow-card focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-skillup-blue/40"
                                            >
                                                <span className="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-lg bg-skillup-blue/10 text-skillup-blue">
                                                    <action.icon className="h-5 w-5" aria-hidden="true" />
                                                </span>
                                                <span className="flex-1">
                                                    <span className="flex items-center gap-1 font-semibold text-skillup-navy">
                                                        {action.title}
                                                        <ArrowRight className="h-4 w-4 text-slate-400 transition-transform duration-300 group-hover:translate-x-1 group-hover:text-skillup-blue" aria-hidden="true" />
                                                    </span>
                                                    <span className="mt-0.5 block text-sm leading-6 text-slate-600">{action.description}</span>
                                                </span>
                                            </Link>
                                        </li>
                                    ))}
                                </ul>
                            </div>

                            <div className="rounded-2xl border border-slate-200 bg-white p-6 shadow-card sm:p-7">
                                <p className="text-sm font-semibold text-slate-700">Connect with us</p>
                                <p className="mt-1 text-sm leading-6 text-slate-600">Follow SkillUp for updates, stories, and new courses.</p>
                                <div className="mt-4 flex gap-3">
                                    {socials.map((social) => (
                                        <a
                                            key={social.label}
                                            href={social.href}
                                            target="_blank"
                                            rel="noreferrer"
                                            aria-label={social.label}
                                            className="inline-flex h-11 w-11 items-center justify-center rounded-full text-slate-600 transition-colors hover:bg-skillup-blue/10 hover:text-skillup-blue focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-skillup-blue/40"
                                        >
                                            <social.icon className="h-5 w-5" aria-hidden="true" />
                                        </a>
                                    ))}
                                </div>
                            </div>
                        </aside>
                    </div>
                </section>

                {/* FAQ */}
                <section className="bg-slate-50 py-16 sm:py-20">
                    <div className="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
                        <div className="text-center" data-reveal>
                            <span className="inline-block text-xs font-bold uppercase tracking-[0.14em] text-skillup-blue">FAQ</span>
                            <h2 className="mt-3 text-3xl font-bold tracking-tight text-skillup-navy sm:text-4xl">Frequently asked questions</h2>
                            <p className="mx-auto mt-3 max-w-xl text-base leading-7 text-slate-600">
                                Quick answers to the questions we hear most. Can’t find yours? Send us a message above.
                            </p>
                        </div>
                        <div className="mt-10">
                            <FaqAccordion items={faqs} />
                        </div>
                    </div>
                </section>
            </div>
        </PublicLayout>
    );
}
