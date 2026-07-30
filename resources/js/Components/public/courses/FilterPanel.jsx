import { useId, useState } from 'react';
import { Check, ChevronDown } from 'lucide-react';
import { cn } from '@/lib/utils';

/**
 * Accessible catalogue filter panel — shared by the desktop sidebar and the
 * mobile drawer. Each facet is single-select (mirrors the URL/query model), so
 * options are toggle controls with aria-pressed and a non-colour checkbox
 * indicator. Selection lives in the parent (URL), so collapsing a group never
 * loses the user's choices.
 */
export default function FilterPanel({ options, filters, onToggle }) {
    return (
        <div className="divide-y divide-slate-100">
            <FilterGroup title="Program" options={options.programs} selected={filters.program} onSelect={(v) => onToggle('program', v)} />
            <FilterGroup title="Category" options={options.categories} selected={filters.category} onSelect={(v) => onToggle('category', v)} />
            <FilterGroup title="Level" options={options.levels} selected={filters.level} onSelect={(v) => onToggle('level', v)} />
            <FilterGroup title="Delivery" options={options.deliveryModes} selected={filters.delivery} onSelect={(v) => onToggle('delivery', v)} />
            <FilterGroup title="Skills" noun="skills" options={options.skills} selected={filters.tags} onSelect={(v) => onToggle('tags', v)} />
            <FilterGroup title="Price" options={options.priceBuckets} selected={filters.price} onSelect={(v) => onToggle('price', v)} />
        </div>
    );
}

function FilterGroup({ title, options = [], selected, onSelect, noun }) {
    const [open, setOpen] = useState(true);
    const [expanded, setExpanded] = useState(false);
    const panelId = useId();
    if (options.length === 0) return null;

    const LIMIT = 6;
    const visible = expanded ? options : options.slice(0, LIMIT);
    const moreLabel = noun ? `Show all ${options.length} ${noun}` : `Show all ${options.length}`;
    const lessLabel = noun ? `Show fewer ${noun}` : 'Show fewer';

    return (
        <div className="py-4">
            <h3 className="m-0">
                <button
                    type="button"
                    onClick={() => setOpen((v) => !v)}
                    aria-expanded={open}
                    aria-controls={panelId}
                    className="flex min-h-11 w-full items-center justify-between py-1 text-left focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-skillup-blue/40"
                >
                    <span className="text-xs font-bold uppercase tracking-wide text-slate-500">{title}</span>
                    <ChevronDown
                        className={cn('h-4 w-4 shrink-0 text-slate-400 transition-transform duration-200', open ? 'rotate-180' : '')}
                        aria-hidden="true"
                    />
                </button>
            </h3>

            <div
                id={panelId}
                className={cn('grid transition-all duration-200 ease-out', open ? 'grid-rows-[1fr] opacity-100' : 'grid-rows-[0fr] opacity-0')}
            >
                <div className="overflow-hidden">
                    <ul className="mt-1 space-y-0.5">
                        {visible.map((option) => {
                            const active = selected === option.value;
                            const disabled = option.count === 0 && !active;
                            return (
                                <li key={option.value}>
                                    <button
                                        type="button"
                                        onClick={() => onSelect(option.value)}
                                        aria-pressed={active}
                                        disabled={disabled}
                                        className={cn(
                                            'flex min-h-11 w-full items-center gap-2.5 rounded-lg px-2 py-1.5 text-left text-sm transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-skillup-blue/40',
                                            disabled && 'cursor-not-allowed opacity-40',
                                            active ? 'bg-skillup-blue/10' : 'hover:bg-slate-100',
                                        )}
                                    >
                                        <span
                                            aria-hidden="true"
                                            className={cn(
                                                'flex h-4 w-4 shrink-0 items-center justify-center rounded border transition-colors',
                                                active ? 'border-skillup-blue bg-skillup-blue text-white' : 'border-slate-300 bg-white',
                                            )}
                                        >
                                            {active && <Check className="h-3 w-3" />}
                                        </span>
                                        <span className={cn('flex-1 truncate', active ? 'font-semibold text-skillup-navy' : 'text-slate-700')}>
                                            {option.label}
                                        </span>
                                        <span className="shrink-0 text-xs font-medium tabular-nums text-slate-400">{option.count}</span>
                                    </button>
                                </li>
                            );
                        })}
                        {options.length > LIMIT && (
                            <li>
                                <button
                                    type="button"
                                    onClick={() => setExpanded((v) => !v)}
                                    className="mt-1 rounded px-2 py-1 text-xs font-semibold text-skillup-blue hover:underline focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-skillup-blue/40"
                                >
                                    {expanded ? lessLabel : moreLabel}
                                </button>
                            </li>
                        )}
                    </ul>
                </div>
            </div>
        </div>
    );
}
