import { Head, Link } from '@inertiajs/react';
import { ArrowLeft, Calendar, Clock } from 'lucide-react';
import PublicLayout from '@/Components/public/PublicLayout';
import Img from '@/Components/Img';
import Breadcrumbs from '@/Components/Breadcrumbs';
import StructuredData from '@/Components/StructuredData';
import BlogPostCard from '@/Components/public/blog/BlogPostCard';

export default function BlogShow({ post, relatedPosts = [], structuredData = null }) {
    const description = post.summary || '';
    const canonical = typeof window !== 'undefined' ? window.location.href : post.url;
    const ogImage = post.image?.startsWith('http') ? post.image : `${typeof window !== 'undefined' ? window.location.origin : ''}${post.image}`;

    return (
        <PublicLayout>
            <Head title={`${post.title} — SkillUp Blog`}>
                <meta name="description" content={description} />
                <link rel="canonical" href={canonical} />
                <meta property="og:type" content="article" />
                <meta property="og:title" content={post.title} />
                <meta property="og:description" content={description} />
                <meta property="og:image" content={ogImage} />
                {post.publishedAt && <meta property="article:published_time" content={post.publishedAt} />}
                <meta name="twitter:card" content="summary_large_image" />
                <meta name="twitter:title" content={post.title} />
                <meta name="twitter:description" content={description} />
                <meta name="twitter:image" content={ogImage} />
            </Head>
            <StructuredData data={structuredData} />

            <article className="bg-white">
                <header className="bg-skillup-navy pb-14 pt-28 text-white sm:pt-32">
                    <div className="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
                        <Breadcrumbs
                            items={[
                                { label: 'Home', href: '/' },
                                { label: 'Blog', href: '/blog' },
                                { label: post.category?.name || 'Article' },
                            ]}
                            tone="light"
                        />
                        {post.category && (
                            <span className="mt-5 inline-flex items-center rounded-full bg-white/10 px-3 py-1 text-xs font-semibold text-blue-100">
                                {post.category.name}
                            </span>
                        )}
                        <h1 className="mt-4 text-3xl font-bold leading-tight tracking-tight sm:text-4xl md:text-5xl">
                            {post.title}
                        </h1>
                        <div className="mt-4 flex flex-wrap items-center gap-4 text-sm text-blue-100">
                            {post.dateLabel && (
                                <span className="inline-flex items-center gap-1.5">
                                    <Calendar className="h-4 w-4" aria-hidden="true" />
                                    {post.dateLabel}
                                </span>
                            )}
                            <span className="inline-flex items-center gap-1.5">
                                <Clock className="h-4 w-4" aria-hidden="true" />
                                {post.readingMinutes} min read
                            </span>
                        </div>
                    </div>
                </header>

                <div className="mx-auto max-w-3xl px-4 py-12 sm:px-6 lg:px-8">
                    {post.summary && (
                        <p className="border-l-4 border-skillup-blue pl-4 text-lg italic leading-8 text-slate-600">
                            {post.summary}
                        </p>
                    )}

                    <Img
                        src={post.image}
                        alt={post.title}
                        className="mt-8 aspect-[16/9] w-full rounded-2xl object-cover shadow-md"
                        eager
                    />

                    <div
                        className="prose prose-slate mt-10 max-w-none leading-8 text-slate-700"
                        dangerouslySetInnerHTML={{ __html: post.content }}
                    />

                    <div className="mt-12 border-t border-slate-200 pt-6">
                        <Link href="/blog" className="inline-flex items-center gap-2 text-sm font-semibold text-skillup-blue hover:underline">
                            <ArrowLeft className="h-4 w-4" aria-hidden="true" />
                            Back to all articles
                        </Link>
                    </div>
                </div>
            </article>

            {relatedPosts.length > 0 && (
                <section className="border-t border-slate-200 bg-slate-50 py-16">
                    <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                        <h2 className="mb-8 text-2xl font-bold text-skillup-navy">Related articles</h2>
                        <div className="grid gap-8 sm:grid-cols-2 lg:grid-cols-3">
                            {relatedPosts.map((related) => (
                                <BlogPostCard key={related.id} post={related} />
                            ))}
                        </div>
                    </div>
                </section>
            )}
        </PublicLayout>
    );
}
