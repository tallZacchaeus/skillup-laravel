import { cn } from '@/lib/utils';

/** Reusable icon + title + text card (values, feature reasons). Equal height. */
export default function IconCard({ icon: Icon, title, text, className }) {
    return (
        <div className={cn('group flex h-full flex-col rounded-2xl border border-slate-200 bg-white p-6 shadow-card transition-all duration-300 hover:-translate-y-1 hover:border-skillup-blue/40 hover:shadow-card-hover', className)}>
            {Icon && (
                <div className="flex h-12 w-12 items-center justify-center rounded-xl bg-skillup-blue/10 text-skillup-blue transition-transform duration-300 group-hover:scale-110">
                    <Icon className="h-6 w-6" aria-hidden="true" />
                </div>
            )}
            <h3 className="mt-5 text-lg font-bold text-skillup-navy">{title}</h3>
            <p className="mt-2 text-sm leading-6 text-slate-600">{text}</p>
        </div>
    );
}
