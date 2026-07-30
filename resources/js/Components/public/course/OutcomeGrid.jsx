import { CheckCircle2 } from 'lucide-react';

/** Learning-outcome cards from a string[] of outcomes. */
export default function OutcomeGrid({ outcomes = [] }) {
    if (!outcomes || outcomes.length === 0) return null;

    return (
        <div className="grid gap-4 sm:grid-cols-2">
            {outcomes.map((outcome) => (
                <div key={outcome} className="flex items-start gap-3 rounded-2xl border border-slate-200 bg-white p-5 shadow-card">
                    <span className="flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600">
                        <CheckCircle2 className="h-5 w-5" aria-hidden="true" />
                    </span>
                    <p className="pt-1 font-medium text-skillup-navy">{outcome}</p>
                </div>
            ))}
        </div>
    );
}
