import { Head, Link } from '@inertiajs/react';
import { ArrowLeft, Download, FileText } from 'lucide-react';
import PublicLayout from '@/Components/public/PublicLayout';
import Img from '@/Components/Img';
import Breadcrumbs from '@/Components/Breadcrumbs';
import StructuredData from '@/Components/StructuredData';
import ResourceCard from '@/Components/public/resources/ResourceCard';
import { cn } from '@/lib/utils';

const csrfToken = () => document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

const inputClass =
    'mt-1 block h-11 w-full rounded-md border border-slate-300 px-3 text-sm text-slate-900 shadow-sm outline-none transition focus:border-skillup-blue focus:ring-2 focus:ring-skillup-blue/20';

export default function ResourceShow({ resource, relatedResources = [] }) {
    const canonical = typeof window !== 'undefined' ? window.location.href : resource.url;
    const description = resource.description || '';
    const meta = [resource.fileType, resource.fileSize, resource.updatedLabel ? `Updated ${resource.updatedLabel}` : null].filter(Boolean);

    const schema = [
        {
            '@context': 'https://schema.org',
            '@type': 'DigitalDocument',
            name: resource.title,
            description,
            url: resource.url,
            ...(resource.fileType ? { fileFormat: resource.fileType } : {}),
            publisher: { '@type': 'Organization', name: 'SkillUp' },
        },
        {
            '@context': 'https://schema.org',
            '@type': 'BreadcrumbList',
            itemListElement: [
                { '@type': 'ListItem', position: 1, name: 'Resources', item: '/resources' },
                { '@type': 'ListItem', position: 2, name: resource.title, item: resource.url },
            ],
        },
    ];

    return (
        <PublicLayout>
            <Head title={`${resource.title} — SkillUp Resources`}>
                <meta head-key="description" name="description" content={description} />
                <link head-key="canonical" rel="canonical" href={canonical} />
                <meta head-key="og:type" property="og:type" content="article" />
                <meta head-key="og:title" property="og:title" content={resource.title} />
                <meta head-key="og:description" property="og:description" content={description} />
                <meta head-key="twitter:card" name="twitter:card" content="summary_large_image" />
            </Head>
            <StructuredData data={schema} />

            <section className="bg-skillup-navy pb-14 pt-28 text-white sm:pt-32">
                <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <Breadcrumbs
                        items={[
                            { label: 'Home', href: '/' },
                            { label: 'Resources', href: '/resources' },
                            { label: resource.category?.name || 'Resource' },
                        ]}
                        tone="light"
                    />
                </div>
            </section>

            <section className="bg-white py-12 sm:py-16">
                <div className="mx-auto grid max-w-7xl gap-10 px-4 sm:px-6 lg:grid-cols-[1.05fr_0.95fr] lg:px-8">
                    <div>
                        {resource.category && (
                            <span className="inline-flex items-center rounded-full bg-skillup-blue/10 px-3 py-1 text-xs font-semibold text-skillup-blue">
                                {resource.category.name}
                            </span>
                        )}
                        <h1 className="mt-4 text-3xl font-bold tracking-tight text-skillup-navy sm:text-4xl">{resource.title}</h1>
                        {resource.description && <p className="mt-5 max-w-3xl text-lg leading-8 text-slate-600">{resource.description}</p>}
                        {meta.length > 0 && (
                            <p className="mt-4 flex flex-wrap items-center gap-x-1.5 text-sm font-medium text-slate-500">
                                {meta.map((item, i) => (
                                    <span key={item} className="inline-flex items-center gap-1.5">
                                        {i > 0 && <span aria-hidden="true" className="text-slate-300">·</span>}
                                        {item}
                                    </span>
                                ))}
                            </p>
                        )}

                        <div className="mt-8 rounded-2xl border border-slate-200 bg-slate-50 p-6">
                            <h2 className="text-base font-bold text-skillup-navy">
                                {resource.isGated ? 'Get your free download' : 'Download this resource'}
                            </h2>
                            <p className="mt-1 text-sm leading-6 text-slate-600">
                                {resource.isGated
                                    ? 'Enter your details and your download will begin immediately.'
                                    : 'Click below and your download will begin immediately.'}
                            </p>

                            <form action={resource.downloadUrl} method="POST" className="mt-5 grid gap-4 sm:grid-cols-2">
                                <input type="hidden" name="_token" value={csrfToken()} />
                                {resource.isGated && (
                                    <>
                                        <div>
                                            <label htmlFor="dl-name" className="block text-sm font-semibold text-slate-700">
                                                Name <span className="ml-1 text-xs font-normal text-slate-400">(optional)</span>
                                            </label>
                                            <input id="dl-name" type="text" name="name" autoComplete="name" className={inputClass} />
                                        </div>
                                        <div>
                                            <label htmlFor="dl-email" className="block text-sm font-semibold text-slate-700">
                                                Work email <span className="text-red-500" aria-hidden="true">*</span>
                                            </label>
                                            <input id="dl-email" type="email" name="email" required autoComplete="email" className={inputClass} />
                                        </div>
                                    </>
                                )}
                                <div className="sm:col-span-2">
                                    <button
                                        type="submit"
                                        className={cn(
                                            'inline-flex h-11 w-full items-center justify-center gap-2 rounded-md bg-skillup-blue px-5 text-sm font-semibold text-white transition-colors hover:bg-blue-700 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-skillup-blue focus-visible:ring-offset-2 sm:w-auto',
                                        )}
                                    >
                                        <Download className="h-4 w-4" aria-hidden="true" />
                                        Download{resource.fileType ? ` (${resource.fileType})` : ''}
                                    </button>
                                </div>
                            </form>
                        </div>

                        <div className="mt-8">
                            <Link href="/resources" className="inline-flex items-center gap-2 text-sm font-semibold text-skillup-blue hover:underline">
                                <ArrowLeft className="h-4 w-4" aria-hidden="true" />
                                Back to all resources
                            </Link>
                        </div>
                    </div>

                    <div>
                        <div className="overflow-hidden rounded-2xl border border-slate-200 bg-slate-100 shadow-sm">
                            <Img src={resource.image} alt={resource.title} className="aspect-[4/3] w-full object-cover" eager />
                        </div>
                    </div>
                </div>
            </section>

            {relatedResources.length > 0 && (
                <section className="border-t border-slate-200 bg-slate-50 py-16">
                    <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                        <h2 className="mb-8 flex items-center gap-2 text-2xl font-bold text-skillup-navy">
                            <FileText className="h-6 w-6 text-skillup-blue" aria-hidden="true" />
                            Related resources
                        </h2>
                        <div className="grid gap-8 sm:grid-cols-2 lg:grid-cols-3">
                            {relatedResources.map((item) => (
                                <ResourceCard key={item.id} resource={item} />
                            ))}
                        </div>
                    </div>
                </section>
            )}
        </PublicLayout>
    );
}
