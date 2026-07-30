import { cn } from '@/lib/utils';

const variants = {
    default: 'bg-skillup-blue text-white shadow-sm hover:bg-skillup-navy focus-visible:ring-skillup-blue',
    secondary: 'bg-white text-skillup-navy shadow-sm ring-1 ring-inset ring-slate-200 hover:bg-slate-50 focus-visible:ring-skillup-blue',
    outline: 'border border-skillup-blue bg-transparent text-skillup-blue hover:bg-blue-50 focus-visible:ring-skillup-blue',
    ghost: 'text-slate-700 hover:bg-slate-100 hover:text-skillup-navy focus-visible:ring-skillup-blue',
    accent: 'bg-skillup-orange text-white shadow-sm hover:bg-orange-600 focus-visible:ring-skillup-orange',
};

const sizes = {
    default: 'h-11 px-5 py-2.5 text-sm',
    sm: 'h-9 px-3 text-sm',
    lg: 'h-12 px-6 text-base',
    icon: 'h-11 w-11',
};

export function buttonVariants({ variant = 'default', size = 'default', className = '' } = {}) {
    return cn(
        'inline-flex items-center justify-center gap-2 rounded-md font-semibold transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50',
        variants[variant],
        sizes[size],
        className,
    );
}

export function Button({ className, variant = 'default', size = 'default', type = 'button', ...props }) {
    return (
        <button
            type={type}
            className={buttonVariants({ variant, size, className })}
            {...props}
        />
    );
}
