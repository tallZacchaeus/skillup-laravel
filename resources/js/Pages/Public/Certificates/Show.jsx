import { Head } from '@inertiajs/react';
import Img from '@/Components/Img';
import { Printer } from 'lucide-react';

/**
 * Printable Certificate of Participation. Deliberately self-contained
 * (no PublicLayout) so browser print / save-as-PDF produces a clean sheet.
 */
export default function Show({ certificate }) {
    return (
        <div className="min-h-svh bg-slate-100 px-4 py-10 print:bg-white print:p-0">
            <Head title={`Certificate — ${certificate.recipientName}`} />

            <div className="mx-auto max-w-4xl">
                <div className="mb-6 flex items-center justify-between print:hidden">
                    <Img src="/images/skillUp.png" alt="SkillUp Edtech" className="h-8 w-auto" />
                    <button
                        type="button"
                        onClick={() => window.print()}
                        className="inline-flex h-11 items-center gap-2 rounded-md bg-blue-900 px-5 text-sm font-semibold text-white hover:bg-blue-700"
                    >
                        <Printer className="h-4 w-4" aria-hidden="true" />
                        Print / Save as PDF
                    </button>
                </div>

                <div className="relative overflow-hidden rounded-2xl border-8 border-skillup-navy bg-white p-10 shadow-xl print:rounded-none print:border-4 print:shadow-none sm:p-16">
                    <div className="absolute left-0 top-0 h-3 w-full bg-skillup-blue" aria-hidden="true" />
                    <div className="absolute bottom-0 right-0 h-40 w-40 rounded-tl-full bg-skillup-soft" aria-hidden="true" />

                    <div className="relative text-center">
                        <Img src="/images/skillUp.png" alt="SkillUp Edtech" className="mx-auto h-10 w-auto" />
                        <p className="mt-8 text-sm font-semibold uppercase tracking-[0.3em] text-gray-500">
                            Certificate of Participation
                        </p>
                        <h1 className="mt-6 font-serif text-4xl font-bold text-skillup-navy sm:text-5xl">
                            {certificate.recipientName}
                        </h1>
                        <p className="mx-auto mt-6 max-w-xl text-base leading-7 text-gray-600">
                            successfully completed all four weeks of
                            <span className="block mt-1 text-xl font-bold text-skillup-navy">{certificate.programTitle}</span>
                            {certificate.trackName && <span className="block mt-1 text-sm font-semibold text-gray-500">{certificate.trackName} Track</span>}
                        </p>

                        <div className="mt-10 flex items-end justify-between text-left">
                            <div>
                                <p className="text-xs uppercase tracking-wide text-gray-400">Issued</p>
                                <p className="mt-1 text-sm font-semibold text-gray-800">{certificate.issuedOn}</p>
                            </div>
                            <div className="text-right">
                                <p className="text-xs uppercase tracking-wide text-gray-400">Verification serial</p>
                                <p className="mt-1 font-mono text-sm font-bold tracking-widest text-skillup-blue">{certificate.serial}</p>
                                <p className="mt-1 text-xs text-gray-400">Verify at {certificate.verifyUrl.replace(/^https?:\/\//, '')}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    );
}
