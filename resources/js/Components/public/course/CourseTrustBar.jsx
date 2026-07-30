import { Award, MessagesSquare, Sparkles, Users, Wrench } from 'lucide-react';

// Truthful, platform-wide indicators (not per-course claims / no fabricated stats).
const indicators = [
    { icon: Award, label: 'Certificate included' },
    { icon: Sparkles, label: 'Beginner-friendly' },
    { icon: Wrench, label: 'Practical projects' },
    { icon: Users, label: 'Mentor support' },
    { icon: MessagesSquare, label: 'Community access' },
];

export default function CourseTrustBar() {
    return (
        <section className="border-b border-slate-200 bg-white">
            <ul className="mx-auto flex max-w-7xl flex-wrap items-center justify-center gap-x-8 gap-y-3 px-4 py-5 sm:px-6 lg:px-8">
                {indicators.map((item) => (
                    <li key={item.label} className="inline-flex items-center gap-2 text-sm font-medium text-slate-600">
                        <item.icon className="h-4 w-4 text-skillup-blue" aria-hidden="true" />
                        {item.label}
                    </li>
                ))}
            </ul>
        </section>
    );
}
