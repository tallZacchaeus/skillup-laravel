import { useState } from 'react';
import Img from '@/Components/Img';
import { Head, Link, useForm } from '@inertiajs/react';
import { BadgeCheck, CheckCircle2, Clock, CreditCard, Layers, Play, Star, Target, Users } from 'lucide-react';
import PublicLayout from '@/Components/public/PublicLayout';
import AddToCartButton from '@/Components/AddToCartButton';
import Breadcrumbs from '@/Components/Breadcrumbs';
import StructuredData from '@/Components/StructuredData';
import WishlistButton from '@/Components/WishlistButton';
import { Badge } from '@/Components/ui/badge';
import { buttonVariants } from '@/Components/ui/button';
import { Card, CardContent } from '@/Components/ui/card';

export default function ProductShow({ product, structuredData = null, canReview = false, myReview = null }) {
    const rating = product.rating || { average: 0, count: 0 };
    const relevance = product.relevance || null;
    const canonical = typeof window !== 'undefined' ? window.location.href.split('?')[0] : undefined;

    return (
        <PublicLayout>
            <Head title={product.title}>
                <meta name="description" content={product.summary} />
                {canonical && <link rel="canonical" href={canonical} />}
                <meta property="og:title" content={product.title} />
                <meta property="og:description" content={product.summary} />
                <meta property="og:image" content={product.image} />
            </Head>
            <StructuredData data={structuredData} />

            {/* Hero */}
            <section className="bg-skillup-navy pb-16 pt-32 text-white">
                <div className="mx-auto grid max-w-7xl gap-10 px-4 sm:px-6 lg:grid-cols-[1fr_0.9fr] lg:px-8">
                    <div>
                        <Breadcrumbs
                            tone="light"
                            items={[
                                { label: 'Courses', href: '/courses' },
                                { label: product.trackTitle, href: product.trackUrl },
                                { label: product.title },
                            ]}
                        />
                        <div className="mt-6 flex flex-wrap gap-2">
                            <Badge className="bg-white/10 text-white ring-white/20">{product.category}</Badge>
                            <Badge className="bg-white/10 text-white ring-white/20">{product.level}</Badge>
                            <Badge className="bg-white/10 text-white ring-white/20">{product.deliveryMode}</Badge>
                        </div>
                        <h1 className="mt-5 max-w-4xl text-4xl font-bold leading-tight sm:text-5xl">{product.title}</h1>
                        <p className="mt-5 max-w-2xl text-lg leading-8 text-blue-50">{product.subtitle || product.description}</p>

                        {product.tags?.length > 0 && (
                            <div className="mt-5 flex flex-wrap gap-2">
                                {product.tags.map((tag) => (
                                    <span key={tag.slug} className="rounded-full bg-white/10 px-3 py-1 text-xs font-medium text-blue-50 ring-1 ring-white/20">
                                        {tag.name}
                                    </span>
                                ))}
                            </div>
                        )}

                        {/* Social proof */}
                        <div className="mt-6 flex flex-wrap items-center gap-x-6 gap-y-2 text-sm">
                            {rating.count > 0 && (
                                <span className="inline-flex items-center gap-2">
                                    <Stars value={rating.average} />
                                    <span className="font-bold text-amber-300">{rating.average.toFixed(1)}</span>
                                    <span className="text-blue-200">({rating.count} reviews)</span>
                                </span>
                            )}
                            {product.studentsCount > 0 && (
                                <span className="inline-flex items-center gap-2 text-blue-100">
                                    <Users className="h-4 w-4" aria-hidden="true" />
                                    {product.studentsLabel} learners enrolled
                                </span>
                            )}
                        </div>

                        <div className="mt-8 flex flex-col gap-3 sm:flex-row">
                            <Link href={`/checkout/${product.slug}`} className={buttonVariants({ size: 'lg' })}>
                                Buy now
                            </Link>
                            <AddToCartButton product={product} className="text-white" />
                            <WishlistButton product={product} variant="button" className="bg-white/5 text-white hover:bg-white/10" />
                            <Link href="/corporate" className={buttonVariants({ variant: 'secondary', size: 'lg' })}>
                                Corporate seats
                            </Link>
                        </div>
                    </div>

                    {/* Preview video or image */}
                    <div className="overflow-hidden rounded-xl bg-black/30 shadow-2xl">
                        {product.promoVideo ? (
                            <VideoPlayer video={product.promoVideo} title={product.title} poster={product.image} />
                        ) : (
                            <Img src={product.image} alt={product.title} className="h-full min-h-80 w-full object-cover" eager />
                        )}
                    </div>
                </div>
            </section>

            {/* Quick stats */}
            <section className="bg-white py-12">
                <div className="mx-auto grid max-w-7xl gap-6 px-4 sm:px-6 md:grid-cols-4 lg:px-8">
                    <Info icon={CreditCard} title="Price" value={product.price} />
                    <Info icon={Clock} title="Duration" value={product.duration} />
                    <Info icon={Layers} title="Level" value={product.level} />
                    <Info icon={Users} title="Enrolled" value={product.studentsCount > 0 ? `${product.studentsLabel} learners` : product.seats} />
                </div>
            </section>

            {/* Relevance — why this course matters */}
            {relevance && (
                <section className="bg-skillup-soft py-16">
                    <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                        <div className="grid gap-10 lg:grid-cols-[1fr_0.9fr]">
                            <div>
                                <div className="inline-flex items-center gap-2 rounded-full bg-skillup-blue/10 px-4 py-1.5 text-sm font-semibold text-skillup-blue">
                                    <Target className="h-4 w-4" aria-hidden="true" />
                                    Why this course
                                </div>
                                <h2 className="mt-4 text-3xl font-bold text-skillup-navy">Is this course right for you?</h2>
                                {relevance.demandNote && <p className="mt-4 text-lg leading-8 text-slate-600">{relevance.demandNote}</p>}

                                {(relevance.audience || []).length > 0 && (
                                    <div className="mt-6 space-y-3">
                                        {relevance.audience.map((who) => (
                                            <div key={who} className="flex gap-3">
                                                <CheckCircle2 className="mt-0.5 h-5 w-5 shrink-0 text-emerald-600" aria-hidden="true" />
                                                <span className="text-slate-700">{who}</span>
                                            </div>
                                        ))}
                                    </div>
                                )}
                            </div>

                            {(relevance.stats || []).length > 0 && (
                                <div className="grid grid-cols-1 gap-4 sm:grid-cols-3 lg:grid-cols-1 lg:content-start">
                                    {relevance.stats.map((stat) => (
                                        <div key={stat.label} className="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-100">
                                            <p className="text-2xl font-bold text-skillup-blue">{stat.value}</p>
                                            <p className="mt-1 text-sm font-medium text-slate-500">{stat.label}</p>
                                        </div>
                                    ))}
                                </div>
                            )}
                        </div>
                    </div>
                </section>
            )}

            {/* Outcomes + cohort */}
            <section className="bg-white py-16">
                <div className="mx-auto grid max-w-7xl gap-8 px-4 sm:px-6 lg:grid-cols-[1fr_0.8fr] lg:px-8">
                    <Card>
                        <CardContent className="p-6">
                            <h2 className="text-2xl font-bold text-skillup-navy">What you'll be able to do</h2>
                            <div className="mt-5 grid gap-3 sm:grid-cols-2">
                                {(product.outcomes || []).map((outcome) => (
                                    <div key={outcome} className="flex gap-3">
                                        <CheckCircle2 className="mt-0.5 h-5 w-5 shrink-0 text-emerald-600" />
                                        <span className="text-slate-700">{outcome}</span>
                                    </div>
                                ))}
                            </div>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardContent className="p-6">
                            <h2 className="text-2xl font-bold text-skillup-navy">Cohort</h2>
                            {product.cohort ? (
                                <div className="mt-5 space-y-3 text-sm leading-6 text-slate-700">
                                    <p className="font-semibold text-skillup-navy">{product.cohort.title}</p>
                                    <p>Starts: {product.cohort.startsAt || 'TBA'}</p>
                                    <p>Enrollment closes: {product.cohort.enrollmentClosesAt || 'TBA'}</p>
                                    <p>{product.seats}</p>
                                </div>
                            ) : (
                                <p className="mt-5 text-sm leading-6 text-slate-600">Cohort schedule will be published soon.</p>
                            )}
                        </CardContent>
                    </Card>
                </div>
            </section>

            {/* Syllabus + tools */}
            <section className="bg-slate-50 py-16">
                <div className="mx-auto grid max-w-7xl gap-8 px-4 sm:px-6 lg:grid-cols-2 lg:px-8">
                    <Card>
                        <CardContent className="p-6">
                            <h2 className="text-2xl font-bold text-skillup-navy">Syllabus</h2>
                            <div className="mt-5 space-y-4">
                                {(product.syllabus || []).map((item, index) => (
                                    <div key={`${item.week}-${item.title}`} className="rounded-md border border-slate-200 bg-white p-4">
                                        <p className="text-xs font-semibold uppercase tracking-wide text-slate-500">Week {item.week || index + 1}</p>
                                        <p className="mt-1 font-semibold text-skillup-navy">{item.title}</p>
                                    </div>
                                ))}
                            </div>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardContent className="p-6">
                            <h2 className="text-2xl font-bold text-skillup-navy">Tools and payment options</h2>
                            <div className="mt-5 flex flex-wrap gap-2">
                                {(product.tools || []).map((tool) => (
                                    <span key={tool} className="inline-flex items-center gap-2 rounded-full bg-white px-4 py-2 text-sm font-semibold text-slate-700 ring-1 ring-slate-200">
                                        {tool}
                                    </span>
                                ))}
                            </div>
                            <div className="mt-6 space-y-3">
                                {(product.paymentPlans || []).map((plan) => (
                                    <div key={plan.name} className="rounded-md border border-slate-200 bg-white p-4">
                                        <p className="font-semibold text-skillup-navy">{plan.name}</p>
                                        <p className="mt-1 text-sm text-slate-600">
                                            {plan.deposit} deposit, then {plan.installment} {plan.interval.toLowerCase()}.
                                        </p>
                                    </div>
                                ))}
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </section>

            {/* Reviews */}
            {(product.reviewsSummary || canReview) && (
                <section className="bg-white py-16">
                    <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                        <h2 className="text-3xl font-bold text-skillup-navy">Learner reviews</h2>

                        {canReview && (
                            <ReviewForm slug={product.slug} myReview={myReview} />
                        )}

                        {product.reviewsSummary ? (
                            <div className="mt-8 grid gap-10 lg:grid-cols-[0.6fr_1fr]">
                                <ReviewsSummary summary={product.reviewsSummary} />
                                <div className="space-y-5">
                                    {(product.reviews || []).map((review) => (
                                        <ReviewCard key={review.id} review={review} />
                                    ))}
                                </div>
                            </div>
                        ) : (
                            <p className="mt-6 text-slate-500">No reviews yet — be the first to share your experience.</p>
                        )}
                    </div>
                </section>
            )}

            {/* Closing CTA */}
            <section className="px-4 py-12">
                <div className="mx-auto flex max-w-[1296px] flex-col items-center gap-5 rounded-2xl bg-skillup-deep px-6 py-14 text-center">
                    <h2 className="text-3xl font-bold text-white sm:text-4xl">Ready to start {product.title}?</h2>
                    <p className="max-w-2xl text-lg text-blue-100">Join {product.studentsLabel || 'thousands of'} learners building real, in-demand skills.</p>
                    <Link href={`/checkout/${product.slug}`} className="inline-flex h-12 items-center justify-center rounded-md bg-white px-8 text-base font-semibold text-blue-900 shadow-sm transition hover:bg-blue-50">
                        Enroll now — {product.price}
                    </Link>
                </div>
            </section>
        </PublicLayout>
    );
}

function VideoPlayer({ video, title, poster }) {
    const [playing, setPlaying] = useState(false);

    if (video.provider === 'file') {
        return <video src={video.url} controls poster={poster} className="aspect-video h-full w-full bg-black" />;
    }

    // Facade: show the course poster + play button; load the embed only on click
    // (no black-box void if the embed is slow/blocked, and no third-party iframe until wanted).
    if (!playing) {
        return (
            <button
                type="button"
                onClick={() => setPlaying(true)}
                aria-label={`Play ${title} preview`}
                className="group relative block aspect-video w-full overflow-hidden bg-skillup-navy"
            >
                <Img src={poster} alt="" className="h-full w-full object-cover opacity-80 transition group-hover:opacity-100" />
                <span className="absolute inset-0 flex items-center justify-center">
                    <span className="flex h-16 w-16 items-center justify-center rounded-full bg-white/90 text-skillup-navy shadow-lg transition group-hover:scale-105">
                        <Play className="ml-1 h-7 w-7 fill-current" aria-hidden="true" />
                    </span>
                </span>
            </button>
        );
    }

    const src = video.embedUrl + (video.embedUrl.includes('?') ? '&' : '?') + 'autoplay=1';

    return (
        <div className="relative aspect-video w-full">
            <iframe
                src={src}
                title={`${title} preview`}
                className="absolute inset-0 h-full w-full"
                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                allowFullScreen
                loading="lazy"
            />
        </div>
    );
}

function ReviewForm({ slug, myReview }) {
    const { data, setData, post, processing, errors, recentlySuccessful } = useForm({
        rating: myReview?.rating || 0,
        title: myReview?.title || '',
        body: myReview?.body || '',
    });

    const submit = (e) => {
        e.preventDefault();
        post(route('courses.reviews.store', slug), { preserveScroll: true });
    };

    return (
        <form onSubmit={submit} className="mt-6 rounded-2xl border border-slate-200 bg-slate-50 p-6">
            <div className="flex items-center gap-2">
                <BadgeCheck className="h-5 w-5 text-skillup-blue" aria-hidden="true" />
                <h3 className="text-lg font-bold text-skillup-navy">{myReview ? 'Update your review' : 'Write a review'}</h3>
            </div>
            <p className="mt-1 text-sm text-slate-500">You’re a verified learner on this course.</p>

            <div className="mt-4">
                <label className="mb-1 block text-sm font-semibold text-slate-700">Your rating</label>
                <StarInput value={data.rating} onChange={(v) => setData('rating', v)} />
                {errors.rating && <p className="mt-1 text-sm text-red-600">{errors.rating}</p>}
            </div>

            <div className="mt-4">
                <label htmlFor="review-title" className="mb-1 block text-sm font-semibold text-slate-700">Title (optional)</label>
                <input
                    id="review-title"
                    type="text"
                    value={data.title}
                    onChange={(e) => setData('title', e.target.value)}
                    maxLength={120}
                    className="h-11 w-full rounded-md border-slate-300 text-slate-900 focus:border-skillup-blue focus:ring-skillup-blue"
                />
            </div>

            <div className="mt-4">
                <label htmlFor="review-body" className="mb-1 block text-sm font-semibold text-slate-700">Your review</label>
                <textarea
                    id="review-body"
                    rows={4}
                    value={data.body}
                    onChange={(e) => setData('body', e.target.value)}
                    className="w-full rounded-md border-slate-300 text-slate-900 focus:border-skillup-blue focus:ring-skillup-blue"
                    placeholder="What did you learn? Would you recommend it?"
                />
                {errors.body && <p className="mt-1 text-sm text-red-600">{errors.body}</p>}
            </div>

            <div className="mt-5 flex items-center gap-4">
                <button
                    type="submit"
                    disabled={processing || data.rating === 0}
                    className="inline-flex h-11 items-center justify-center rounded-md bg-skillup-blue px-6 text-sm font-semibold text-white transition-colors hover:bg-blue-700 disabled:bg-slate-300"
                >
                    {myReview ? 'Update review' : 'Post review'}
                </button>
                {recentlySuccessful && <span className="text-sm font-medium text-emerald-600">Saved. Thank you!</span>}
            </div>
        </form>
    );
}

function StarInput({ value, onChange }) {
    const [hover, setHover] = useState(0);

    return (
        <div className="flex gap-1" role="radiogroup" aria-label="Rating">
            {[1, 2, 3, 4, 5].map((i) => (
                <button
                    key={i}
                    type="button"
                    aria-label={`${i} star${i > 1 ? 's' : ''}`}
                    aria-pressed={value === i}
                    onClick={() => onChange(i)}
                    onMouseEnter={() => setHover(i)}
                    onMouseLeave={() => setHover(0)}
                    className="p-2"
                >
                    <Star className={`h-7 w-7 transition-colors ${i <= (hover || value) ? 'fill-amber-400 text-amber-400' : 'fill-transparent text-slate-300'}`} aria-hidden="true" />
                </button>
            ))}
        </div>
    );
}

function Stars({ value = 0, className = 'h-4 w-4' }) {
    const rounded = Math.round(value);
    return (
        <span className="inline-flex" aria-label={`${value} out of 5 stars`}>
            {[1, 2, 3, 4, 5].map((i) => (
                <Star key={i} className={`${className} ${i <= rounded ? 'fill-amber-400 text-amber-400' : 'fill-transparent text-amber-300/40'}`} aria-hidden="true" />
            ))}
        </span>
    );
}

function ReviewsSummary({ summary }) {
    const total = summary.count || 1;
    return (
        <div className="rounded-2xl bg-skillup-soft p-8 text-center">
            <p className="text-5xl font-bold text-skillup-navy">{summary.average.toFixed(1)}</p>
            <div className="mt-2 flex justify-center">
                <Stars value={summary.average} className="h-5 w-5" />
            </div>
            <p className="mt-2 text-sm text-slate-500">{summary.count} learner reviews</p>

            <div className="mt-6 space-y-2 text-left">
                {[5, 4, 3, 2, 1].map((star) => {
                    const count = summary.distribution?.[star] || 0;
                    const pct = Math.round((count / total) * 100);
                    return (
                        <div key={star} className="flex items-center gap-3 text-xs text-slate-500">
                            <span className="w-8 shrink-0">{star} ★</span>
                            <span className="h-2 flex-1 overflow-hidden rounded-full bg-slate-200">
                                <span className="block h-full rounded-full bg-amber-400" style={{ width: `${pct}%` }} />
                            </span>
                            <span className="w-8 shrink-0 text-right">{count}</span>
                        </div>
                    );
                })}
            </div>
        </div>
    );
}

function ReviewCard({ review }) {
    return (
        <div className="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <div className="flex items-start justify-between gap-4">
                <div className="flex items-center gap-3">
                    <div className="flex h-11 w-11 items-center justify-center rounded-full bg-skillup-blue/10 text-base font-bold text-skillup-blue">
                        {review.name.charAt(0)}
                    </div>
                    <div>
                        <p className="flex items-center gap-1.5 font-semibold text-skillup-navy">
                            {review.name}
                            {review.verified && <BadgeCheck className="h-4 w-4 text-skillup-blue" aria-label="Verified learner" />}
                        </p>
                        {review.title && <p className="text-xs text-slate-500">{review.title}</p>}
                    </div>
                </div>
                <Stars value={review.rating} />
            </div>
            {review.heading && <p className="mt-4 font-semibold text-skillup-navy">{review.heading}</p>}
            <p className="mt-1 text-sm leading-6 text-slate-600">{review.body}</p>
            {review.date && <p className="mt-3 text-xs text-slate-500">{review.date}</p>}
        </div>
    );
}

function Info({ icon: Icon, title, value }) {
    return (
        <div className="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
            <Icon className="h-6 w-6 text-skillup-blue" />
            <h2 className="mt-4 text-sm font-semibold uppercase tracking-wide text-slate-500">{title}</h2>
            <p className="mt-2 text-xl font-bold text-skillup-navy">{value}</p>
        </div>
    );
}
