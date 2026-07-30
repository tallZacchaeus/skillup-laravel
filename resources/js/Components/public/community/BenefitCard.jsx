/**
 * Reusable benefit / feature card: an icon tile, title, and supporting text.
 * Used for the community benefits and "how it supports you" grids.
 */
export default function BenefitCard({ icon: Icon, title, text }) {
    return (
        <div className="group h-full rounded-2xl border border-slate-200 bg-white p-6 shadow-card transition-all duration-300 hover:-translate-y-1 hover:border-skillup-blue/40 hover:shadow-card-hover">
            <span className="mb-4 flex h-12 w-12 items-center justify-center rounded-xl bg-skillup-blue/10 text-skillup-blue transition-transform duration-300 group-hover:scale-110">
                <Icon className="h-6 w-6" aria-hidden="true" />
            </span>
            <h3 className="text-lg font-semibold text-skillup-navy">{title}</h3>
            <p className="mt-2 text-sm leading-6 text-gray-600">{text}</p>
        </div>
    );
}
