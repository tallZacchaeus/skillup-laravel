import PublicLayout from '@/Components/public/PublicLayout';
import { Badge } from '@/Components/ui/badge';
import { buttonVariants } from '@/Components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';
import { Head, Link } from '@inertiajs/react';
import { AlertCircle, CheckCircle2, Clock3, Hourglass, ReceiptText } from 'lucide-react';

const copy = {
    success: {
        title: 'Payment confirmed',
        description: 'Your payment has been recorded. Enrollment will be synced into the learner systems.',
        icon: CheckCircle2,
        badge: 'success',
    },
    pending: {
        title: 'Payment partly complete',
        description: 'Your payment was received and the remaining balance will follow the selected schedule.',
        icon: Hourglass,
        badge: 'warning',
    },
    processing: {
        title: 'Payment processing',
        description: 'We are waiting for final confirmation from Paystack. This page can be checked again shortly.',
        icon: Clock3,
        badge: 'neutral',
    },
    failed: {
        title: 'Payment not completed',
        description: 'The payment could not be verified. You can try again from the order review page or contact support.',
        icon: AlertCircle,
        badge: 'warning',
    },
};

export default function CheckoutStatus({ state, order }) {
    const stateCopy = copy[state] || copy.processing;
    const Icon = stateCopy.icon;

    return (
        <PublicLayout>
            <Head title={stateCopy.title} />

            <section className="bg-skillup-navy pb-12 pt-32 text-white">
                <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div className="flex h-14 w-14 items-center justify-center rounded-full bg-white/10 ring-1 ring-white/20">
                        <Icon className="h-7 w-7" />
                    </div>
                    <div className="mt-6 flex flex-wrap gap-2">
                        <Badge className="bg-white/10 text-white ring-white/20">{state}</Badge>
                        {order && <Badge className="bg-white/10 text-white ring-white/20">{order.orderNumber}</Badge>}
                    </div>
                    <h1 className="mt-5 text-4xl font-bold leading-tight sm:text-5xl">{stateCopy.title}</h1>
                    <p className="mt-4 max-w-2xl text-lg leading-8 text-blue-50">{stateCopy.description}</p>
                </div>
            </section>

            <section className="bg-slate-50 py-16">
                <div className="mx-auto grid max-w-7xl gap-8 px-4 sm:px-6 lg:grid-cols-[1fr_0.72fr] lg:px-8">
                    {order ? (
                        <>
                            <div className="space-y-6">
                                <Card>
                                    <CardHeader>
                                        <CardTitle>Order details</CardTitle>
                                    </CardHeader>
                                    <CardContent className="space-y-4">
                                        {order.items.map((item) => (
                                            <div key={item.title} className="flex flex-col gap-2 rounded-md border border-slate-200 p-4 sm:flex-row sm:items-center sm:justify-between">
                                                <div>
                                                    <p className="font-semibold text-skillup-navy">{item.title}</p>
                                                    <p className="mt-1 text-sm text-slate-600">{item.track || 'General track'}</p>
                                                </div>
                                                <p className="font-bold text-skillup-navy">{item.total}</p>
                                            </div>
                                        ))}
                                    </CardContent>
                                </Card>

                                {order.receipts.length > 0 && (
                                    <Card>
                                        <CardHeader>
                                            <CardTitle>Receipts</CardTitle>
                                        </CardHeader>
                                        <CardContent className="space-y-3">
                                            {order.receipts.map((receipt) => (
                                                <div key={receipt.number} className="flex items-center justify-between gap-4 rounded-md border border-slate-200 p-4 text-sm">
                                                    <div className="flex items-center gap-3">
                                                        <ReceiptText className="h-5 w-5 text-skillup-blue" />
                                                        <div>
                                                            <p className="font-semibold text-skillup-navy">{receipt.number}</p>
                                                            <p className="text-slate-600">{receipt.issuedAt}</p>
                                                        </div>
                                                    </div>
                                                    <p className="font-bold text-skillup-navy">{receipt.amount}</p>
                                                </div>
                                            ))}
                                        </CardContent>
                                    </Card>
                                )}
                            </div>

                            <aside className="space-y-6">
                                <Card>
                                    <CardHeader>
                                        <CardTitle>Balance</CardTitle>
                                    </CardHeader>
                                    <CardContent className="space-y-4">
                                        <SummaryRow label="Total" value={order.total} strong />
                                        <SummaryRow label="Paid" value={order.amountPaid} />
                                        <SummaryRow label="Balance due" value={order.balanceDue} strong />
                                        <div className="grid gap-3 pt-2">
                                            {Number(order.balanceDueAmount) > 0 && (
                                                <Link href={`/checkout/orders/${order.uuid}/review`} className={buttonVariants({ size: 'lg' })}>
                                                    Continue payment
                                                </Link>
                                            )}
                                            <Link href="/courses" className={buttonVariants({ variant: 'outline' })}>
                                                View programs
                                            </Link>
                                        </div>
                                    </CardContent>
                                </Card>

                                {order.installments.length > 0 && (
                                    <Card>
                                        <CardHeader>
                                            <CardTitle>Installments</CardTitle>
                                        </CardHeader>
                                        <CardContent className="space-y-3">
                                            {order.installments.map((installment) => (
                                                <div key={installment.number} className="rounded-md border border-slate-200 p-4 text-sm">
                                                    <div className="flex items-center justify-between gap-3">
                                                        <span className="font-semibold text-skillup-navy">Installment {installment.number}</span>
                                                        <Badge variant={installment.status === 'paid' ? 'success' : 'neutral'}>{installment.status}</Badge>
                                                    </div>
                                                    <div className="mt-2 flex items-center justify-between gap-3 text-slate-600">
                                                        <span>{installment.dueAt || 'Due date pending'}</span>
                                                        <span className="font-semibold text-slate-800">{installment.amount}</span>
                                                    </div>
                                                </div>
                                            ))}
                                        </CardContent>
                                    </Card>
                                )}
                            </aside>
                        </>
                    ) : (
                        <Card className="lg:col-span-2">
                            <CardContent className="flex flex-col gap-4 p-6 sm:flex-row sm:items-center sm:justify-between">
                                <div>
                                    <p className="font-semibold text-skillup-navy">No order was found for this payment attempt.</p>
                                    <p className="mt-1 text-sm text-slate-600">Start again from the programs page or contact support with your payment reference.</p>
                                </div>
                                <Link href="/courses" className={buttonVariants()}>
                                    View programs
                                </Link>
                            </CardContent>
                        </Card>
                    )}
                </div>
            </section>
        </PublicLayout>
    );
}

function SummaryRow({ label, value, strong = false }) {
    return (
        <div className="flex items-center justify-between gap-4 border-t border-slate-100 pt-3 text-sm first:border-t-0 first:pt-0">
            <span className="text-slate-600">{label}</span>
            <span className={strong ? 'text-lg font-bold text-skillup-navy' : 'font-semibold text-slate-800'}>{value}</span>
        </div>
    );
}
