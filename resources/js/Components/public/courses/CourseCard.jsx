import { Link } from '@inertiajs/react';
import { ArrowRight, GraduationCap } from 'lucide-react';
import Img from '@/Components/Img';
import AddToCartButton from '@/Components/AddToCartButton';
import WishlistButton from '@/Components/WishlistButton';
import { buttonVariants } from '@/Components/ui/button';
import { cn } from '@/lib/utils';

/**
 * Standardized catalogue course card. Fixed image ratio + flex column keeps
 * every card the same height with actions pinned to the bottom. Only renders
 * metadata that the backend actually provides — nothing is fabricated.
 *
 * `priority` eager-loads the image (use for the first row, above the fold).
 */
export default function CourseCard({ product, priority = false }) {
    // Real, non-fabricated metadata only.
    const meta = [
        product.duration && product.duration !== 'TBA' ? product.duration : null,
        product.deliveryMode || null,
        product.level || null,
    ].filter(Boolean);

    const hasRating = product.rating?.count > 0;
    const hasInstallments = Array.isArray(product.paymentPlans) && product.paymentPlans.length > 0;
    const cohortStart = product.cohort?.status === 'Open' ? product.cohort?.startsAt : null;

    return (
        <article className="group flex h-full flex-col overflow-hidden rounded-2xl bg-white shadow-card ring-1 ring-slate-100 transition-all duration-300 hover:-translate-y-1 hover:shadow-card-hover">
            <div className="relative aspect-[16/10] overflow-hidden bg-slate-100">
                <Img
                    src={product.image}
                    alt={product.title}
                    className="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105"
                    eager={priority}
                />
                {product.isProgram ? (
                    <span className="absolute left-3 top-3 inline-flex items-center gap-1.5 rounded-full bg-skillup-navy px-3 py-1 text-xs font-semibold text-white shadow">
                        <GraduationCap className="h-3.5 w-3.5" aria-hidden="true" />
                        Program
                    </span>
                ) : (
                    product.cohort?.status === 'Open' && (
                        <span className="absolute left-3 top-3 inline-flex items-center gap-1.5 rounded-full bg-emerald-600 px-3 py-1 text-xs font-semibold text-white shadow">
                            <span className="h-1.5 w-1.5 rounded-full bg-white" aria-hidden="true" />
                            Enrollment open
                        </span>
                    )
                )}
                {!product.isProgram && <WishlistButton product={product} />}
            </div>

            <div className="flex flex-1 flex-col p-5">
                <p className="text-xs font-semibold uppercase tracking-wide text-skillup-blue">{product.trackTitle}</p>
                <h3 className="mt-1.5 line-clamp-2 text-lg font-bold leading-snug text-skillup-navy">
                    <Link href={product.url} className="transition-colors hover:text-skillup-blue focus-visible:underline focus-visible:outline-none">
                        {product.title}
                    </Link>
                </h3>
                {product.summary && (
                    <p className="mt-2 line-clamp-2 text-sm leading-6 text-slate-600">{product.summary}</p>
                )}

                {meta.length > 0 && (
                    <p className="mt-3 flex flex-wrap items-center gap-x-1.5 gap-y-1 text-xs font-medium text-slate-500">
                        {meta.map((item, i) => (
                            <span key={item} className="inline-flex items-center gap-1.5">
                                {i > 0 && <span aria-hidden="true" className="text-slate-300">·</span>}
                                {item}
                            </span>
                        ))}
                    </p>
                )}

                {cohortStart && (
                    <p className="mt-1.5 text-xs font-medium text-slate-500">Next cohort starts {cohortStart}</p>
                )}

                {product.tags?.length > 0 && (
                    <ul className="mt-3 flex flex-wrap gap-1.5">
                        {product.tags.slice(0, 3).map((tag) => (
                            <li key={tag.slug} className="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-medium text-slate-600">
                                {tag.name}
                            </li>
                        ))}
                    </ul>
                )}

                <div className="mt-4 flex-1" />

                <div className="border-t border-slate-100 pt-4">
                    <div className="flex items-baseline justify-between gap-2">
                        <span className="text-lg font-bold text-skillup-navy">{product.price}</span>
                        {hasRating && (
                            <span className="text-xs font-medium text-slate-500">
                                <span aria-hidden="true">★</span> {product.rating.average}{' '}
                                <span className="text-slate-400">({product.rating.count})</span>
                            </span>
                        )}
                    </div>
                    {hasInstallments && (
                        <p className="mt-1 text-xs text-slate-500">Installments available</p>
                    )}

                    {product.isProgram ? (
                        <Link href={product.url} className={cn(buttonVariants({ variant: 'default' }), 'mt-4 w-full')}>
                            {product.cta || 'Register'}
                            <ArrowRight className="h-4 w-4" aria-hidden="true" />
                        </Link>
                    ) : (
                        <div className="mt-4 space-y-2">
                            <Link href={product.url} className={cn(buttonVariants({ variant: 'default' }), 'w-full')}>
                                View course
                                <ArrowRight className="h-4 w-4" aria-hidden="true" />
                            </Link>
                            <AddToCartButton product={product} variant="button" className="w-full" />
                        </div>
                    )}
                </div>
            </div>
        </article>
    );
}
