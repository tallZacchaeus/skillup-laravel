import { Search, X } from 'lucide-react';
import { cn } from '@/lib/utils';

/**
 * Programme search + filter-chip bar. Fully controlled by the parent, which
 * owns URL sync and debouncing. Chips are real radio-style toggles (only real
 * facets are passed in) and are keyboard + screen-reader accessible.
 */
export default function ProgramFilters({ query, onQuery, chips, active, onChip, resultCount }) {
    return (
        <div className="space-y-5">
            <div className="relative">
                <Search className="pointer-events-none absolute left-4 top-1/2 h-5 w-5 -translate-y-1/2 text-slate-400" aria-hidden="true" />
                <label htmlFor="program-search" className="sr-only">Search programmes</label>
                <input
                    id="program-search"
                    type="search"
                    role="searchbox"
                    value={query}
                    onChange={(e) => onQuery(e.target.value)}
                    placeholder="Search programmes…"
                    autoComplete="off"
                    aria-describedby="program-search-count"
                    className="h-12 w-full rounded-full border border-slate-300 bg-white pl-12 pr-11 text-slate-900 shadow-sm outline-none transition focus:border-skillup-blue focus:ring-2 focus:ring-skillup-blue/20"
                />
                {query && (
                    <button
                        type="button"
                        onClick={() => onQuery('')}
                        aria-label="Clear search"
                        className="absolute right-3 top-1/2 flex h-8 w-8 -translate-y-1/2 items-center justify-center rounded-full text-slate-500 transition-colors hover:bg-slate-100 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-skillup-blue/40"
                    >
                        <X className="h-4 w-4" aria-hidden="true" />
                    </button>
                )}
            </div>

            {chips.length > 0 && (
                <div className="flex flex-wrap gap-2" role="group" aria-label="Filter programmes">
                    {chips.map((chip) => {
                        const isActive = active === chip.key;
                        return (
                            <button
                                key={chip.key}
                                type="button"
                                aria-pressed={isActive}
                                onClick={() => onChip(chip.key)}
                                className={cn(
                                    'inline-flex items-center rounded-full px-4 py-2 text-sm font-semibold transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-skillup-blue/40',
                                    isActive
                                        ? 'bg-skillup-blue text-white shadow-sm'
                                        : 'bg-white text-slate-700 ring-1 ring-slate-200 hover:bg-slate-50 hover:text-skillup-navy',
                                )}
                            >
                                {chip.label}
                            </button>
                        );
                    })}
                </div>
            )}

            <p id="program-search-count" className="text-sm text-slate-500" role="status" aria-live="polite">
                {resultCount === 1 ? '1 programme' : `${resultCount} programmes`}
            </p>
        </div>
    );
}
