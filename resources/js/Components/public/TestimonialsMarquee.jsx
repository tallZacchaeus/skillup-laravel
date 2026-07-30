import { Quote } from 'lucide-react';
import { useMarquee } from '@/lib/animations';
import Img from '@/Components/Img';
import SectionHeading from '@/Components/public/SectionHeading';
import { alumniTestimonials } from '@/data/homeContent';

export default function TestimonialsMarquee({ testimonials = [] }) {
    // De-duplicate by a stable key so seeded + fallback data never repeats the
    // same person. The single row is duplicated once ONLY to loop seamlessly;
    // the cloned set is hidden from assistive tech.
    const source = testimonials.length > 0 ? testimonials : alumniTestimonials;
    const items = Array.from(
        new Map(source.map((t) => [t.id ?? `${t.name ?? t.student_name}-${t.quote?.slice(0, 24)}`, t])).values(),
    );
    const row = useMarquee({ duration: 60 });

    return (
        <section className="overflow-hidden bg-skillup-soft py-16 sm:py-20" aria-label="Alumni testimonials">
            <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <SectionHeading
                    data-reveal
                    eyebrow="Alumni stories"
                    title="What our alumni are saying"
                    description="From beginners with big dreams to professionals seeking an edge, our graduates are living proof that Africa’s digital future is here."
                />
            </div>

            <div className="edge-fade-x mt-14 overflow-hidden py-4">
                <div ref={row} className="flex w-max gap-6 px-4">
                    {[...items, ...items].map((testimonial, index) => (
                        <TestimonialCard
                            key={`${testimonial.id ?? testimonial.student_name ?? testimonial.name}-${index}`}
                            testimonial={testimonial}
                            hidden={index >= items.length}
                        />
                    ))}
                </div>
            </div>
        </section>
    );
}

function TestimonialCard({ testimonial, hidden = false }) {
    const name = testimonial.name || testimonial.student_name || 'Alumnus';
    const role = testimonial.role || testimonial.course_title || '';
    const { company, program, gradYear } = testimonial;

    const avatar = testimonial.avatar_path
        ? testimonial.avatar_path.startsWith('/')
            ? testimonial.avatar_path
            : `/storage/${testimonial.avatar_path}`
        : null;
    const initials = name
        .split(' ')
        .map((part) => part[0])
        .slice(0, 2)
        .join('')
        .toUpperCase();

    const meta = [program, gradYear ? `Class of ${gradYear}` : null].filter(Boolean).join(' • ');

    return (
        <figure
            aria-hidden={hidden || undefined}
            className="flex w-[300px] flex-shrink-0 flex-col rounded-2xl border border-blue-100 bg-white p-6 shadow-card transition-shadow duration-300 hover:shadow-card-hover md:w-[360px]"
        >
            <Quote className="h-8 w-8 flex-shrink-0 text-skillup-blue/25" aria-hidden="true" />
            <blockquote className="mt-3 flex-1 text-sm leading-relaxed text-slate-700">{testimonial.quote}</blockquote>
            <figcaption className="mt-6 flex items-center gap-3 border-t border-slate-100 pt-4">
                {avatar ? (
                    <Img src={avatar} alt="" className="h-11 w-11 rounded-full object-cover" />
                ) : (
                    <span className="flex h-11 w-11 flex-shrink-0 items-center justify-center rounded-full bg-skillup-blue/10 text-xs font-bold text-skillup-blue">
                        {initials}
                    </span>
                )}
                <div className="min-w-0">
                    <p className="truncate text-sm font-semibold text-skillup-navy">{name}</p>
                    <p className="truncate text-xs text-slate-500">
                        {role}
                        {company ? ` · ${company}` : ''}
                    </p>
                    {meta && <p className="mt-0.5 truncate text-[11px] font-medium text-skillup-blue">{meta}</p>}
                </div>
            </figcaption>
        </figure>
    );
}
