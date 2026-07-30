import { Head, Link } from '@inertiajs/react';
import PublicLayout from '@/Components/public/PublicLayout';
import Breadcrumbs from '@/Components/Breadcrumbs';
import { legalContent } from '@/data/legal';

/**
 * Shared informational / legal page (Privacy, Terms, Cookies, Careers).
 * Content is looked up from @/data/legal by the `kind` prop passed from the route.
 */
export default function Info({ kind }) {
    const doc = legalContent[kind] || { title: 'Information', intro: '', sections: [] };

    return (
        <PublicLayout>
            <Head title={doc.title} />

            <section className="bg-skillup-navy pb-14 pt-32 text-white">
                <div className="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
                    <Breadcrumbs items={[{ label: 'Home', href: '/' }, { label: doc.title }]} tone="light" />
                    <h1 className="mt-5 text-4xl font-bold sm:text-5xl">{doc.title}</h1>
                    {doc.updated && <p className="mt-3 text-sm text-blue-100">Last updated {doc.updated}</p>}
                </div>
            </section>

            <section className="bg-white py-16">
                <div className="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
                    {doc.intro && <p className="text-lg leading-8 text-slate-700">{doc.intro}</p>}

                    <div className="mt-10 space-y-8">
                        {doc.sections.map((section) => (
                            <div key={section.heading}>
                                <h2 className="text-xl font-bold text-skillup-navy">{section.heading}</h2>
                                <p className="mt-2 leading-7 text-slate-600">{section.body}</p>
                            </div>
                        ))}
                    </div>

                    <div className="mt-12 rounded-2xl border border-slate-200 bg-slate-50 p-6">
                        <p className="text-sm text-slate-600">
                            Have questions about this page?{' '}
                            <Link href="/contact" className="font-semibold text-skillup-blue hover:underline">
                                Contact our team
                            </Link>
                            .
                        </p>
                    </div>
                </div>
            </section>
        </PublicLayout>
    );
}
