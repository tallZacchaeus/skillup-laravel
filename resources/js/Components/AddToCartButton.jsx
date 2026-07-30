import { useState } from 'react';
import { router, usePage } from '@inertiajs/react';
import { Check, Loader2, ShoppingCart } from 'lucide-react';
import { cn } from '@/lib/utils';

/**
 * Add / view cart toggle. Reads the shared `cart.ids` for the in-cart state, so
 * the label persists across refreshes and never adds a product twice. Shows a
 * processing state during the request and surfaces errors via a flash toast.
 * variant: "button" (labelled) | "compact" (icon only, for dense grids).
 */
export default function AddToCartButton({ product, variant = 'button', className }) {
    const { cart } = usePage().props;
    const inCart = (cart?.ids || []).includes(product.id);
    const [processing, setProcessing] = useState(false);

    const onClick = (e) => {
        e.preventDefault();
        e.stopPropagation();

        if (inCart) {
            router.get('/cart');
            return;
        }

        router.post(route('cart.add', product.slug), {}, {
            preserveScroll: true,
            preserveState: true,
            onStart: () => setProcessing(true),
            onFinish: () => setProcessing(false),
        });
    };

    if (variant === 'compact') {
        return (
            <button
                type="button"
                onClick={onClick}
                disabled={processing}
                aria-label={inCart ? 'In cart — view cart' : 'Add to cart'}
                className={cn(
                    'inline-flex h-11 w-11 flex-shrink-0 items-center justify-center rounded-md border-2 transition-colors disabled:opacity-60',
                    inCart ? 'border-emerald-500 bg-emerald-50 text-emerald-600' : 'border-slate-300 text-slate-600 hover:border-skillup-blue hover:text-skillup-blue',
                    className,
                )}
            >
                {processing ? (
                    <Loader2 className="h-4 w-4 motion-safe:animate-spin" aria-hidden="true" />
                ) : inCart ? (
                    <Check className="h-4 w-4" aria-hidden="true" />
                ) : (
                    <ShoppingCart className="h-4 w-4" aria-hidden="true" />
                )}
            </button>
        );
    }

    return (
        <button
            type="button"
            onClick={onClick}
            disabled={processing}
            aria-label={inCart ? 'View cart' : `Add ${product.title} to cart`}
            className={cn(
                'inline-flex h-11 items-center justify-center gap-2 rounded-md border-2 px-5 text-sm font-semibold transition-colors disabled:opacity-70',
                inCart ? 'border-emerald-500 bg-emerald-50 text-emerald-700' : 'border-skillup-blue text-skillup-navy hover:bg-skillup-blue hover:text-white',
                className,
            )}
        >
            {processing ? (
                <>
                    <Loader2 className="h-4 w-4 motion-safe:animate-spin" aria-hidden="true" />
                    Adding…
                </>
            ) : inCart ? (
                <>
                    <Check className="h-4 w-4" aria-hidden="true" />
                    In cart — view
                </>
            ) : (
                <>
                    <ShoppingCart className="h-4 w-4" aria-hidden="true" />
                    Add to cart
                </>
            )}
        </button>
    );
}
