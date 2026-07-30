import { Link } from '@inertiajs/react';
import { ArrowRight, Clock } from 'lucide-react';
import Img from '@/Components/Img';

/**
 * Blog article card. Fixed image ratio + flex column keeps cards equal height;
 * only renders metadata that exists (no fabricated author). `priority` eager-loads
 * the image for the first row (above the fold).
 */
export default function BlogPostCard({ post, priority = false }) {
    return (
        <article className="group flex h-full flex-col overflow-hidden rounded-2xl bg-white shadow-card ring-1 ring-slate-100 transition-all duration-300 hover:-translate-y-1 hover:shadow-card-hover">
            <Link href={post.url} className="relative block aspect-[16/10] overflow-hidden bg-slate-100" tabIndex={-1} aria-hidden="true">
                <Img
                    src={post.image}
                    alt=""
                    className="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105"
                    eager={priority}
                />
                {post.category && (
                    <span className="absolute left-4 top-4 rounded-full bg-white/90 px-3 py-1 text-xs font-semibold text-skillup-navy shadow-sm backdrop-blur-sm">
                        {post.category.name}
                    </span>
                )}
            </Link>
            <div className="flex flex-1 flex-col p-6">
                <div className="flex items-center gap-2 text-xs text-slate-500">
                    {post.dateLabel && (
                        <>
                            <span>{post.dateLabel}</span>
                            <span aria-hidden="true">·</span>
                        </>
                    )}
                    <span className="inline-flex items-center gap-1">
                        <Clock className="h-3.5 w-3.5" aria-hidden="true" />
                        {post.readingMinutes} min read
                    </span>
                </div>
                <h3 className="mt-3 line-clamp-2 text-lg font-bold leading-snug text-skillup-navy">
                    <Link href={post.url} className="transition-colors hover:text-skillup-blue focus-visible:underline focus-visible:outline-none">
                        {post.title}
                    </Link>
                </h3>
                {post.summary && <p className="mt-3 line-clamp-2 text-sm leading-6 text-slate-600">{post.summary}</p>}
                <div className="mt-5 flex-1" />
                <Link
                    href={post.url}
                    className="inline-flex items-center gap-1 border-t border-slate-100 pt-4 text-sm font-semibold text-skillup-blue"
                    aria-label={`Read more: ${post.title}`}
                >
                    Read more
                    <ArrowRight className="h-4 w-4 transition-transform group-hover:translate-x-0.5" aria-hidden="true" />
                </Link>
            </div>
        </article>
    );
}
