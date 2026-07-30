import { Head, Link } from '@inertiajs/react';
import PublicLayout from '@/Components/public/PublicLayout';
import { buttonVariants } from '@/Components/ui/button';

export default function Welcome() {
    return (
        <PublicLayout>
            <Head title="Welcome" />
            <section className="bg-white px-4 py-32">
                <div className="mx-auto max-w-3xl text-center">
                    <h1 className="text-4xl font-bold text-skillup-navy">Welcome to SKILLUP</h1>
                    <p className="mt-4 text-lg leading-8 text-slate-600">
                        The Laravel/Inertia public shell now uses the SKILLUP pages. This fallback remains available for development only.
                    </p>
                    <div className="mt-8">
                        <Link href="/" className={buttonVariants()}>
                            Go to home
                        </Link>
                    </div>
                </div>
            </section>
        </PublicLayout>
    );
}
