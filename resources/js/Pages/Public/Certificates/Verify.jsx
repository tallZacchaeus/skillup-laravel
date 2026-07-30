import { Head, Link, router } from '@inertiajs/react';
import { useState } from 'react';
import { BadgeCheck, ShieldAlert, ShieldCheck } from 'lucide-react';
import PublicLayout from '@/Components/public/PublicLayout';

export default function Verify({ serial: initialSerial, checked, certificate }) {
    const [serial, setSerial] = useState(initialSerial ?? '');

    const submit = (e) => {
        e.preventDefault();
        router.get('/certificates/verify', { serial: serial.trim().toUpperCase() }, { preserveState: true });
    };

    return (
        <PublicLayout>
            <Head title="Verify a certificate" />

            <div className="flex min-h-svh items-start justify-center bg-skillup-soft px-4 pb-16 pt-32">
                <div className="w-full max-w-lg">
                    <div className="rounded-2xl bg-white p-8 shadow-lg">
                        <ShieldCheck className="mx-auto h-12 w-12 text-skillup-blue" aria-hidden="true" />
                        <h1 className="mt-4 text-center text-2xl font-bold text-skillup-navy">Verify a certificate</h1>
                        <p className="mt-2 text-center text-sm leading-6 text-gray-600">
                            Enter the verification serial printed on a SkillUp certificate to confirm it's genuine.
                        </p>

                        <form onSubmit={submit} className="mt-6 flex gap-3">
                            <label htmlFor="serial" className="sr-only">
                                Certificate serial
                            </label>
                            <input
                                id="serial"
                                type="text"
                                required
                                placeholder="e.g. AB12-CD34-EF56"
                                value={serial}
                                onChange={(e) => setSerial(e.target.value)}
                                className="h-12 w-full rounded-md border-slate-300 font-mono uppercase tracking-widest focus:border-skillup-blue focus:ring-skillup-blue"
                            />
                            <button
                                type="submit"
                                className="h-12 flex-shrink-0 rounded-md bg-blue-900 px-6 text-sm font-semibold text-white hover:bg-blue-700"
                            >
                                Verify
                            </button>
                        </form>
                    </div>

                    {checked && certificate && (
                        <div className="mt-6 rounded-2xl border border-green-200 bg-green-50 p-6" role="status">
                            <div className="flex items-start gap-4">
                                <BadgeCheck className="h-8 w-8 flex-shrink-0 text-green-600" aria-hidden="true" />
                                <div>
                                    <h2 className="font-bold text-green-900">Genuine certificate</h2>
                                    <p className="mt-1 text-sm leading-6 text-green-800">
                                        <strong>{certificate.recipientName}</strong> completed{' '}
                                        <strong>{certificate.programTitle}</strong>
                                        {certificate.trackName && <> ({certificate.trackName} Track)</>} — issued {certificate.issuedOn}.
                                    </p>
                                    <Link
                                        href={`/certificates/${certificate.serial}`}
                                        className="mt-2 inline-block text-sm font-semibold text-green-700 underline hover:text-green-900"
                                    >
                                        View the certificate
                                    </Link>
                                </div>
                            </div>
                        </div>
                    )}

                    {checked && !certificate && (
                        <div className="mt-6 rounded-2xl border border-red-200 bg-red-50 p-6" role="status">
                            <div className="flex items-start gap-4">
                                <ShieldAlert className="h-8 w-8 flex-shrink-0 text-red-600" aria-hidden="true" />
                                <div>
                                    <h2 className="font-bold text-red-900">No match found</h2>
                                    <p className="mt-1 text-sm leading-6 text-red-800">
                                        We couldn't find a certificate with that serial. Check for typos — or contact us if
                                        you believe this certificate should be valid.
                                    </p>
                                </div>
                            </div>
                        </div>
                    )}
                </div>
            </div>
        </PublicLayout>
    );
}
