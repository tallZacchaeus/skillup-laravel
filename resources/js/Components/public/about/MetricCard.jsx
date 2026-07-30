/**
 * Impact metric card. The number renders in the DOM immediately and counts up
 * from 0 when scrolled into view via the shared useRevealScope() data-count
 * mechanism (reduced-motion users keep the final value — no fabrication, just
 * animation). Includes a short supporting description.
 */
export default function MetricCard({ value, suffix = '', label, description }) {
    return (
        <div className="rounded-2xl border border-slate-200 bg-white p-6 text-center shadow-card">
            <div
                className="text-4xl font-bold tabular-nums text-skillup-navy sm:text-5xl"
                data-count={value}
                data-count-suffix={suffix}
            >
                {value.toLocaleString()}
                {suffix}
            </div>
            <div className="mt-2 text-xs font-bold uppercase tracking-wide text-skillup-blue">{label}</div>
            {description && <p className="mt-2 text-sm leading-6 text-slate-600">{description}</p>}
        </div>
    );
}
