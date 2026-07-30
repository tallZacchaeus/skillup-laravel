import { router, usePage } from '@inertiajs/react';
import { Heart } from 'lucide-react';
import { cn } from '@/lib/utils';

/**
 * Save-for-later toggle. Reads shared `wishlist.ids` for the filled state and
 * posts to the auth-only toggle route; guests are sent to login first.
 * variant: "overlay" (circular, over a card image) | "button" (inline labelled).
 */
export default function WishlistButton({ product, variant = 'overlay', className }) {
    const { auth, wishlist } = usePage().props;
    const saved = (wishlist?.ids || []).includes(product.id);

    const onClick = (e) => {
        e.preventDefault();
        e.stopPropagation();

        if (!auth?.user) {
            router.get('/login');
            return;
        }

        router.post(route('wishlist.toggle', product.slug), {}, { preserveScroll: true, preserveState: true });
    };

    if (variant === 'button') {
        return (
            <button
                type="button"
                onClick={onClick}
                aria-pressed={saved}
                className={cn(
                    'inline-flex h-12 items-center justify-center gap-2 rounded-md border-2 px-5 text-base font-semibold transition-colors',
                    saved ? 'border-rose-500 bg-rose-50 text-rose-600' : 'border-slate-300 text-slate-700 hover:border-rose-400 hover:text-rose-500',
                    className,
                )}
            >
                <Heart className={cn('h-5 w-5', saved && 'fill-rose-500')} aria-hidden="true" />
                {saved ? 'Saved' : 'Save'}
            </button>
        );
    }

    const label = saved
        ? 'Remove from wishlist'
        : auth?.user
            ? 'Save to wishlist'
            : 'Log in to save to wishlist';

    return (
        <button
            type="button"
            onClick={onClick}
            aria-pressed={saved}
            aria-label={label}
            title={label}
            className={cn(
                'absolute right-3 top-3 inline-flex h-11 w-11 items-center justify-center rounded-full bg-white/90 shadow-sm backdrop-blur transition hover:bg-white focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-rose-400',
                className,
            )}
        >
            <Heart className={cn('h-5 w-5', saved ? 'fill-rose-500 text-rose-500' : 'text-slate-600')} aria-hidden="true" />
        </button>
    );
}
