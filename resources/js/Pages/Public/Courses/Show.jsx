import { Head } from '@inertiajs/react';
import { Briefcase, Users } from 'lucide-react';
import PublicLayout from '@/Components/public/PublicLayout';
import SectionHeading from '@/Components/public/SectionHeading';
import FaqAccordion from '@/Components/public/FaqAccordion';
import StructuredData from '@/Components/StructuredData';
import CourseCard from '@/Components/public/courses/CourseCard';
import CourseHero from '@/Components/public/course/CourseHero';
import CourseTrustBar from '@/Components/public/course/CourseTrustBar';
import CurriculumList from '@/Components/public/course/CurriculumList';
import OutcomeGrid from '@/Components/public/course/OutcomeGrid';
import ToolBadges from '@/Components/public/course/ToolBadges';
import PillGrid from '@/Components/public/course/PillGrid';
import CourseCtaSection from '@/Components/public/course/CourseCtaSection';
import { buildCourseFaqs, getCourseContent } from '@/data/courseContent';

const EMPTY_SEO = { title: '', description: '', canonical: '', ogImage: '' };

export default function CourseShow({ track, related = [], seo = EMPTY_SEO, structuredData = null }) {
    const products = track.products || [];
    const content = getCourseContent(track);
    const faqs = buildCourseFaqs(track);
    const curriculum = products[0]?.syllabus || [];
    const primaryUrl = products[0]?.url || '#curriculum';

    return (
        <PublicLayout>
            <Head title={seo.title || track.title}>
                <meta head-key="description" name="description" content={seo.description} />
                {seo.canonical && <link head-key="canonical" rel="canonical" href={seo.canonical} />}
                <meta head-key="og:type" property="og:type" content="website" />
                <meta head-key="og:title" property="og:title" content={seo.title || track.title} />
                <meta head-key="og:description" property="og:description" content={seo.description} />
                {seo.canonical && <meta head-key="og:url" property="og:url" content={seo.canonical} />}
                {seo.ogImage && <meta head-key="og:image" property="og:image" content={seo.ogImage} />}
                <meta head-key="twitter:card" name="twitter:card" content="summary_large_image" />
                <meta head-key="twitter:title" name="twitter:title" content={seo.title || track.title} />
                <meta head-key="twitter:description" name="twitter:description" content={seo.description} />
            </Head>
            <StructuredData data={structuredData} />

            <CourseHero track={track} valueProp={content.valueProp} primaryUrl={primaryUrl} />
            <CourseTrustBar />

            {/* Overview */}
            {(track.description || track.summary) && (
                <section className="bg-white py-16 sm:py-20">
                    <div className="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
                        <SectionHeading align="left" eyebrow="Overview" title={`About the ${track.title} track`} />
                        <p className="mt-6 text-lg leading-8 text-slate-600">{track.description || track.summary}</p>
                    </div>
                </section>
            )}

            {/* Curriculum */}
            {curriculum.length > 0 && (
                <section id="curriculum" className="scroll-mt-24 bg-slate-50 py-16 sm:py-20">
                    <div className="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
                        <SectionHeading eyebrow="Curriculum" title="What you’ll cover" description="A practical, project-based path from foundations to a portfolio piece." />
                        <div className="mt-10">
                            <CurriculumList modules={curriculum} />
                        </div>
                    </div>
                </section>
            )}

            {/* Learning outcomes */}
            {track.outcomes?.length > 0 && (
                <section className="bg-white py-16 sm:py-20">
                    <div className="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
                        <SectionHeading eyebrow="Outcomes" title="What you’ll be able to do" />
                        <div className="mt-10">
                            <OutcomeGrid outcomes={track.outcomes} />
                        </div>
                    </div>
                </section>
            )}

            {/* Tools + audience */}
            <section className="bg-slate-50 py-16 sm:py-20">
                <div className="mx-auto grid max-w-6xl gap-12 px-4 sm:px-6 lg:grid-cols-2 lg:px-8">
                    {track.tools?.length > 0 && (
                        <div>
                            <SectionHeading align="left" eyebrow="Tools" title="Tools you’ll use" />
                            <div className="mt-6">
                                <ToolBadges tools={track.tools} />
                            </div>
                        </div>
                    )}
                    {content.whoFor.length > 0 && (
                        <div>
                            <SectionHeading align="left" eyebrow="Who it’s for" title="Ideal for" />
                            <div className="mt-6">
                                <PillGrid items={content.whoFor} icon={Users} />
                            </div>
                        </div>
                    )}
                </div>
            </section>

            {/* Career paths — hidden when we have no accurate roles */}
            {content.careers.length > 0 && (
                <section className="bg-white py-16 sm:py-20">
                    <div className="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
                        <SectionHeading
                            eyebrow="Career opportunities"
                            title="Where this can take you"
                            description="Roles this track helps you prepare for. Outcomes depend on your effort and experience."
                        />
                        <div className="mt-10">
                            <PillGrid items={content.careers} icon={Briefcase} />
                        </div>
                    </div>
                </section>
            )}

            {/* Enrollable levels */}
            {products.length > 0 && (
                <section className="bg-slate-50 py-16 sm:py-20">
                    <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                        <SectionHeading eyebrow="Enroll" title="Choose your level" description="Start at the level that fits you — you can progress as you grow." />
                        <div className="mt-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                            {products.map((product, i) => (
                                <CourseCard key={product.slug} product={product} priority={i < 3} />
                            ))}
                        </div>
                    </div>
                </section>
            )}

            {/* Related courses */}
            {related.length > 0 && (
                <section className="bg-white py-16 sm:py-20">
                    <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                        <SectionHeading eyebrow="Explore more" title="Students also learn" />
                        <div className="mt-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                            {related.map((product) => (
                                <CourseCard key={product.slug} product={product} />
                            ))}
                        </div>
                    </div>
                </section>
            )}

            {/* FAQ */}
            {faqs.length > 0 && (
                <section className="bg-slate-50 py-16 sm:py-20">
                    <div className="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
                        <SectionHeading eyebrow="FAQ" title="Frequently asked questions" />
                        <div className="mt-10">
                            <FaqAccordion items={faqs} />
                        </div>
                    </div>
                </section>
            )}

            <CourseCtaSection track={track} primaryUrl={primaryUrl} />
        </PublicLayout>
    );
}
