import { cn } from '@/lib/utils';

/**
 * Standardized section header (eyebrow + title + description). Centralizes the
 * type scale and spacing so every homepage section shares one rhythm.
 */
export default function SectionHeading({
    eyebrow,
    title,
    description,
    align = 'center',
    className,
    titleClassName,
    ...props
}) {
    const centered = align === 'center';

    return (
        <div className={cn(centered ? 'mx-auto max-w-headline text-center' : 'max-w-headline', className)} {...props}>
            {eyebrow && (
                <span className="mb-3 inline-block text-xs font-bold uppercase tracking-[0.14em] text-skillup-blue">
                    {eyebrow}
                </span>
            )}
            <h2 className={cn('text-3xl font-bold tracking-tight text-skillup-navy sm:text-4xl', titleClassName)}>
                {title}
            </h2>
            {description && (
                <p className={cn('mt-4 text-base leading-7 text-slate-600 sm:text-lg', centered && 'mx-auto max-w-2xl')}>
                    {description}
                </p>
            )}
        </div>
    );
}
