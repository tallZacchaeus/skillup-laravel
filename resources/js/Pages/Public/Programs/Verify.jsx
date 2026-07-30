import { Head, useForm, usePage } from '@inertiajs/react';
import { MailCheck } from 'lucide-react';
import PublicLayout from '@/Components/public/PublicLayout';

export default function Verify({ registration }) {
    const { flash } = usePage().props;
    const otpForm = useForm({ otp: '' });
    const resendForm = useForm({});

    const submitOtp = (e) => {
        e.preventDefault();
        otpForm.post(`/program-registrations/${registration.uuid}/verify-otp`);
    };

    const resend = () => {
        resendForm.post(`/program-registrations/${registration.uuid}/resend`, { preserveScroll: true });
    };

    return (
        <PublicLayout>
            <Head title="Confirm your email" />

            <div className="flex min-h-svh items-center justify-center bg-skillup-soft px-4 pb-16 pt-28">
                <div className="w-full max-w-md rounded-2xl bg-white p-8 shadow-lg">
                    <MailCheck className="mx-auto h-12 w-12 text-skillup-blue" aria-hidden="true" />
                    <h1 className="mt-4 text-center text-2xl font-bold text-skillup-navy">Check your email</h1>
                    <p className="mt-3 text-center text-sm leading-6 text-gray-600">
                        We sent a confirmation link and a 6-digit code to{' '}
                        <strong className="text-gray-900">{registration.guardianEmail}</strong>. Click the link, or type
                        the code below to continue to payment for <strong>{registration.participantName}</strong>.
                    </p>

                    {flash?.message && (
                        <p
                            role="status"
                            className={`mt-4 rounded-lg px-4 py-2 text-center text-sm font-medium ${
                                flash.type === 'error' ? 'bg-red-50 text-red-700' : 'bg-green-50 text-green-700'
                            }`}
                        >
                            {flash.message}
                        </p>
                    )}

                    <form onSubmit={submitOtp} className="mt-6">
                        <label htmlFor="otp" className="mb-1 block text-sm font-semibold text-slate-800">
                            6-digit code
                        </label>
                        <input
                            id="otp"
                            type="text"
                            inputMode="numeric"
                            autoComplete="one-time-code"
                            pattern="[0-9]{6}"
                            maxLength={6}
                            required
                            value={otpForm.data.otp}
                            onChange={(e) => otpForm.setData('otp', e.target.value.replace(/\D/g, ''))}
                            className="h-14 w-full rounded-md border-slate-300 text-center text-2xl font-bold tracking-[0.5em] text-slate-900 focus:border-skillup-blue focus:ring-skillup-blue"
                        />
                        {otpForm.errors.otp && <p className="mt-2 text-sm text-red-600">{otpForm.errors.otp}</p>}

                        <button
                            type="submit"
                            disabled={otpForm.processing || otpForm.data.otp.length !== 6}
                            className="mt-4 flex h-12 w-full items-center justify-center rounded-md bg-blue-900 text-base font-semibold text-white transition-colors hover:bg-blue-700 disabled:bg-slate-300"
                        >
                            {otpForm.processing ? 'Checking…' : 'Confirm and continue'}
                        </button>
                    </form>

                    <p className="mt-6 text-center text-sm text-gray-500">
                        Nothing arrived? Check your spam folder, or{' '}
                        <button
                            type="button"
                            onClick={resend}
                            disabled={resendForm.processing}
                            className="font-semibold text-skillup-blue hover:text-blue-800 disabled:text-slate-500"
                        >
                            resend the code
                        </button>
                        .
                    </p>
                </div>
            </div>
        </PublicLayout>
    );
}
