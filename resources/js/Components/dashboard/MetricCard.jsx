import { Link } from '@inertiajs/react';

/**
 * Reusable dashboard metric card. Shows an icon, a value, and a label. Renders
 * as a link when `href` is passed. Only mount this for metrics backed by real
 * data — the dashboard decides which metrics to include.
 */
export default function MetricCard({ icon: Icon, value, label, href, accent = 'blue' }) {
    const accents = {
        blue: 'bg-skillup-blue/10 text-skillup-blue',
        green: 'bg-emerald-50 text-emerald-600',
        amber: 'bg-amber-50 text-amber-600',
        purple: 'bg-indigo-50 text-indigo-600',
    };

    const body = (
        <div className="flex h-full items-center gap-4 rounded-2xl border border-slate-200 bg-white p-5 shadow-card transition-all duration-300 hover:-translate-y-1 hover:shadow-card-hover">
            <span className={`flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-xl ${accents[accent] ?? accents.blue}`}>
                <Icon className="h-6 w-6" aria-hidden="true" />
            </span>
            <div className="min-w-0">
                <p className="text-2xl font-bold leading-tight text-skillup-navy">{value}</p>
                <p className="truncate text-sm text-slate-500">{label}</p>
            </div>
        </div>
    );

    if (href) {
        return (
            <Link href={href} className="block rounded-2xl focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-skillup-blue/40">
                {body}
            </Link>
        );
    }
    return body;
}
