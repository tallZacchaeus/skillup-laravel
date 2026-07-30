import { useState } from 'react';
import Img from '@/Components/Img';
import { CheckCircle2 } from 'lucide-react';

/**
 * Shared newsletter CTA band (home + blog). Self-contained: manages
 * its own submit state and posts to leads.newsletter, with an aria-live success
 * panel. Copy is overridable via props.
 */
export default function NewsletterCta({
    eyebrow = 'Newsletter',
    heading = 'Get weekly tech insights',
    description = 'Join thousands of African tech learners and never miss the latest opportunities, tips, and success stories shaping the future of tech talent.',
}) {
    const [email, setEmail] = useState('');
    const [success, setSuccess] = useState(null);
    const [error, setError] = useState(null);
    const [loading, setLoading] = useState(false);

    const subscribe = async (event) => {
        event.preventDefault();
        setLoading(true);
        setSuccess(null);
        setError(null);

        try {
            const response = await fetch(route('leads.newsletter'), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                },
                body: JSON.stringify({ email }),
            });

            const data = await response.json().catch(() => null);
            if (response.ok && data?.success) {
                setSuccess(data.message || 'You’re in! Check your inbox to confirm.');
                setEmail('');
            } else {
                setError(data?.message || 'Something went wrong. Please try again.');
            }
        } catch (err) {
            setError('Failed to subscribe. Please try again.');
        } finally {
            setLoading(false);
        }
    };

    return (
        <section className="bg-white px-4 py-12 sm:py-16">
            <div className="relative mx-auto flex w-full max-w-6xl flex-col items-center justify-center gap-6 overflow-hidden rounded-3xl bg-skillup-deep px-6 py-16 text-center sm:px-12">
                <Img
                    src="/images/Shape.png"
                    alt=""
                    className="pointer-events-none absolute bottom-0 right-0 h-auto max-h-full w-auto max-w-full opacity-80"
                    aria-hidden="true"
                    loading="lazy"
                />

                <span data-reveal className="z-10 inline-flex items-center rounded-full bg-white/10 px-4 py-1 text-xs font-semibold uppercase tracking-wide text-blue-100">
                    {eyebrow}
                </span>
                <h2 data-reveal className="z-10 max-w-2xl text-3xl font-bold text-white sm:text-4xl">
                    {heading}
                </h2>
                <p data-reveal className="z-10 max-w-2xl font-montserrat text-base leading-relaxed text-blue-50 sm:text-lg">
                    {description}
                </p>

                {success ? (
                    <div
                        className="z-10 flex items-center gap-3 rounded-xl bg-white/10 px-6 py-4 text-white motion-safe:animate-fade-in-up"
                        role="status"
                        aria-live="polite"
                    >
                        <CheckCircle2 className="h-6 w-6 text-emerald-300" aria-hidden="true" />
                        <span className="text-sm font-medium">{success}</span>
                    </div>
                ) : (
                    <>
                        <form onSubmit={subscribe} className="z-10 flex w-full max-w-md flex-col gap-3 sm:flex-row" data-reveal>
                            <label htmlFor="newsletter-email" className="sr-only">
                                Email address
                            </label>
                            <input
                                id="newsletter-email"
                                name="email"
                                type="email"
                                required
                                autoComplete="email"
                                value={email}
                                onChange={(e) => setEmail(e.target.value)}
                                placeholder="Enter your email"
                                disabled={loading}
                                className="h-12 min-w-0 flex-auto rounded-md border-0 bg-white/10 px-4 text-white shadow-sm ring-1 ring-inset ring-white/20 placeholder:text-blue-100 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-white"
                            />
                            <button
                                type="submit"
                                disabled={loading}
                                className="inline-flex h-12 flex-none items-center justify-center rounded-md bg-white px-8 text-base font-semibold text-blue-900 shadow-sm transition hover:bg-blue-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-white/70 disabled:bg-slate-200 disabled:text-slate-500"
                            >
                                {loading ? 'Subscribing…' : 'Subscribe'}
                            </button>
                        </form>
                        {error && (
                            <p className="z-10 text-sm font-medium text-red-200" role="status" aria-live="polite">
                                {error}
                            </p>
                        )}
                    </>
                )}
            </div>
        </section>
    );
}
