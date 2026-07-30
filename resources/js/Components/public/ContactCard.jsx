import { cn } from '@/lib/utils';

/**
 * Reusable contact info card (icon, title, value, description). Pass `href` to
 * make the whole card an actionable link, or `children` for custom content
 * (e.g. social icons). Equal height with a subtle hover lift.
 */
export default function ContactCard({ icon: Icon, title, value, description, href, external, children }) {
    const body = (
        <>
            {Icon && (
                <span className="flex h-11 w-11 flex-shrink-0 items-center justify-center rounded-xl bg-skillup-blue/10 text-skillup-blue transition-transform duration-300 group-hover:scale-110">
                    <Icon className="h-5 w-5" aria-hidden="true" />
                </span>
            )}
            <span className="mt-4 block text-xs font-bold uppercase tracking-wide text-slate-500">{title}</span>
            {value && <span className="mt-1 block font-semibold leading-6 text-skillup-navy">{value}</span>}
            {description && <span className="mt-1 block text-sm leading-6 text-slate-600">{description}</span>}
            {children}
        </>
    );

    const base = 'group flex h-full flex-col rounded-2xl border border-slate-200 bg-white p-6 shadow-card transition-all duration-300 hover:-translate-y-1 hover:border-skillup-blue/40 hover:shadow-card-hover focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-skillup-blue/40';

    if (href) {
        return (
            <a
                href={href}
                target={external ? '_blank' : undefined}
                rel={external ? 'noreferrer' : undefined}
                className={cn(base, 'no-underline')}
            >
                {body}
            </a>
        );
    }

    return <div className={base}>{body}</div>;
}
