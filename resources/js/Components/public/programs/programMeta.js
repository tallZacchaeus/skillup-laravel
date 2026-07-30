// Shared, real-data formatters for programme metadata. Every helper returns
// null when its source field is missing so callers can hide the row entirely.

const DELIVERY_LABELS = {
    in_person: 'In person',
    online: 'Online',
    hybrid: 'Hybrid',
    onsite: 'On site',
};

export function deliveryLabel(mode) {
    if (!mode) return null;
    return DELIVERY_LABELS[mode] ?? mode.replace(/_/g, ' ').replace(/\b\w/g, (c) => c.toUpperCase());
}

export function durationLabel(weeks) {
    if (!weeks) return null;
    return weeks === 1 ? '1 week' : `${weeks} weeks`;
}

export function dateLabel(startsOn, endsOn) {
    if (!startsOn) return null;
    const start = new Date(startsOn);
    const startStr = start.toLocaleDateString(undefined, { day: 'numeric', month: 'short', year: 'numeric' });
    if (!endsOn) return startStr;

    const end = new Date(endsOn);
    const sameYear = start.getFullYear() === end.getFullYear();
    const startShort = start.toLocaleDateString(undefined, { day: 'numeric', month: 'short', ...(sameYear ? {} : { year: 'numeric' }) });
    const endStr = end.toLocaleDateString(undefined, { day: 'numeric', month: 'short', year: 'numeric' });
    return `${startShort} – ${endStr}`;
}
