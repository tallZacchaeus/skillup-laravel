import { cn } from '@/lib/utils';

export function Textarea({ className, ...props }) {
    return (
        <textarea
            className={cn(
                'flex min-h-32 w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm outline-none transition placeholder:text-slate-500 focus:border-skillup-blue focus:ring-2 focus:ring-skillup-blue/20 disabled:cursor-not-allowed disabled:opacity-50',
                className,
            )}
            {...props}
        />
    );
}
