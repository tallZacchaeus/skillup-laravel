/**
 * Single animated statistic. Renders the real value in the DOM immediately;
 * the shared useRevealScope() count-up (driven by data-count) animates it from
 * 0 → value when scrolled into view, and reduced-motion users keep the value.
 */
export default function StatCounter({ value, suffix = '', label, icon: Icon }) {
    return (
        <div className="text-center">
            {Icon && (
                <div className="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-xl bg-skillup-blue/10 text-skillup-blue">
                    <Icon className="h-6 w-6" aria-hidden="true" />
                </div>
            )}
            <div
                className="text-4xl font-bold tabular-nums text-skillup-navy sm:text-5xl"
                data-count={value}
                data-count-suffix={suffix}
            >
                {value.toLocaleString()}
                {suffix}
            </div>
            <div className="mt-1.5 text-sm font-medium text-slate-600">{label}</div>
        </div>
    );
}
