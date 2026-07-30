import { useEffect, useRef } from 'react';
import { createPortal } from 'react-dom';
import { Download, Lock, X } from 'lucide-react';
import { Input } from '@/Components/ui/input';

/**
 * Gated-download dialog. Submits a NATIVE form POST to the download route so the
 * browser handles the file response as a download (streamed attachment). Traps
 * focus, locks scroll, closes on Escape/overlay. `csrfToken` is injected as the
 * Laravel `_token` field.
 */
export default function DownloadDialog({ resource, csrfToken, onClose }) {
    const panelRef = useRef(null);

    useEffect(() => {
        const previousOverflow = document.body.style.overflow;
        document.body.style.overflow = 'hidden';
        const panel = panelRef.current;

        const focusable = () =>
            Array.from(panel?.querySelectorAll('a[href], button:not([disabled]), input, [tabindex]:not([tabindex="-1"])') ?? []);
        focusable()[1]?.focus(); // the email input (after the close button)

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

    const titleId = `dl-${resource.slug}`;

    return createPortal(
        <div className="fixed inset-0 z-[130] flex items-end justify-center p-0 sm:items-center sm:p-4">
            <div className="absolute inset-0 bg-black/50" onClick={onClose} aria-hidden="true" />
            <div
                ref={panelRef}
                role="dialog"
                aria-modal="true"
                aria-labelledby={titleId}
                className="relative w-full max-w-md rounded-t-2xl bg-white p-6 shadow-elevated motion-safe:animate-fade-in-up sm:rounded-2xl"
            >
                <button
                    type="button"
                    onClick={onClose}
                    aria-label="Close"
                    className="absolute right-4 top-4 inline-flex h-9 w-9 items-center justify-center rounded-md text-slate-400 transition-colors hover:bg-slate-100 hover:text-slate-600 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-skillup-blue/40"
                >
                    <X className="h-5 w-5" aria-hidden="true" />
                </button>

                <div className="flex h-12 w-12 items-center justify-center rounded-xl bg-skillup-blue/10 text-skillup-blue">
                    <Lock className="h-6 w-6" aria-hidden="true" />
                </div>
                <h2 id={titleId} className="mt-4 text-xl font-bold text-skillup-navy">Get your free download</h2>
                <p className="mt-1.5 text-sm leading-6 text-slate-600">
                    Enter your email and we’ll start your download of <span className="font-semibold">{resource.title}</span>.
                </p>

                <form
                    method="POST"
                    action={resource.downloadUrl}
                    onSubmit={() => window.setTimeout(onClose, 1200)}
                    className="mt-5 space-y-4"
                >
                    <input type="hidden" name="_token" value={csrfToken} />
                    <div>
                        <label htmlFor={`${titleId}-name`} className="mb-1.5 block text-sm font-semibold text-slate-700">
                            Name <span className="ml-1 text-xs font-normal text-slate-400">(optional)</span>
                        </label>
                        <Input id={`${titleId}-name`} name="name" autoComplete="name" placeholder="Your name" />
                    </div>
                    <div>
                        <label htmlFor={`${titleId}-email`} className="mb-1.5 block text-sm font-semibold text-slate-700">
                            Work email <span className="text-red-500" aria-hidden="true">*</span>
                        </label>
                        <Input id={`${titleId}-email`} name="email" type="email" required autoComplete="email" placeholder="you@company.com" />
                    </div>
                    <button
                        type="submit"
                        className="inline-flex h-11 w-full items-center justify-center gap-2 rounded-md bg-skillup-blue text-sm font-semibold text-white transition-colors hover:bg-blue-700 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-skillup-blue focus-visible:ring-offset-2"
                    >
                        <Download className="h-4 w-4" aria-hidden="true" />
                        Download {resource.fileType ? `(${resource.fileType})` : ''}
                    </button>
                    <p className="text-center text-xs text-slate-400">
                        We’ll email you occasional learning resources. Unsubscribe anytime.
                    </p>
                </form>
            </div>
        </div>,
        document.body,
    );
}
