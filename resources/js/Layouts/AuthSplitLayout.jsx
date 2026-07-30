import ApplicationLogo from '@/Components/ApplicationLogo';
import { Link } from '@inertiajs/react';
import { Award, GraduationCap, LineChart } from 'lucide-react';

// Real, in-product capabilities (learner dashboard, enrolments, certificates).
const highlights = [
    { icon: GraduationCap, title: 'Your enrolled courses', text: 'Pick up right where you left off.' },
    { icon: LineChart, title: 'Track your progress', text: 'See how far you’ve come across every track.' },
    { icon: Award, title: 'Earn certificates', text: 'Download and share your SkillUp certificates.' },
];

/**
 * Two-column authentication shell. Left: on-brand navy panel with headline +
 * real product highlights (desktop only). Right: the auth form, with the logo
 * on a white surface for correct contrast. Collapses to a single, form-first
 * column on mobile. Not a replacement for GuestLayout — used only where a split
 * layout is wanted (login), so other auth pages are unaffected.
 */
export default function AuthSplitLayout({
    children,
    title = 'Welcome back to your learning journey',
    subtitle = 'Sign in to continue building job-ready tech skills with practical, mentor-led courses.',
}) {
    return (
        <div className="min-h-svh bg-white font-sans lg:grid lg:grid-cols-2">
            {/* Brand panel — desktop only */}
            <aside className="relative hidden overflow-hidden bg-skillup-navy p-12 text-white lg:flex lg:flex-col lg:justify-between">
                <div className="hero-scrim absolute inset-0" aria-hidden="true" />
                <Link href="/" className="relative inline-flex items-center text-lg font-bold tracking-tight text-white">
                    SkillUp
                </Link>
                <div className="relative">
                    <h2 className="max-w-md text-3xl font-bold leading-tight tracking-tight xl:text-4xl">
                        {title}
                    </h2>
                    <p className="mt-4 max-w-md text-base leading-7 text-blue-100">
                        {subtitle}
                    </p>
                    <ul className="mt-10 space-y-5">
                        {highlights.map((item) => (
                            <li key={item.title} className="flex items-start gap-4">
                                <span className="flex h-11 w-11 flex-shrink-0 items-center justify-center rounded-xl bg-white/10 text-blue-200">
                                    <item.icon className="h-5 w-5" aria-hidden="true" />
                                </span>
                                <span>
                                    <span className="block font-semibold text-white">{item.title}</span>
                                    <span className="block text-sm text-blue-100">{item.text}</span>
                                </span>
                            </li>
                        ))}
                    </ul>
                </div>
                <p className="relative text-xs text-blue-100/70">© {new Date().getFullYear()} SkillUp Edtech</p>
            </aside>

            {/* Form column */}
            <main className="flex min-h-svh flex-col justify-center px-4 py-10 sm:px-6 lg:px-12">
                <div className="mx-auto w-full max-w-md">
                    <div className="mb-8">
                        <Link href="/" className="inline-flex transition-transform hover:scale-105" aria-label="SkillUp home">
                            <ApplicationLogo className="h-9 w-auto" />
                        </Link>
                    </div>
                    {children}
                    <p className="mt-8 text-center text-sm lg:hidden">
                        <Link href="/" className="font-medium text-skillup-blue hover:underline">← Back to SkillUp</Link>
                    </p>
                </div>
            </main>
        </div>
    );
}
