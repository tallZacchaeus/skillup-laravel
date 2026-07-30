/**
 * Generic labelled-pill grid used for "Who this is for" (audiences) and
 * "Career opportunities" (roles). Renders nothing when the list is empty, so
 * callers can drop it in and it hides itself when there's no real data.
 */
export default function PillGrid({ items = [], icon: Icon }) {
    if (!items || items.length === 0) return null;

    return (
        <ul className="grid gap-3 sm:grid-cols-2">
            {items.map((item) => (
                <li key={item} className="flex items-center gap-3 rounded-xl border border-slate-200 bg-white p-4 shadow-card">
                    {Icon && (
                        <span className="flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-lg bg-skillup-blue/10 text-skillup-blue">
                            <Icon className="h-5 w-5" aria-hidden="true" />
                        </span>
                    )}
                    <span className="font-medium text-skillup-navy">{item}</span>
                </li>
            ))}
        </ul>
    );
}
