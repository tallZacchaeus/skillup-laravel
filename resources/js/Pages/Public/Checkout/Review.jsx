import PublicLayout from '@/Components/public/PublicLayout';
import { Badge } from '@/Components/ui/badge';
import { Button, buttonVariants } from '@/Components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';
import { Head, Link, useForm } from '@inertiajs/react';
import { ArrowLeft, CreditCard, FileText, ShieldCheck } from 'lucide-react';

export default function CheckoutReview({ order }) {
    const { post, processing } = useForm({});

    const pay = (event) => {
        event.preventDefault();
        post(`/checkout/orders/${order.uuid}/pay`);
    };

    return (
        <PublicLayout>
            <Head title={`Review order ${order.orderNumber}`} />

            <section className="bg-skillup-navy pb-12 pt-32 text-white">
                <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <Link href="/courses" className="inline-flex items-center gap-2 text-sm font-semibold text-blue-100 hover:text-white">
                        <ArrowLeft className="h-4 w-4" />
                        Back to programs
                    </Link>
                    <div className="mt-6 flex flex-wrap gap-2">
                        <Badge className="bg-white/10 text-white ring-white/20">{order.orderNumber}</Badge>
                        <Badge className="bg-white/10 text-white ring-white/20">{order.paymentStatus}</Badge>
                    </div>
                    <h1 className="mt-5 text-4xl font-bold leading-tight sm:text-5xl">Review your order</h1>
                    <p className="mt-4 max-w-2xl text-lg leading-8 text-blue-50">
                        Confirm your details and continue to Paystack to complete payment securely.
                    </p>
                </div>
            </section>

            <section className="bg-slate-50 py-16">
                <div className="mx-auto grid max-w-7xl gap-8 px-4 sm:px-6 lg:grid-cols-[1fr_0.72fr] lg:px-8">
                    <div className="space-y-6">
                        <Card>
                            <CardHeader>
                                <CardTitle>Program</CardTitle>
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

                        {order.installments.length > 0 && (
                            <Card>
                                <CardHeader>
                                    <CardTitle>Installment schedule</CardTitle>
                                </CardHeader>
                                <CardContent className="space-y-3">
                                    {order.installments.map((installment) => (
                                        <div key={installment.number} className="grid gap-2 rounded-md border border-slate-200 p-4 text-sm sm:grid-cols-4 sm:items-center">
                                            <span className="font-semibold text-skillup-navy">Installment {installment.number}</span>
                                            <span>{installment.amount}</span>
                                            <span>{installment.dueAt || 'Due date pending'}</span>
                                            <Badge variant={installment.status === 'paid' ? 'success' : 'neutral'} className="w-fit">{installment.status}</Badge>
                                        </div>
                                    ))}
                                </CardContent>
                            </Card>
                        )}
                    </div>

                    <aside className="space-y-6">
                        <Card>
                            <CardHeader>
                                <CardTitle>Payment summary</CardTitle>
                            </CardHeader>
                            <CardContent className="space-y-4">
                                <SummaryRow label="Subtotal" value={order.subtotal} />
                                <SummaryRow label="Discount" value={order.discountTotal} />
                                <SummaryRow label="Total" value={order.total} strong />
                                <SummaryRow label="Paid" value={order.amountPaid} />
                                <SummaryRow label="Due now" value={order.payableAmount} strong />
                                <form onSubmit={pay} className="pt-2">
                                    <Button type="submit" size="lg" className="w-full" disabled={processing}>
                                        <CreditCard className="h-5 w-5" />
                                        Pay with Paystack
                                    </Button>
                                </form>
                            </CardContent>
                        </Card>

                        <Card>
                            <CardHeader>
                                <CardTitle>Customer</CardTitle>
                            </CardHeader>
                            <CardContent className="space-y-3 text-sm text-slate-700">
                                <p className="font-semibold text-skillup-navy">{order.customer.name}</p>
                                <p>{order.customer.email}</p>
                                {order.customer.phone && <p>{order.customer.phone}</p>}
                                <div className="flex items-start gap-2 border-t border-slate-100 pt-4 text-slate-600">
                                    <ShieldCheck className="mt-0.5 h-4 w-4 text-emerald-600" />
                                    <p>Payment confirmation creates a receipt and queues the learner for LMS enrollment.</p>
                                </div>
                                <div className="flex items-start gap-2 text-slate-600">
                                    <FileText className="mt-0.5 h-4 w-4 text-skillup-blue" />
                                    <p>Your invoice remains available from the learner account area.</p>
                                </div>
                            </CardContent>
                        </Card>
                    </aside>
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
