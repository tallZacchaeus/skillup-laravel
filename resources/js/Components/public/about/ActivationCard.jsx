import { MapPin } from 'lucide-react';
import Img from '@/Components/Img';

/** Past-activation card: image, location, summary, and verified learner/course counts. */
export default function ActivationCard({ activation }) {
    return (
        <article className="group flex h-full flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-card transition-all duration-300 hover:-translate-y-1 hover:shadow-card-hover">
            {activation.image && (
                <div className="overflow-hidden">
                    <Img src={activation.image} alt="" className="aspect-[16/10] w-full object-cover transition-transform duration-500 group-hover:scale-105" loading="lazy" />
                </div>
            )}
            <div className="flex flex-1 flex-col p-6">
                {activation.location && (
                    <p className="inline-flex items-center gap-1.5 text-xs font-semibold uppercase tracking-wide text-skillup-blue">
                        <MapPin className="h-3.5 w-3.5" aria-hidden="true" />
                        {activation.location}
                    </p>
                )}
                <h3 className="mt-1.5 text-xl font-bold text-skillup-navy">{activation.title}</h3>
                {activation.text && <p className="mt-2 flex-1 text-sm leading-6 text-slate-600">{activation.text}</p>}
                <div className="mt-5 grid grid-cols-2 gap-3">
                    <div className="rounded-xl bg-skillup-soft p-3 text-center">
                        <div className="text-xl font-bold text-skillup-navy">{activation.learners}</div>
                        <div className="mt-0.5 text-xs font-semibold uppercase tracking-wide text-slate-500">Learners</div>
                    </div>
                    <div className="rounded-xl bg-skillup-soft p-3 text-center">
                        <div className="text-xl font-bold text-skillup-navy">{activation.courses}</div>
                        <div className="mt-0.5 text-xs font-semibold uppercase tracking-wide text-slate-500">Courses</div>
                    </div>
                </div>
            </div>
        </article>
    );
}
