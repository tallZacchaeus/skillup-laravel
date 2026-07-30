import { cn } from '@/lib/utils';

const variants = {
    default: 'bg-blue-50 text-skillup-blue ring-blue-100',
    neutral: 'bg-slate-100 text-slate-700 ring-slate-200',
    success: 'bg-emerald-50 text-emerald-700 ring-emerald-100',
    warning: 'bg-orange-50 text-orange-700 ring-orange-100',
};

export function Badge({ className, variant = 'default', ...props }) {
    return (
        <span
            className={cn(
                'inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold ring-1 ring-inset',
                variants[variant],
                className,
            )}
            {...props}
        />
    );
}
