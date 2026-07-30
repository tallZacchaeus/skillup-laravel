/**
 * Curriculum preview from a product's syllabus ([{ week, title, description? }]).
 * Renders each syllabus entry as a numbered module. Descriptions show only when
 * present (expandable content can be added later without changing callers).
 */
export default function CurriculumList({ modules = [] }) {
    if (!modules || modules.length === 0) return null;

    return (
        <ol className="space-y-4">
            {modules.map((module, index) => (
                <li key={`${module.title}-${index}`} className="flex gap-4 rounded-2xl border border-slate-200 bg-white p-5 shadow-card">
                    <span className="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-full bg-skillup-blue/10 text-sm font-bold text-skillup-blue">
                        {String(index + 1).padStart(2, '0')}
                    </span>
                    <div className="min-w-0">
                        <p className="text-xs font-semibold uppercase tracking-wide text-skillup-blue">Module {index + 1}</p>
                        <h3 className="mt-0.5 font-bold text-skillup-navy">{module.title}</h3>
                        {module.description && <p className="mt-1 text-sm leading-6 text-slate-600">{module.description}</p>}
                    </div>
                </li>
            ))}
        </ol>
    );
}
