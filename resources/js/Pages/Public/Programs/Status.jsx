import { Head, Link, useForm, usePage } from '@inertiajs/react';
import { CheckCircle2, CircleDashed } from 'lucide-react';
import PublicLayout from '@/Components/public/PublicLayout';

const STEPS = [
    { key: 'verify', label: 'Email confirmed' },
    { key: 'pay', label: 'Payment' },
    { key: 'profile', label: 'Onboarding form' },
];

export default function Status({ registration, onboardingUrl }) {
    const { flash } = usePage().props;
    const payForm = useForm({});

    const paidOrBeyond = ['paid', 'profile_completed', 'enrolled', 'completed'].includes(registration.status);
    const profileDone = ['profile_completed', 'enrolled', 'completed'].includes(registration.status);

    const stepState = {
        verify: registration.emailVerified,
        pay: paidOrBeyond,
        profile: profileDone,
    };

    const pay = () => payForm.post(`/program-registrations/${registration.uuid}/pay`);

    return (
        <PublicLayout>
            <Head title={`${registration.participantName} — ${registration.editionTitle}`} />

            <div className="flex min-h-svh items-center justify-center bg-skillup-soft px-4 pb-16 pt-28">
                <div className="w-full max-w-lg rounded-2xl bg-white p-8 shadow-lg">
                    <p className="text-sm font-semibold uppercase tracking-wide text-skillup-blue">{registration.editionTitle}</p>
                    <h1 className="mt-2 text-2xl font-bold text-skillup-navy">
                        {registration.participantName} · {registration.trackName}
                    </h1>

                    {flash?.message && (
                        <p role="status" className="mt-4 rounded-lg bg-blue-50 px-4 py-2 text-sm font-medium text-blue-800">
                            {flash.message}
                        </p>
                    )}

                    {registration.status === 'waitlisted' ? (
                        <p className="mt-6 rounded-xl bg-amber-50 p-4 text-sm leading-6 text-amber-800">
                            This track is currently full, so {registration.participantName} is on the waitlist. We'll reach
                            out on WhatsApp the moment a seat opens.
                        </p>
                    ) : (
                        <ol className="mt-6 space-y-4">
                            {STEPS.map((step) => (
                                <li key={step.key} className="flex items-center gap-3">
                                    {stepState[step.key] ? (
                                        <CheckCircle2 className="h-6 w-6 flex-shrink-0 text-green-600" aria-hidden="true" />
                                    ) : (
                                        <CircleDashed className="h-6 w-6 flex-shrink-0 text-slate-300" aria-hidden="true" />
                                    )}
                                    <span className={`text-sm font-semibold ${stepState[step.key] ? 'text-gray-900' : 'text-gray-500'}`}>
                                        {step.label}
                                    </span>
                                </li>
                            ))}
                        </ol>
                    )}

                    <div className="mt-8 space-y-3">
                        {!registration.emailVerified && registration.status !== 'waitlisted' && (
                            <Link
                                href={`/program-registrations/${registration.uuid}/verify-email`}
                                className="flex h-12 w-full items-center justify-center rounded-md bg-blue-900 text-base font-semibold text-white transition-colors hover:bg-blue-700"
                            >
                                Confirm your email
                            </Link>
                        )}

                        {registration.emailVerified && !paidOrBeyond && registration.status !== 'waitlisted' && (
                            <button
                                type="button"
                                onClick={pay}
                                disabled={payForm.processing}
                                className="flex h-12 w-full items-center justify-center rounded-md bg-blue-900 text-base font-semibold text-white transition-colors hover:bg-blue-700 disabled:bg-slate-300"
                            >
                                {payForm.processing ? 'Preparing checkout…' : 'Continue to payment'}
                            </button>
                        )}
                        {payForm.errors.registration && <p className="text-sm text-red-600">{payForm.errors.registration}</p>}

                        {paidOrBeyond && !profileDone && onboardingUrl && (
                            <a
                                href={onboardingUrl}
                                className="flex h-12 w-full items-center justify-center rounded-md bg-blue-900 text-base font-semibold text-white transition-colors hover:bg-blue-700"
                            >
                                Complete the onboarding form
                            </a>
                        )}

                        {profileDone && (
                            <p className="rounded-xl bg-green-50 p-4 text-sm leading-6 text-green-800">
                                All done — {registration.participantName}'s seat is confirmed and onboarding is complete.
                                We'll be in touch with programme updates. 🎉
                            </p>
                        )}

                        <Link
                            href={`/programs/${registration.programSlug}`}
                            className="block text-center text-sm font-semibold text-skillup-blue hover:text-blue-800"
                        >
                            Back to {registration.programName}
                        </Link>
                    </div>
                </div>
            </div>
        </PublicLayout>
    );
}
