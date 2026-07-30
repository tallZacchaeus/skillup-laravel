import InputError from '@/Components/InputError';
import PublicLayout from '@/Components/public/PublicLayout';
import { Badge } from '@/Components/ui/badge';
import { Button, buttonVariants } from '@/Components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';
import { Input } from '@/Components/ui/input';
import { cn } from '@/lib/utils';
import { Head, Link, useForm } from '@inertiajs/react';
import { ArrowLeft, CreditCard, Mail, Phone, Tag, UserRound } from 'lucide-react';

export default function CheckoutDetails({ product }) {
    const hasPlans = product.paymentPlans.length > 0;
    const { data, setData, post, processing, errors } = useForm({
        name: '',
        email: '',
        phone: '',
        discount_code: '',
        payment_mode: 'full',
        product_payment_plan_id: '',
    });

    const choosePaymentMode = (mode) => {
        setData((values) => ({
            ...values,
            payment_mode: mode,
            product_payment_plan_id: mode === 'installment' ? values.product_payment_plan_id || product.paymentPlans[0]?.id || '' : '',
        }));
    };

    const submit = (event) => {
        event.preventDefault();
        post(`/checkout/${product.slug}/review`);
    };

    return (
        <PublicLayout>
            <Head title={`Checkout - ${product.title}`} />

            <section className="bg-skillup-navy pb-12 pt-32 text-white">
                <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <Link href={product.trackSlug ? `/courses/${product.trackSlug}/${product.slug}` : '/courses'} className="inline-flex items-center gap-2 text-sm font-semibold text-blue-100 hover:text-white">
                        <ArrowLeft className="h-4 w-4" />
                        Back to program
                    </Link>
                    <div className="mt-6 max-w-3xl">
                        <Badge className="bg-white/10 text-white ring-white/20">Secure enrollment</Badge>
                        <h1 className="mt-5 text-4xl font-bold leading-tight sm:text-5xl">{product.title}</h1>
                        <p className="mt-4 text-lg leading-8 text-blue-50">
                            Complete your details, apply any eligible discount code, and choose how you want to pay.
                        </p>
                    </div>
                </div>
            </section>

            <section className="bg-slate-50 py-16">
                <div className="mx-auto grid max-w-7xl gap-8 px-4 sm:px-6 lg:grid-cols-[1fr_0.7fr] lg:px-8">
                    <Card>
                        <CardHeader>
                            <CardTitle>Enrollment details</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <form className="space-y-6" onSubmit={submit}>
                                <Field label="Full name" error={errors.name} icon={UserRound}>
                                    <Input value={data.name} onChange={(event) => setData('name', event.target.value)} autoComplete="name" />
                                </Field>

                                <div className="grid gap-5 sm:grid-cols-2">
                                    <Field label="Email address" error={errors.email} icon={Mail}>
                                        <Input type="email" value={data.email} onChange={(event) => setData('email', event.target.value)} autoComplete="email" />
                                    </Field>
                                    <Field label="Phone number" error={errors.phone} icon={Phone}>
                                        <Input value={data.phone} onChange={(event) => setData('phone', event.target.value)} autoComplete="tel" />
                                    </Field>
                                </div>

                                <Field label="Discount code" error={errors.discount_code} icon={Tag}>
                                    <Input value={data.discount_code} onChange={(event) => setData('discount_code', event.target.value)} placeholder="Optional" />
                                </Field>

                                <div>
                                    <div className="mb-3 flex items-center gap-2 text-sm font-semibold text-skillup-navy">
                                        <CreditCard className="h-4 w-4 text-skillup-blue" />
                                        Payment option
                                    </div>
                                    <div className="grid gap-3 sm:grid-cols-2">
                                        <button
                                            type="button"
                                            onClick={() => choosePaymentMode('full')}
                                            className={cn(
                                                'rounded-md border p-4 text-left transition',
                                                data.payment_mode === 'full'
                                                    ? 'border-skillup-blue bg-blue-50 text-skillup-navy ring-2 ring-skillup-blue/20'
                                                    : 'border-slate-200 bg-white text-slate-700 hover:border-skillup-blue',
                                            )}
                                        >
                                            <span className="block font-semibold">Pay in full</span>
                                            <span className="mt-1 block text-sm text-slate-600">Complete payment once through Paystack.</span>
                                        </button>
                                        <button
                                            type="button"
                                            onClick={() => hasPlans && choosePaymentMode('installment')}
                                            disabled={!hasPlans}
                                            className={cn(
                                                'rounded-md border p-4 text-left transition disabled:cursor-not-allowed disabled:opacity-60',
                                                data.payment_mode === 'installment'
                                                    ? 'border-skillup-blue bg-blue-50 text-skillup-navy ring-2 ring-skillup-blue/20'
                                                    : 'border-slate-200 bg-white text-slate-700 hover:border-skillup-blue',
                                            )}
                                        >
                                            <span className="block font-semibold">Installment plan</span>
                                            <span className="mt-1 block text-sm text-slate-600">{hasPlans ? 'Start with a deposit and pay the balance over time.' : 'No installment plan is currently available.'}</span>
                                        </button>
                                    </div>
                                    <InputError message={errors.payment_mode || errors.product_payment_plan_id} className="mt-2" />
                                </div>

                                {data.payment_mode === 'installment' && (
                                    <div className="space-y-3">
                                        {product.paymentPlans.map((plan) => (
                                            <label
                                                key={plan.id}
                                                className={cn(
                                                    'block cursor-pointer rounded-md border bg-white p-4 transition',
                                                    String(data.product_payment_plan_id) === String(plan.id)
                                                        ? 'border-skillup-blue ring-2 ring-skillup-blue/20'
                                                        : 'border-slate-200 hover:border-skillup-blue',
                                                )}
                                            >
                                                <input
                                                    type="radio"
                                                    name="product_payment_plan_id"
                                                    value={plan.id}
                                                    checked={String(data.product_payment_plan_id) === String(plan.id)}
                                                    onChange={(event) => setData('product_payment_plan_id', event.target.value)}
                                                    className="sr-only"
                                                />
                                                <span className="font-semibold text-skillup-navy">{plan.name}</span>
                                                <span className="mt-1 block text-sm leading-6 text-slate-600">
                                                    {plan.deposit} deposit, then {plan.installment} {plan.interval}.
                                                </span>
                                            </label>
                                        ))}
                                    </div>
                                )}

                                <div className="flex flex-col gap-3 border-t border-slate-200 pt-6 sm:flex-row sm:items-center sm:justify-between">
                                    <Link href="/courses" className={buttonVariants({ variant: 'ghost' })}>
                                        Browse other programs
                                    </Link>
                                    <Button type="submit" size="lg" disabled={processing}>
                                        Review order
                                    </Button>
                                </div>
                            </form>
                        </CardContent>
                    </Card>

                    <aside className="space-y-5">
                        <Card>
                            <CardHeader>
                                <CardTitle>Order summary</CardTitle>
                            </CardHeader>
                            <CardContent className="space-y-4">
                                <div>
                                    <p className="text-sm font-semibold uppercase tracking-wide text-slate-500">Program</p>
                                    <p className="mt-1 text-lg font-bold text-skillup-navy">{product.title}</p>
                                </div>
                                <div className="grid gap-3 text-sm text-slate-600">
                                    <SummaryRow label="Track" value={product.track || 'General'} />
                                    <SummaryRow label="Level" value={product.level || 'Open'} />
                                    <SummaryRow label="Price" value={product.price || 'Pricing unavailable'} strong />
                                </div>
                            </CardContent>
                        </Card>
                    </aside>
                </div>
            </section>
        </PublicLayout>
    );
}

function Field({ label, error, icon: Icon, children }) {
    return (
        <label className="block">
            <span className="mb-2 flex items-center gap-2 text-sm font-semibold text-skillup-navy">
                <Icon className="h-4 w-4 text-skillup-blue" />
                {label}
            </span>
            {children}
            <InputError message={error} className="mt-2" />
        </label>
    );
}

function SummaryRow({ label, value, strong = false }) {
    return (
        <div className="flex items-center justify-between gap-4 border-t border-slate-100 pt-3">
            <span>{label}</span>
            <span className={cn('text-right', strong ? 'text-lg font-bold text-skillup-navy' : 'font-semibold text-slate-800')}>{value}</span>
        </div>
    );
}
