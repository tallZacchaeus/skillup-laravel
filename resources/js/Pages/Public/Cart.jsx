import { useState } from 'react';
import { Head, Link, router } from '@inertiajs/react';
import { ArrowRight, Award, Loader2, Lock, ShieldCheck, ShoppingCart, Trash2, Users, Wrench } from 'lucide-react';
import PublicLayout from '@/Components/public/PublicLayout';
import Img from '@/Components/Img';
import Breadcrumbs from '@/Components/Breadcrumbs';
import CourseCard from '@/Components/public/courses/CourseCard';
import { Badge } from '@/Components/ui/badge';
import { buttonVariants } from '@/Components/ui/button';
import { cn } from '@/lib/utils';

const benefits = [
    { icon: Wrench, title: 'Practical, project-based', text: 'Build real projects you can show employers — not just theory.' },
    { icon: Users, title: 'Expert facilitators', text: 'Learn from practitioners who have built products and led teams.' },
    { icon: Award, title: 'Certificate on completion', text: 'Earn a SkillUp certificate you can share and verify.' },
    { icon: ShieldCheck, title: 'Career-focused support', text: 'CV reviews, interview prep, and a pan-African network.' },
];

export default function Cart({ items = [], subtotal, total, count = 0, recommended = [] }) {
    const [removingSlug, setRemovingSlug] = useState(null);

    const remove = (slug) => {
        router.delete(route('cart.remove', slug), {
            preserveScroll: true,
            onStart: () => setRemovingSlug(slug),
            onFinish: () => setRemovingSlug(null),
        });
    };

    const empty = count === 0;

    return (
        <PublicLayout>
            <Head title={empty ? 'Your cart' : `Your cart (${count})`} />

            {/* ─── Header ───────────────────────────────────────── */}
            <section className="bg-skillup-navy pb-12 pt-28 text-white sm:pt-32">
                <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <Breadcrumbs items={[{ label: 'Home', href: '/' }, { label: 'Cart' }]} tone="light" />
                    <h1 className="mt-5 text-3xl font-bold tracking-tight sm:text-4xl md:text-5xl">Your cart</h1>
                    <p className="mt-4 text-lg text-blue-100" aria-live="polite">
                        {count > 0 ? `${count} ${count === 1 ? 'course' : 'courses'} ready for checkout.` : 'Ready when you are.'}
                    </p>
                </div>
            </section>

            <section className="bg-slate-50 py-14">
                <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    {empty ? (
                        <div className="space-y-16">
                            {/* Empty state */}
                            <div className="mx-auto max-w-xl rounded-3xl border border-slate-200 bg-white px-6 py-16 text-center shadow-card">
                                <div className="mx-auto flex h-20 w-20 items-center justify-center rounded-2xl bg-skillup-blue/10 text-skillup-blue">
                                    <ShoppingCart className="h-10 w-10" aria-hidden="true" />
                                </div>
                                <h2 className="mt-6 text-2xl font-bold text-skillup-navy">Your cart is empty</h2>
                                <p className="mx-auto mt-3 max-w-md text-base leading-7 text-slate-600">
                                    Find a course that matches your goals and start building job-ready skills today.
                                </p>
                                <div className="mt-8 flex flex-col items-center justify-center gap-3 sm:flex-row">
                                    <Link href="/courses" className={cn(buttonVariants({ size: 'lg' }))}>
                                        Browse courses
                                        <ArrowRight className="h-4 w-4" aria-hidden="true" />
                                    </Link>
                                    <Link href="/programs" className={cn(buttonVariants({ variant: 'outline', size: 'lg' }))}>
                                        View programs
                                    </Link>
                                </div>
                            </div>

                            {/* Learning benefits */}
                            <div>
                                <h2 className="text-center text-xl font-bold text-skillup-navy">Why learn with SkillUp</h2>
                                <div className="mx-auto mt-8 grid max-w-5xl gap-6 sm:grid-cols-2 lg:grid-cols-4">
                                    {benefits.map((benefit) => (
                                        <div key={benefit.title} className="rounded-2xl border border-slate-200 bg-white p-5 shadow-card">
                                            <div className="flex h-11 w-11 items-center justify-center rounded-xl bg-skillup-blue/10 text-skillup-blue">
                                                <benefit.icon className="h-5 w-5" aria-hidden="true" />
                                            </div>
                                            <h3 className="mt-4 text-sm font-bold text-skillup-navy">{benefit.title}</h3>
                                            <p className="mt-1.5 text-sm leading-6 text-slate-600">{benefit.text}</p>
                                        </div>
                                    ))}
                                </div>
                            </div>

                            {/* Recommended courses */}
                            {recommended.length > 0 && (
                                <div>
                                    <div className="mb-8 flex items-end justify-between gap-4">
                                        <h2 className="text-2xl font-bold text-skillup-navy">Recommended for you</h2>
                                        <Link href="/courses" className="inline-flex flex-shrink-0 items-center gap-1 text-sm font-semibold text-skillup-blue hover:underline">
                                            Browse all
                                            <ArrowRight className="h-4 w-4" aria-hidden="true" />
                                        </Link>
                                    </div>
                                    <div className="grid gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                                        {recommended.map((product) => (
                                            <CourseCard key={product.slug} product={product} />
                                        ))}
                                    </div>
                                </div>
                            )}
                        </div>
                    ) : (
                        <div className="grid gap-8 lg:grid-cols-[1fr_360px]">
                            {/* Line items */}
                            <div>
                                <ul className="space-y-4">
                                    {items.map((item) => {
                                        const busy = removingSlug === item.slug;
                                        const meta = [item.duration && item.duration !== 'TBA' ? item.duration : null, item.deliveryMode, item.level].filter(Boolean);
                                        return (
                                            <li
                                                key={item.slug}
                                                className={cn(
                                                    'flex flex-col gap-4 rounded-2xl border border-slate-200 bg-white p-4 shadow-card transition-opacity sm:flex-row',
                                                    busy && 'opacity-50',
                                                )}
                                            >
                                                <Link href={item.url} className="block overflow-hidden rounded-lg sm:flex-shrink-0" tabIndex={-1} aria-hidden="true">
                                                    <Img src={item.image} alt="" className="h-40 w-full object-cover sm:h-24 sm:w-32" loading="lazy" />
                                                </Link>
                                                <div className="flex flex-1 flex-col">
                                                    <p className="text-xs font-semibold uppercase tracking-wide text-slate-500">{item.trackTitle}</p>
                                                    <h2 className="mt-1 text-base font-bold text-skillup-navy">
                                                        <Link href={item.url} className="transition-colors hover:text-skillup-blue">{item.title}</Link>
                                                    </h2>
                                                    {item.summary && <p className="mt-1 line-clamp-2 text-sm leading-6 text-slate-600">{item.summary}</p>}
                                                    {meta.length > 0 && (
                                                        <div className="mt-2 flex flex-wrap gap-2">
                                                            {meta.map((m) => <Badge key={m} variant="neutral">{m}</Badge>)}
                                                        </div>
                                                    )}
                                                    <div className="mt-auto flex items-center justify-between pt-3">
                                                        <span className="text-base font-bold text-skillup-navy">{item.price}</span>
                                                        <button
                                                            type="button"
                                                            onClick={() => remove(item.slug)}
                                                            disabled={busy}
                                                            className="inline-flex min-h-11 items-center gap-1.5 rounded-md px-2 text-sm font-medium text-slate-500 transition-colors hover:text-red-600 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-red-400 disabled:opacity-60"
                                                            aria-label={`Remove ${item.title} from cart`}
                                                        >
                                                            {busy ? <Loader2 className="h-4 w-4 motion-safe:animate-spin" aria-hidden="true" /> : <Trash2 className="h-4 w-4" aria-hidden="true" />}
                                                            Remove
                                                        </button>
                                                    </div>
                                                </div>
                                            </li>
                                        );
                                    })}
                                </ul>

                                <Link href="/courses" className="mt-6 inline-flex items-center gap-1.5 text-sm font-semibold text-skillup-blue hover:underline">
                                    <ArrowRight className="h-4 w-4 rotate-180" aria-hidden="true" />
                                    Continue browsing courses
                                </Link>
                            </div>

                            {/* Order summary */}
                            <aside className="h-fit lg:sticky lg:top-24">
                                <div className="rounded-2xl border border-slate-200 bg-white p-6 shadow-card">
                                    <h2 className="text-lg font-bold text-skillup-navy">Order summary</h2>
                                    <dl className="mt-4 space-y-3 text-sm">
                                        <div className="flex items-center justify-between">
                                            <dt className="text-slate-600">Subtotal ({count} {count === 1 ? 'course' : 'courses'})</dt>
                                            <dd className="font-medium text-skillup-navy">{subtotal}</dd>
                                        </div>
                                    </dl>
                                    <div className="mt-4 flex items-center justify-between border-t border-slate-100 pt-4">
                                        <span className="text-base font-bold text-skillup-navy">Total</span>
                                        <span className="text-xl font-bold text-skillup-navy">{total || subtotal}</span>
                                    </div>

                                    <Link href="/cart/checkout" className={cn(buttonVariants({ size: 'lg' }), 'mt-5 w-full')}>
                                        <Lock className="h-4 w-4" aria-hidden="true" />
                                        Proceed to checkout
                                    </Link>

                                    <ul className="mt-5 space-y-2 text-xs text-slate-500">
                                        <li className="flex items-center gap-2">
                                            <ShieldCheck className="h-4 w-4 flex-shrink-0 text-emerald-500" aria-hidden="true" />
                                            Secure checkout — payments processed by Paystack
                                        </li>
                                        <li className="flex items-center gap-2">
                                            <ShoppingCart className="h-4 w-4 flex-shrink-0 text-slate-400" aria-hidden="true" />
                                            One payment for all your courses
                                        </li>
                                    </ul>
                                </div>
                            </aside>
                        </div>
                    )}
                </div>
            </section>

            {/* Cross-sell for non-empty carts */}
            {!empty && recommended.length > 0 && (
                <section className="border-t border-slate-200 bg-white py-14">
                    <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                        <h2 className="mb-8 text-2xl font-bold text-skillup-navy">You might also like</h2>
                        <div className="grid gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                            {recommended.map((product) => (
                                <CourseCard key={product.slug} product={product} />
                            ))}
                        </div>
                    </div>
                </section>
            )}
        </PublicLayout>
    );
}
