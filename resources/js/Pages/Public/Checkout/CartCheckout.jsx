import { Head, Link, useForm } from '@inertiajs/react';
import Img from '@/Components/Img';
import { ArrowRight, Lock } from 'lucide-react';
import PublicLayout from '@/Components/public/PublicLayout';
import { Badge } from '@/Components/ui/badge';

export default function CartCheckout({ items = [], subtotal, count = 0, customer = {} }) {
    const { data, setData, post, processing, errors } = useForm({
        name: customer.name || '',
        email: customer.email || '',
        phone: customer.phone || '',
        discount_code: '',
    });

    const submit = (e) => {
        e.preventDefault();
        post(route('cart.checkout.store'));
    };

    return (
        <PublicLayout>
            <Head title="Checkout" />

            <section className="bg-skillup-navy pb-12 pt-32 text-white">
                <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <Badge className="bg-white/10 text-white ring-white/20">Checkout</Badge>
                    <h1 className="mt-5 text-4xl font-bold sm:text-5xl">Complete your order</h1>
                    <p className="mt-4 text-lg text-blue-50">{count} {count === 1 ? 'course' : 'courses'} · one secure payment.</p>
                </div>
            </section>

            <section className="bg-slate-50 py-14">
                <div className="mx-auto grid max-w-7xl gap-8 px-4 sm:px-6 lg:grid-cols-[1fr_380px] lg:px-8">
                    {/* Customer details */}
                    <form onSubmit={submit} className="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                        <h2 className="text-xl font-bold text-skillup-navy">Your details</h2>
                        <p className="mt-1 text-sm text-slate-500">We’ll send your receipt and course access here.</p>

                        {errors.cart && (
                            <div className="mt-4 rounded-md bg-red-50 p-3 text-sm font-medium text-red-700">{errors.cart}</div>
                        )}

                        <div className="mt-5 space-y-4">
                            <Field label="Full name" error={errors.name} required>
                                <input type="text" required aria-required="true" autoComplete="name" value={data.name} onChange={(e) => setData('name', e.target.value)} className="h-12 w-full rounded-md border-slate-300 text-slate-900 focus:border-skillup-blue focus:ring-skillup-blue" />
                            </Field>
                            <Field label="Email address" error={errors.email} required>
                                <input type="email" required aria-required="true" autoComplete="email" value={data.email} onChange={(e) => setData('email', e.target.value)} className="h-12 w-full rounded-md border-slate-300 text-slate-900 focus:border-skillup-blue focus:ring-skillup-blue" />
                            </Field>
                            <Field label="Phone (optional)" error={errors.phone}>
                                <input type="tel" autoComplete="tel" value={data.phone} onChange={(e) => setData('phone', e.target.value)} className="h-12 w-full rounded-md border-slate-300 text-slate-900 focus:border-skillup-blue focus:ring-skillup-blue" />
                            </Field>
                            <Field label="Discount code (optional)" error={errors.discount}>
                                <input type="text" value={data.discount_code} onChange={(e) => setData('discount_code', e.target.value)} placeholder="Enter a promo code" className="h-12 w-full rounded-md border-slate-300 uppercase text-slate-900 placeholder:normal-case focus:border-skillup-blue focus:ring-skillup-blue" />
                            </Field>
                        </div>

                        <button type="submit" disabled={processing} className="mt-6 inline-flex h-12 w-full items-center justify-center gap-2 rounded-md bg-skillup-blue px-6 text-base font-semibold text-white transition-colors hover:bg-blue-700 disabled:bg-slate-300">
                            {processing ? 'Preparing…' : 'Continue to payment'}
                            <ArrowRight className="h-4 w-4" aria-hidden="true" />
                        </button>
                        <p className="mt-3 flex items-center justify-center gap-1.5 text-xs text-slate-500">
                            <Lock className="h-3.5 w-3.5" aria-hidden="true" />
                            Secured by Paystack
                        </p>
                    </form>

                    {/* Summary */}
                    <aside className="h-fit rounded-2xl border border-slate-200 bg-white p-6 shadow-sm lg:sticky lg:top-24">
                        <h2 className="text-lg font-bold text-skillup-navy">Order summary</h2>
                        <div className="mt-4 space-y-4">
                            {items.map((item) => (
                                <div key={item.slug} className="flex gap-3">
                                    <Img src={item.image} alt="" className="h-14 w-20 flex-shrink-0 rounded-md object-cover" loading="lazy" />
                                    <div className="flex flex-1 items-start justify-between gap-2">
                                        <div>
                                            <p className="text-sm font-semibold leading-5 text-skillup-navy">{item.title}</p>
                                            <p className="text-xs text-slate-500">{item.level}</p>
                                        </div>
                                        <span className="whitespace-nowrap text-sm font-semibold text-skillup-navy">{item.price}</span>
                                    </div>
                                </div>
                            ))}
                        </div>
                        <div className="mt-5 flex items-center justify-between border-t border-slate-100 pt-4">
                            <span className="font-semibold text-slate-600">Total</span>
                            <span className="text-xl font-bold text-skillup-blue">{subtotal}</span>
                        </div>
                        <Link href="/cart" className="mt-4 block text-center text-sm font-semibold text-skillup-blue hover:underline">
                            Edit cart
                        </Link>
                    </aside>
                </div>
            </section>
        </PublicLayout>
    );
}

function Field({ label, error, required, children }) {
    return (
        <label className="block">
            <span className="mb-1 block text-sm font-semibold text-slate-700">
                {label}
                {required && <span className="text-red-500" aria-hidden="true"> *</span>}
            </span>
            {children}
            {error && <span className="mt-1 block text-sm text-red-600">{error}</span>}
        </label>
    );
}
