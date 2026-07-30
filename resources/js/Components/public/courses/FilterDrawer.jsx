import { useEffect, useRef } from 'react';
import { createPortal } from 'react-dom';
import { X } from 'lucide-react';
import FilterPanel from '@/Components/public/courses/FilterPanel';

/**
 * Mobile / tablet filter drawer (bottom sheet). Mounted only while open, so the
 * parent code-splits it via React.lazy. Locks background scroll, traps focus,
 * closes on Escape/overlay, and exposes Clear-all + Apply in a sticky footer.
 * Filtering is live, so Apply simply closes and reflects the current total.
 */
export default function FilterDrawer({ onClose, options, filters, onToggle, onClearAll, total, activeCount }) {
    const panelRef = useRef(null);

    useEffect(() => {
        const previousOverflow = document.body.style.overflow;
        document.body.style.overflow = 'hidden';
        const panel = panelRef.current;

        const focusable = () =>
            Array.from(
                panel?.querySelectorAll(
                    'a[href], button:not([disabled]), input, select, textarea, [tabindex]:not([tabindex="-1"])',
                ) ?? [],
            );

        focusable()[0]?.focus();

        const onKey = (event) => {
            if (event.key === 'Escape') {
                event.preventDefault();
                onClose();
                return;
            }
            if (event.key !== 'Tab') return;

            const items = focusable();
            if (items.length === 0) return;
            const first = items[0];
            const last = items[items.length - 1];

            if (event.shiftKey && document.activeElement === first) {
                event.preventDefault();
                last.focus();
            } else if (!event.shiftKey && document.activeElement === last) {
                event.preventDefault();
                first.focus();
            }
        };

        document.addEventListener('keydown', onKey);
        return () => {
            document.body.style.overflow = previousOverflow;
            document.removeEventListener('keydown', onKey);
        };
    }, [onClose]);

    return createPortal(
        <div className="fixed inset-0 z-[120] lg:hidden">
            <div className="absolute inset-0 bg-black/40" onClick={onClose} aria-hidden="true" />
            <div
                ref={panelRef}
                role="dialog"
                aria-modal="true"
                aria-label="Filters"
                className="absolute inset-x-0 bottom-0 flex max-h-[88vh] flex-col rounded-t-2xl bg-white shadow-elevated motion-safe:animate-fade-in-up"
            >
                <header className="flex items-center justify-between border-b border-slate-200 px-5 py-4">
                    <h2 className="text-base font-bold text-skillup-navy">
                        Filters{activeCount > 0 ? ` (${activeCount})` : ''}
                    </h2>
                    <button
                        type="button"
                        onClick={onClose}
                        aria-label="Close filters"
                        className="inline-flex h-11 w-11 items-center justify-center rounded-md text-slate-500 transition-colors hover:bg-slate-100 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-skillup-blue/40"
                    >
                        <X className="h-5 w-5" aria-hidden="true" />
                    </button>
                </header>

                <div className="flex-1 overflow-y-auto px-5">
                    <FilterPanel options={options} filters={filters} onToggle={onToggle} />
                </div>

                <footer className="flex items-center gap-3 border-t border-slate-200 px-5 py-4">
                    <button
                        type="button"
                        onClick={onClearAll}
                        disabled={activeCount === 0}
                        className="inline-flex h-11 flex-1 items-center justify-center rounded-md border border-slate-300 text-sm font-semibold text-slate-700 transition-colors hover:bg-slate-50 disabled:opacity-40 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-skillup-blue/40"
                    >
                        Clear all
                    </button>
                    <button
                        type="button"
                        onClick={onClose}
                        className="inline-flex h-11 flex-1 items-center justify-center rounded-md bg-skillup-blue text-sm font-semibold text-white transition-colors hover:bg-blue-700 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-skillup-blue"
                    >
                        Show {total} {total === 1 ? 'course' : 'courses'}
                    </button>
                </footer>
            </div>
        </div>,
        document.body,
    );
}
