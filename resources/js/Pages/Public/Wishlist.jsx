import { Head, Link } from '@inertiajs/react';
import Img from '@/Components/Img';
import { ArrowRight, Heart } from 'lucide-react';
import PublicLayout from '@/Components/public/PublicLayout';
import WishlistButton from '@/Components/WishlistButton';
import { Badge } from '@/Components/ui/badge';
import { buttonVariants } from '@/Components/ui/button';
import { Card, CardContent } from '@/Components/ui/card';

export default function Wishlist({ products = [] }) {
    return (
        <PublicLayout>
            <Head title="My wishlist" />

            <section className="bg-skillup-navy pb-12 pt-32 text-white">
                <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <Badge className="bg-white/10 text-white ring-white/20">Saved for later</Badge>
                    <h1 className="mt-5 text-4xl font-bold sm:text-5xl">My wishlist</h1>
                    <p className="mt-4 text-lg text-blue-50">
                        {products.length > 0
                            ? `${products.length} ${products.length === 1 ? 'course' : 'courses'} saved. Enroll whenever you're ready.`
                            : 'Save courses you’re interested in and come back to them anytime.'}
                    </p>
                </div>
            </section>

            <section className="bg-slate-50 py-14">
                <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    {products.length === 0 ? (
                        <div className="rounded-2xl border border-dashed border-slate-300 bg-white p-12 text-center">
                            <Heart className="mx-auto h-10 w-10 text-slate-300" aria-hidden="true" />
                            <h2 className="mt-4 text-lg font-semibold text-skillup-navy">Your wishlist is empty</h2>
                            <p className="mt-2 text-sm text-slate-500">Browse the catalogue and tap the heart to save a course.</p>
                            <Link href="/courses" className={`${buttonVariants({ size: 'sm' })} mt-6`}>
                                Explore courses
                                <ArrowRight className="h-4 w-4" aria-hidden="true" />
                            </Link>
                        </div>
                    ) : (
                        <div className="grid gap-6 sm:grid-cols-2 xl:grid-cols-3">
                            {products.map((product) => (
                                <Card key={product.slug} className="flex flex-col overflow-hidden transition-all duration-300 hover:-translate-y-1 hover:shadow-lg">
                                    <div className="relative">
                                        <Img src={product.image} alt={product.title} className="h-48 w-full object-cover" loading="lazy" />
                                        <WishlistButton product={product} />
                                    </div>
                                    <CardContent className="flex flex-1 flex-col p-6">
                                        <div className="flex flex-wrap gap-2">
                                            <Badge>{product.category}</Badge>
                                            <Badge variant="neutral">{product.level}</Badge>
                                        </div>
                                        <p className="mt-4 text-xs font-semibold uppercase tracking-wide text-slate-500">{product.trackTitle}</p>
                                        <h2 className="mt-1 text-xl font-bold text-skillup-navy">{product.title}</h2>
                                        <p className="mt-3 flex-1 text-sm leading-6 text-slate-600">{product.summary}</p>
                                        <div className="mt-5 flex items-center justify-between">
                                            <span className="font-bold text-skillup-blue">{product.price}</span>
                                            <Link href={product.url} className={buttonVariants({ variant: 'outline', size: 'sm' })}>
                                                View course
                                                <ArrowRight className="h-4 w-4" aria-hidden="true" />
                                            </Link>
                                        </div>
                                    </CardContent>
                                </Card>
                            ))}
                        </div>
                    )}
                </div>
            </section>
        </PublicLayout>
    );
}
