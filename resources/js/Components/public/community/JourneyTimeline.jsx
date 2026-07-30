/**
 * Reusable learner-journey timeline. Renders an ordered list of steps as a
 * connected roadmap — vertical on mobile, horizontal on large screens — with a
 * staggered reveal. Each step: { icon, title, text }.
 */
export default function JourneyTimeline({ steps = [] }) {
    if (!steps.length) return null;

    return (
        <ol className="relative grid gap-6 lg:grid-cols-7 lg:gap-4" data-reveal-group aria-label="The community journey">
            {steps.map((step, index) => {
                const Icon = step.icon;
                return (
                    <li key={step.title} className="relative flex items-start gap-4 lg:flex-col lg:items-center lg:text-center">
                        {/* Connector line to the next step (decorative). */}
                        {index < steps.length - 1 && (
                            <>
                                <span className="absolute left-6 top-12 h-[calc(100%-1rem)] w-0.5 bg-blue-200 lg:hidden" aria-hidden="true" />
                                <span className="absolute left-1/2 top-6 hidden h-0.5 w-full bg-blue-200 lg:block" aria-hidden="true" />
                            </>
                        )}
                        <span className="relative z-10 flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full bg-skillup-blue text-white ring-4 ring-white">
                            <Icon className="h-5 w-5" aria-hidden="true" />
                        </span>
                        <div className="lg:mt-3">
                            <span className="block text-xs font-bold uppercase tracking-wide text-skillup-blue">Step {index + 1}</span>
                            <h3 className="mt-0.5 font-semibold text-skillup-navy">{step.title}</h3>
                            {step.text && <p className="mt-1 text-sm leading-6 text-gray-600 lg:text-xs">{step.text}</p>}
                        </div>
                    </li>
                );
            })}
        </ol>
    );
}
