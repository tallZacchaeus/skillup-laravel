import { Head, Link } from '@inertiajs/react';
import { ArrowRight, CheckCircle2, Sparkles } from 'lucide-react';
import PublicLayout from '@/Components/public/PublicLayout';
import { Badge } from '@/Components/ui/badge';
import { buttonVariants } from '@/Components/ui/button';

export default function Show({ module }) {
    const comingSoon = module.comingSoon;

    return (
        <PublicLayout>
            <Head title={`${module.name} — SkillUp`}>
                <meta name="description" content={module.summary} />
            </Head>

            <section className="bg-skillup-navy pb-16 pt-32 text-white">
                <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    {comingSoon ? (
                        <span className="inline-flex items-center gap-2 rounded-full bg-skillup-orange px-4 py-1.5 text-sm font-bold text-white">
                            <Sparkles className="h-4 w-4" aria-hidden="true" />
                            Coming soon
                        </span>
                    ) : (
                        <Badge className="bg-white/10 text-white ring-white/20">{module.moduleGroup}</Badge>
                    )}
                    <h1 className="mt-5 max-w-4xl text-4xl font-bold leading-tight sm:text-5xl">{module.name}</h1>
                    <p className="mt-5 max-w-2xl text-lg leading-8 text-blue-50">{module.summary}</p>
                </div>
            </section>

            {comingSoon ? (
                <section className="bg-white py-16">
                    <div className="mx-auto max-w-3xl px-4 text-center sm:px-6 lg:px-8">
                        <h2 className="text-2xl font-bold text-skillup-navy sm:text-3xl">We’re building this next</h2>
                        <p className="mx-auto mt-4 max-w-xl text-base leading-7 text-slate-600">
                            This is part of the SkillUp roadmap and isn’t live yet. In the meantime, explore our courses and
                            programs — or reach out and we’ll let you know the moment it launches.
                        </p>
                        <div className="mt-8 flex flex-col justify-center gap-3 sm:flex-row">
                            <Link href="/courses" className={buttonVariants({ size: 'lg' })}>
                                Explore courses
                                <ArrowRight className="h-4 w-4" aria-hidden="true" />
                            </Link>
                            <Link href="/contact" className={buttonVariants({ variant: 'outline', size: 'lg' })}>
                                Notify me
                            </Link>
                        </div>
                    </div>
                </section>
            ) : (
                <section className="bg-white py-16">
                    <div className="mx-auto grid max-w-7xl gap-10 px-4 sm:px-6 lg:grid-cols-[0.85fr_1.15fr] lg:px-8">
                        <div>
                            <h2 className="text-2xl font-bold text-slate-900">Launch readiness</h2>
                            <p className="mt-3 text-sm leading-6 text-slate-600">
                                This module expands the SkillUp platform once the academy core is stable in production.
                            </p>
                            <Link href="/contact" className={`${buttonVariants({ size: 'lg' })} mt-6`}>
                                Contact SkillUp
                                <ArrowRight className="h-4 w-4" aria-hidden="true" />
                            </Link>
                        </div>

                        <div className="rounded-lg border border-slate-200 bg-slate-50 p-6">
                            <h3 className="font-bold text-slate-900">Readiness checks</h3>
                            <div className="mt-5 space-y-4">
                                {(module.readinessChecks || []).map((item) => (
                                    <div key={item} className="flex gap-3">
                                        <CheckCircle2 className="mt-0.5 h-5 w-5 shrink-0 text-skillup-blue" aria-hidden="true" />
                                        <p className="text-sm leading-6 text-slate-700">{item}</p>
                                    </div>
                                ))}
                            </div>
                        </div>
                    </div>
                </section>
            )}
        </PublicLayout>
    );
}
