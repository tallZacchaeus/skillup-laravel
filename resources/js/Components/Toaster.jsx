import { useEffect, useState } from 'react';
import { router, usePage } from '@inertiajs/react';
import { AlertCircle, CheckCircle2, X } from 'lucide-react';
import { cn } from '@/lib/utils';

/**
 * Global toast for Inertia flash messages ({status} / {error}). Optionally shows
 * an Undo action when the server flashes an {undo: {label, url, method}} payload
 * (e.g. cart add, wishlist save). aria-live="polite"; dismisses itself.
 */
export default function Toaster() {
    const { flash } = usePage().props;
    const [toast, setToast] = useState(null);

    useEffect(() => {
        const message = flash?.status || flash?.error;
        if (!message) return undefined;

        setToast({ message, type: flash.error ? 'error' : 'success', undo: flash.error ? null : flash.undo || null });
        const id = setTimeout(() => setToast(null), flash.undo ? 6000 : 3500);
        return () => clearTimeout(id);
    }, [flash?.status, flash?.error, flash?.undo]);

    if (!toast) return null;

    const isError = toast.type === 'error';

    const handleUndo = () => {
        const { url, method = 'post' } = toast.undo;
        router.visit(url, { method, preserveScroll: true, preserveState: true });
        setToast(null);
    };

    return (
        <div
            role="status"
            aria-live="polite"
            className="pointer-events-none fixed inset-x-0 bottom-4 z-[200] flex justify-center px-4 sm:inset-x-auto sm:right-4 sm:justify-end"
        >
            <div
                className={cn(
                    'pointer-events-auto flex max-w-sm items-start gap-3 rounded-xl border bg-white px-4 py-3 shadow-lg',
                    isError ? 'border-red-200' : 'border-emerald-200',
                )}
            >
                {isError ? (
                    <AlertCircle className="mt-0.5 h-5 w-5 shrink-0 text-red-500" aria-hidden="true" />
                ) : (
                    <CheckCircle2 className="mt-0.5 h-5 w-5 shrink-0 text-emerald-500" aria-hidden="true" />
                )}
                <p className="text-sm font-medium text-skillup-navy">{toast.message}</p>
                {toast.undo && (
                    <button
                        type="button"
                        onClick={handleUndo}
                        className="ml-1 shrink-0 rounded px-1.5 py-0.5 text-sm font-semibold text-skillup-blue underline-offset-2 transition-colors hover:bg-skillup-blue/10 hover:underline focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-skillup-blue"
                    >
                        {toast.undo.label || 'Undo'}
                    </button>
                )}
                <button
                    type="button"
                    onClick={() => setToast(null)}
                    aria-label="Dismiss"
                    className="ml-0.5 shrink-0 rounded p-0.5 text-slate-400 transition-colors hover:text-slate-600 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-300"
                >
                    <X className="h-4 w-4" aria-hidden="true" />
                </button>
            </div>
        </div>
    );
}
