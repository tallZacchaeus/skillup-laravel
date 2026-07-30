import { Wrench } from 'lucide-react';

/** Consistent tool/technology badges from a string[]. */
export default function ToolBadges({ tools = [] }) {
    if (!tools || tools.length === 0) return null;

    return (
        <ul className="flex flex-wrap gap-2.5">
            {tools.map((tool) => (
                <li
                    key={tool}
                    className="inline-flex items-center gap-2 rounded-full bg-white px-4 py-2 text-sm font-semibold text-slate-700 ring-1 ring-slate-200"
                >
                    <Wrench className="h-4 w-4 text-skillup-blue" aria-hidden="true" />
                    {tool}
                </li>
            ))}
        </ul>
    );
}
