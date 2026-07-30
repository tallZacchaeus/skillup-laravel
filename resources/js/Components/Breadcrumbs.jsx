import { Link } from '@inertiajs/react';
import { ChevronRight } from 'lucide-react';
import { cn } from '@/lib/utils';

/**
 * Accessible breadcrumb trail. items: [{ label, href? }] — the last item is the
 * current page (no href). `tone="light"` for dark/navy hero backgrounds.
 */
export default function Breadcrumbs({ items = [], tone = 'dark', className }) {
    if (items.length === 0) return null;

    const link = tone === 'light' ? 'text-blue-100 hover:text-white' : 'text-slate-500 hover:text-skillup-blue';
    const current = tone === 'light' ? 'text-white' : 'text-skillup-navy';
    const sep = tone === 'light' ? 'text-blue-200/60' : 'text-slate-400';

    return (
        <nav aria-label="Breadcrumb" className={className}>
            <ol className="flex flex-wrap items-center gap-1.5 text-sm">
                {items.map((item, i) => {
                    const last = i === items.length - 1;
                    return (
                        <li key={`${item.label}-${i}`} className="flex items-center gap-1.5">
                            {i > 0 && <ChevronRight className={cn('h-3.5 w-3.5', sep)} aria-hidden="true" />}
                            {item.href && !last ? (
                                <Link href={item.href} className={cn('transition-colors', link)}>{item.label}</Link>
                            ) : (
                                <span aria-current="page" className={cn('font-medium', current)}>{item.label}</span>
                            )}
                        </li>
                    );
                })}
            </ol>
        </nav>
    );
}
