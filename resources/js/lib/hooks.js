import { useEffect, useState } from 'react';

/**
 * Tracks whether the window has scrolled past `threshold` px. GSAP-free on
 * purpose: it lives in the layout (every page) and must not pull the animation
 * bundle into the shared chunk.
 */
export function useScrolled(threshold = 8) {
    const [scrolled, setScrolled] = useState(false);

    useEffect(() => {
        const onScroll = () => setScrolled(window.scrollY > threshold);
        onScroll();
        window.addEventListener('scroll', onScroll, { passive: true });

        return () => window.removeEventListener('scroll', onScroll);
    }, [threshold]);

    return scrolled;
}
