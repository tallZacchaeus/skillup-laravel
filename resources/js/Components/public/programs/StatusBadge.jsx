import { cn } from '@/lib/utils';

/**
 * Reusable registration-status pill. Styling is keyed off the human label so a
 * new status only needs an entry here. Falls back to a neutral style for any
 * label not yet mapped — it never crashes on an unknown status.
 */
const STYLES = {
    open: { dot: 'bg-green-500', text: 'text-green-800', ring: 'bg-green-50 ring-green-200' },
    'closing soon': { dot: 'bg-amber-500', text: 'text-amber-800', ring: 'bg-amber-50 ring-amber-200' },
    full: { dot: 'bg-red-500', text: 'text-red-800', ring: 'bg-red-50 ring-red-200' },
    'coming soon': { dot: 'bg-skillup-blue', text: 'text-blue-800', ring: 'bg-blue-50 ring-blue-200' },
    'in progress': { dot: 'bg-indigo-500', text: 'text-indigo-800', ring: 'bg-indigo-50 ring-indigo-200' },
    completed: { dot: 'bg-slate-400', text: 'text-slate-700', ring: 'bg-slate-100 ring-slate-200' },
    closed: { dot: 'bg-slate-400', text: 'text-slate-700', ring: 'bg-slate-100 ring-slate-200' },
};

const NEUTRAL = { dot: 'bg-slate-400', text: 'text-slate-700', ring: 'bg-slate-100 ring-slate-200' };

export default function StatusBadge({ label, className }) {
    if (!label) return null;
    const style = STYLES[label.toLowerCase()] ?? NEUTRAL;
    const live = ['open', 'closing soon'].includes(label.toLowerCase());

    return (
        <span
            className={cn('inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-bold ring-1', style.ring, style.text, className)}
        >
            <span className={cn('h-1.5 w-1.5 rounded-full', style.dot, live && 'motion-safe:animate-pulse')} aria-hidden="true" />
            {label}
        </span>
    );
}
