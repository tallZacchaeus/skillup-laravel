import { Head } from '@inertiajs/react';
import {
    Award, BriefcaseBusiness, CalendarDays, CheckCircle2, GraduationCap, HeartHandshake,
    Lightbulb, MessagesSquare, Rocket, ShieldCheck, Sparkles, Star, UserPlus, Users, UsersRound,
} from 'lucide-react';
import PublicLayout from '@/Components/public/PublicLayout';
import StructuredData from '@/Components/StructuredData';
import FaqAccordion from '@/Components/public/FaqAccordion';
import CtaBanner from '@/Components/public/CtaBanner';
import EventCard from '@/Components/public/events/EventCard';
import CommunityHero from '@/Components/public/community/CommunityHero';
import BenefitCard from '@/Components/public/community/BenefitCard';
import JourneyTimeline from '@/Components/public/community/JourneyTimeline';
import SuccessStoryCard from '@/Components/public/community/SuccessStoryCard';
import { useRevealScope } from '@/lib/animations';

const BENEFITS = [
    { icon: UsersRound, title: 'Peer learning', text: 'Learn faster alongside people working toward the same goals — swap ideas, unblock each other, and stay motivated.' },
    { icon: HeartHandshake, title: 'Mentorship', text: 'Get direction from experienced members and mentors who have walked the path before you.' },
    { icon: Star, title: 'Portfolio feedback', text: 'Share your work in progress and get honest, constructive feedback that makes it stronger.' },
    { icon: Users, title: 'Networking', text: 'Build genuine relationships with learners, alumni, and professionals across the ecosystem.' },
    { icon: BriefcaseBusiness, title: 'Career opportunities', text: 'Hear about openings, referrals, and opportunities shared within the community.' },
    { icon: CalendarDays, title: 'Events & meetups', text: 'Join live sessions, workshops, and meetups that go beyond the course material.' },
];

const EXPERIENCE = [
    { icon: MessagesSquare, text: 'Ask questions and get help when you are stuck — no question is too small.' },
    { icon: Rocket, text: 'Share the projects you are building and celebrate wins together.' },
    { icon: Lightbulb, text: 'Get guidance from mentors on your learning path and career.' },
    { icon: Users, text: 'Collaborate with peers on ideas, study groups, and challenges.' },
    { icon: GraduationCap, text: 'Keep learning and stay connected long after you finish a course.' },
];

const JOURNEY = [
    { icon: UserPlus, title: 'Enrol', text: 'Pick a course and create your account.' },
    { icon: Lightbulb, title: 'Learn', text: 'Build skills through hands-on lessons.' },
    { icon: UsersRound, title: 'Join community', text: 'Meet peers and mentors.' },
    { icon: MessagesSquare, title: 'Collaborate', text: 'Share, ask, and build together.' },
    { icon: GraduationCap, title: 'Graduate', text: 'Finish with real, portfolio-ready work.' },
    { icon: Award, title: 'Become alumni', text: 'Stay part of the network.' },
    { icon: HeartHandshake, title: 'Mentor others', text: 'Give back and guide the next cohort.' },
];

const GUIDELINES = [
    { icon: HeartHandshake, title: 'Be respectful', text: 'Treat everyone with kindness. We are all here to learn and grow.' },
    { icon: Users, title: 'Collaborate openly', text: 'Share what you know and help others move forward.' },
    { icon: Sparkles, title: 'Stay inclusive', text: 'Everyone is welcome, whatever their background or level.' },
    { icon: CheckCircle2, title: 'Give constructive feedback', text: 'Be honest and encouraging — feedback should help, not discourage.' },
];

const FAQS = [
    { question: 'Who can join the community?', answer: 'The community is for SkillUp learners, alumni, and mentors. Create a SkillUp account to take part and connect with others on the same journey.' },
    { question: 'How do I get access?', answer: 'Create an account to get started. Some spaces are tailored to enrolled learners and alumni, and you’ll see what’s available to you once you join.' },
    { question: 'Do alumni stay involved?', answer: 'Yes — staying connected after you complete a course is a core part of SkillUp. Alumni remain a valued part of the conversation and often return to mentor others.' },
    { question: 'How do mentors take part?', answer: 'Experienced members and mentors support learners by answering questions, reviewing projects, and sharing guidance and encouragement.' },
    { question: 'What kind of activity happens here?', answer: 'Members ask and answer questions, share projects for feedback, form study groups, and take part in learning-focused conversations and events.' },
];

export default function Community({ events = [] }) {
    const scope = useRevealScope();

    const canonical = typeof window !== 'undefined' ? window.location.origin + window.location.pathname : 'https://skillup.com/community';
    const origin = typeof window !== 'undefined' ? window.location.origin : 'https://skillup.com';
    const description = 'Join the SkillUp community — learn alongside ambitious peers, get mentorship, share your projects, grow your network, and keep learning long after you graduate.';

    const schema = {
        '@context': 'https://schema.org',
        '@type': 'BreadcrumbList',
        itemListElement: [
            { '@type': 'ListItem', position: 1, name: 'Home', item: `${origin}/` },
            { '@type': 'ListItem', position: 2, name: 'Community', item: `${origin}/community` },
        ],
    };

    return (
        <PublicLayout>
            <Head title="Community — Learn, Connect & Grow with SkillUp">
                <meta head-key="description" name="description" content={description} />
                <link head-key="canonical" rel="canonical" href={canonical} />
                <meta head-key="og:type" property="og:type" content="website" />
                <meta head-key="og:title" property="og:title" content="SkillUp Community" />
                <meta head-key="og:description" property="og:description" content={description} />
                <meta head-key="twitter:card" name="twitter:card" content="summary_large_image" />
                <meta head-key="twitter:title" name="twitter:title" content="SkillUp Community" />
                <meta head-key="twitter:description" name="twitter:description" content={description} />
            </Head>

            <StructuredData data={schema} />

            <div ref={scope}>
                <CommunityHero
                    eyebrow="SkillUp community"
                    heading="Learn together, grow further"
                    description="Skills stick when you build them with others. Join a community of ambitious learners, mentors, and alumni who share knowledge, give feedback, and cheer each other on."
                    primary={{ label: 'Join the community', href: '/register' }}
                    secondary={{ label: 'Explore courses', href: '/courses' }}
                    image={{ src: '/images/abj.png', alt: 'SkillUp learners collaborating together' }}
                />

                {/* Benefits */}
                <section className="bg-skillup-soft py-16 sm:py-20" aria-labelledby="benefits">
                    <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                        <div className="mb-10 text-center" data-reveal>
                            <span className="text-xs font-bold uppercase tracking-[0.14em] text-skillup-blue">Why join</span>
                            <h2 id="benefits" className="mt-3 text-3xl font-bold tracking-tight text-skillup-navy sm:text-4xl">More than a course — a community behind you</h2>
                            <p className="mx-auto mt-3 max-w-2xl text-base leading-7 text-gray-600">The people around you make the difference. Here’s what being part of the SkillUp community gives you.</p>
                        </div>
                        <div className="grid gap-6 sm:grid-cols-2 lg:grid-cols-3" data-reveal-group>
                            {BENEFITS.map((b) => <BenefitCard key={b.title} icon={b.icon} title={b.title} text={b.text} />)}
                        </div>
                    </div>
                </section>

                {/* Experience */}
                <section className="bg-white py-16 sm:py-20" aria-labelledby="experience">
                    <div className="mx-auto grid max-w-7xl items-center gap-12 px-4 sm:px-6 lg:grid-cols-2 lg:px-8">
                        <div data-reveal>
                            <span className="text-xs font-bold uppercase tracking-[0.14em] text-skillup-blue">How it works</span>
                            <h2 id="experience" className="mt-3 text-3xl font-bold tracking-tight text-skillup-navy sm:text-4xl">A place to ask, share, and keep going</h2>
                            <p className="mt-4 text-base leading-7 text-gray-600">
                                Learning is rarely a straight line. The community is where you get unstuck, show your work, and stay
                                connected — every step of the way.
                            </p>
                        </div>
                        <ul className="space-y-4" data-reveal-group>
                            {EXPERIENCE.map((item) => (
                                <li key={item.text} className="flex items-start gap-4 rounded-2xl border border-slate-200 bg-skillup-soft p-5 transition-all duration-300 hover:-translate-y-1 hover:shadow-card">
                                    <span className="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-lg bg-skillup-blue/10 text-skillup-blue">
                                        <item.icon className="h-5 w-5" aria-hidden="true" />
                                    </span>
                                    <span className="pt-1.5 text-sm leading-6 text-gray-700">{item.text}</span>
                                </li>
                            ))}
                        </ul>
                    </div>
                </section>

                {/* Journey */}
                <section className="bg-skillup-soft py-16 sm:py-20" aria-labelledby="journey">
                    <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                        <div className="mb-12 text-center" data-reveal>
                            <span className="text-xs font-bold uppercase tracking-[0.14em] text-skillup-blue">The journey</span>
                            <h2 id="journey" className="mt-3 text-3xl font-bold tracking-tight text-skillup-navy sm:text-4xl">From first lesson to mentoring others</h2>
                            <p className="mx-auto mt-3 max-w-2xl text-base leading-7 text-gray-600">Every member’s path looks a little different — but this is the shape of the journey.</p>
                        </div>
                        <JourneyTimeline steps={JOURNEY} />
                    </div>
                </section>

                {/* Upcoming events — reuses the Events card; hidden when none exist */}
                {events.length > 0 && (
                    <section className="bg-white py-16 sm:py-20" aria-labelledby="events">
                        <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                            <div className="mb-10 flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between" data-reveal>
                                <div>
                                    <span className="text-xs font-bold uppercase tracking-[0.14em] text-skillup-blue">Get together</span>
                                    <h2 id="events" className="mt-3 text-3xl font-bold tracking-tight text-skillup-navy sm:text-4xl">Upcoming community events</h2>
                                </div>
                                <a href="/events" className="text-sm font-semibold text-skillup-blue hover:underline">View all events →</a>
                            </div>
                            <div className="grid gap-6 sm:grid-cols-2 lg:grid-cols-3" data-reveal-group>
                                {events.map((event, index) => <EventCard key={event.id} event={event} priority={index === 0} />)}
                            </div>
                        </div>
                    </section>
                )}

                {/* Guidelines */}
                <section className="bg-white py-16 sm:py-20" aria-labelledby="guidelines">
                    <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                        <div className="mb-10 text-center" data-reveal>
                            <span className="inline-flex items-center gap-2 text-xs font-bold uppercase tracking-[0.14em] text-skillup-blue">
                                <ShieldCheck className="h-4 w-4" aria-hidden="true" />
                                Community values
                            </span>
                            <h2 id="guidelines" className="mt-3 text-3xl font-bold tracking-tight text-skillup-navy sm:text-4xl">How we look out for each other</h2>
                            <p className="mx-auto mt-3 max-w-2xl text-base leading-7 text-gray-600">A few simple values keep the SkillUp community a welcoming place for everyone.</p>
                        </div>
                        <div className="grid gap-6 sm:grid-cols-2 lg:grid-cols-4" data-reveal-group>
                            {GUIDELINES.map((g) => <BenefitCard key={g.title} icon={g.icon} title={g.title} text={g.text} />)}
                        </div>
                    </div>
                </section>

                {/* FAQ */}
                <section className="bg-skillup-soft py-16 sm:py-20" aria-labelledby="faq">
                    <div className="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
                        <div className="mb-10 text-center" data-reveal>
                            <span className="text-xs font-bold uppercase tracking-[0.14em] text-skillup-blue">FAQ</span>
                            <h2 id="faq" className="mt-3 text-3xl font-bold tracking-tight text-skillup-navy sm:text-4xl">Community questions, answered</h2>
                        </div>
                        <div data-reveal>
                            <FaqAccordion items={FAQS} />
                        </div>
                    </div>
                </section>

                {/* Final CTA */}
                <CtaBanner
                    heading="Ready to learn alongside ambitious learners?"
                    description="Join the SkillUp community and turn your goals into progress — together."
                    primary={{ label: 'Join the community', href: '/register' }}
                    secondary={{ label: 'Browse courses', href: '/courses' }}
                />
            </div>
        </PublicLayout>
    );
}
