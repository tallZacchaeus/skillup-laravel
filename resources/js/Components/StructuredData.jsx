import { useEffect } from 'react';

/**
 * Injects one or more schema.org JSON-LD blocks into <head> for the current page.
 * Accepts a single object or an array of objects. Cleans up on unmount so blocks
 * don't leak across Inertia page visits.
 */
export default function StructuredData({ data }) {
    useEffect(() => {
        if (!data) return undefined;

        const blocks = Array.isArray(data) ? data : [data];
        const nodes = blocks.map((block) => {
            const el = document.createElement('script');
            el.type = 'application/ld+json';
            el.text = JSON.stringify(block);
            el.setAttribute('data-jsonld', '');
            document.head.appendChild(el);
            return el;
        });

        return () => nodes.forEach((el) => el.remove());
    }, [data]);

    return null;
}
