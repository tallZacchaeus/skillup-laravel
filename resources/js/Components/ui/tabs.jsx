import { cn } from '@/lib/utils';

export function Tabs({ className, ...props }) {
    return <div className={cn('w-full', className)} {...props} />;
}

export function TabsList({ className, ...props }) {
    return <div className={cn('inline-flex rounded-md bg-blue-50 p-1', className)} {...props} />;
}

export function TabsTrigger({ className, active = false, ...props }) {
    return (
        <button
            type="button"
            className={cn(
                'rounded px-4 py-2 text-sm font-semibold transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-skillup-blue',
                active ? 'bg-skillup-blue text-white shadow-sm' : 'text-skillup-blue hover:bg-white',
                className,
            )}
            {...props}
        />
    );
}
