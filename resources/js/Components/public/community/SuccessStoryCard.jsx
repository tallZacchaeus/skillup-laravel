import { Quote } from 'lucide-react';
import Img from '@/Components/Img';

/**
 * Reusable community success-story card: photo, name, role, and quote. Rendered
 * only from verified data — the page hides the whole section when none exists,
 * so this component never shows a fabricated placeholder. Falls back to an
 * initial monogram when a story has no photo.
 */
export default function SuccessStoryCard({ story }) {
    return (
        <figure className="flex h-full flex-col rounded-2xl border border-slate-200 bg-white p-6 shadow-card transition-all duration-300 hover:-translate-y-1 hover:shadow-card-hover">
            <Quote className="h-8 w-8 text-skillup-blue/40" aria-hidden="true" />
            <blockquote className="mt-3 flex-1 text-sm leading-6 text-gray-700">“{story.quote}”</blockquote>
            <figcaption className="mt-5 flex items-center gap-3 border-t border-slate-100 pt-5">
                {story.photo ? (
                    <Img src={story.photo} alt="" className="h-11 w-11 rounded-full object-cover" />
                ) : (
                    <span className="flex h-11 w-11 items-center justify-center rounded-full bg-skillup-blue/10 text-sm font-bold text-skillup-blue" aria-hidden="true">
                        {story.name?.charAt(0) ?? '★'}
                    </span>
                )}
                <div>
                    <span className="block font-semibold text-skillup-navy">{story.name}</span>
                    {story.role && <span className="block text-xs text-gray-500">{story.role}</span>}
                </div>
            </figcaption>
        </figure>
    );
}
