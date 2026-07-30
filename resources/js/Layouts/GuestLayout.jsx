import ApplicationLogo from '@/Components/ApplicationLogo';
import { Link } from '@inertiajs/react';

export default function GuestLayout({ children }) {
    return (
        <div className="flex min-h-svh flex-col items-center justify-center bg-skillup-soft px-4 py-10 font-sans">
            <Link href="/" className="transition-transform hover:scale-105">
                <ApplicationLogo className="h-9 w-auto" />
            </Link>

            <div className="mt-6 w-full max-w-md overflow-hidden rounded-2xl border border-slate-200 bg-white p-8 shadow-sm">
                {children}
            </div>

            <Link href="/" className="mt-6 text-sm font-medium text-skillup-blue hover:underline">
                ← Back to SkillUp
            </Link>
        </div>
    );
}
