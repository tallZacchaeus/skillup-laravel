import { useEffect, useRef } from 'react';
import { gsap } from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';

gsap.registerPlugin(ScrollTrigger);

const ENTER_EASE = 'power3.out';
const REDUCED = '(prefers-reduced-motion: reduce)';
const FULL = '(prefers-reduced-motion: no-preference)';

/**
 * Scroll-driven entrance animations for a page section tree.
 *
 * Mark elements with:
 *   data-reveal          → fade-up when scrolled into view
 *   data-reveal-group    → children fade-up with a 60ms stagger
 *   data-count="828"     → number counts up from 0 when visible
 *
 * Reduced-motion users see everything static and instantly visible.
 */
export function useRevealScope() {
    const scope = useRef(null);

    useEffect(() => {
        const ctx = gsap.context(() => {
            const mm = gsap.matchMedia();

            mm.add(FULL, () => {
                gsap.utils.toArray('[data-reveal]').forEach((el) => {
                    gsap.fromTo(
                        el,
                        { autoAlpha: 0, y: 36 },
                        {
                            autoAlpha: 1,
                            y: 0,
                            duration: 0.7,
                            ease: ENTER_EASE,
                            scrollTrigger: { trigger: el, start: 'top 85%', once: true },
                        },
                    );
                });

                gsap.utils.toArray('[data-reveal-group]').forEach((group) => {
                    gsap.fromTo(
                        group.children,
                        { autoAlpha: 0, y: 32 },
                        {
                            autoAlpha: 1,
                            y: 0,
                            duration: 0.6,
                            ease: ENTER_EASE,
                            stagger: 0.06,
                            scrollTrigger: { trigger: group, start: 'top 85%', once: true },
                        },
                    );
                });
            });

            gsap.utils.toArray('[data-count]').forEach((el) => {
                const target = parseFloat(el.dataset.count);
                const suffix = el.dataset.countSuffix || '';

                if (Number.isNaN(target)) {
                    return;
                }

                // Default to the REAL value so a missed/failed ScrollTrigger can
                // never leave a credibility-damaging "0" on screen.
                el.textContent = `${target.toLocaleString()}${suffix}`;

                if (window.matchMedia(REDUCED).matches) {
                    return;
                }

                const state = { value: 0 };
                gsap.to(state, {
                    value: target,
                    duration: 1.6,
                    ease: 'power2.out',
                    scrollTrigger: { trigger: el, start: 'top 92%', once: true },
                    onStart: () => {
                        el.textContent = `0${suffix}`;
                    },
                    onUpdate: () => {
                        el.textContent = `${Math.round(state.value).toLocaleString()}${suffix}`;
                    },
                });
            });
        }, scope);

        return () => ctx.revert();
    }, []);

    return scope;
}

/**
 * Hero entrance timeline. Children marked data-hero animate in sequence
 * on first paint: badge → headline → copy → search → stats.
 */
export function useHeroIntro() {
    const scope = useRef(null);

    useEffect(() => {
        const ctx = gsap.context(() => {
            const mm = gsap.matchMedia();

            mm.add(FULL, () => {
                gsap.fromTo(
                    '[data-hero]',
                    { autoAlpha: 0, y: 44 },
                    { autoAlpha: 1, y: 0, duration: 0.8, ease: ENTER_EASE, stagger: 0.12, delay: 0.1 },
                );
            });
        }, scope);

        return () => ctx.revert();
    }, []);

    return scope;
}

/**
 * Cycles through `words` inside the referenced element with a vertical
 * flip transition. Falls back to the first word for reduced motion.
 */
export function useWordRotate(words, interval = 2.4) {
    const ref = useRef(null);

    useEffect(() => {
        const el = ref.current;

        if (!el || words.length === 0) {
            return undefined;
        }

        el.textContent = words[0];

        if (window.matchMedia(REDUCED).matches) {
            return undefined;
        }

        let index = 0;
        const swap = () => {
            index = (index + 1) % words.length;
            el.textContent = words[index];
        };

        const tl = gsap.timeline({ repeat: -1, repeatDelay: interval });
        tl.to(el, { yPercent: -60, autoAlpha: 0, duration: 0.28, ease: 'power2.in', delay: interval })
            .call(swap)
            .set(el, { yPercent: 60 })
            .to(el, { yPercent: 0, autoAlpha: 1, duration: 0.32, ease: 'power2.out' });

        return () => tl.kill();
    }, [words, interval]);

    return ref;
}

/**
 * Infinite horizontal marquee. The referenced track must contain the
 * content twice in a row; it slides by -50% and loops seamlessly.
 * Pauses on hover / focus. Static for reduced motion.
 */
export function useMarquee({ duration = 28, reverse = false } = {}) {
    const ref = useRef(null);

    useEffect(() => {
        const el = ref.current;

        if (!el || window.matchMedia(REDUCED).matches) {
            return undefined;
        }

        const tween = gsap.fromTo(
            el,
            { xPercent: reverse ? -50 : 0 },
            { xPercent: reverse ? 0 : -50, duration, ease: 'none', repeat: -1 },
        );

        const pause = () => tween.pause();
        const play = () => tween.play();
        el.addEventListener('mouseenter', pause);
        el.addEventListener('mouseleave', play);
        el.addEventListener('focusin', pause);
        el.addEventListener('focusout', play);

        return () => {
            el.removeEventListener('mouseenter', pause);
            el.removeEventListener('mouseleave', play);
            el.removeEventListener('focusin', pause);
            el.removeEventListener('focusout', play);
            tween.kill();
        };
    }, [duration, reverse]);

    return ref;
}

/**
 * Subtle vertical parallax for a hero background. The referenced element should
 * be slightly oversized (e.g. scale-110) so the translate never exposes an edge.
 * Disabled entirely for reduced-motion users.
 */
export function useParallax({ amount = 12 } = {}) {
    const ref = useRef(null);

    useEffect(() => {
        const el = ref.current;

        if (!el || window.matchMedia(REDUCED).matches) {
            return undefined;
        }

        const tween = gsap.fromTo(
            el,
            { yPercent: 0 },
            {
                yPercent: amount,
                ease: 'none',
                scrollTrigger: {
                    trigger: el.parentElement || el,
                    start: 'top top',
                    end: 'bottom top',
                    scrub: true,
                },
            },
        );

        return () => {
            tween.scrollTrigger?.kill();
            tween.kill();
        };
    }, [amount]);

    return ref;
}

/** Expand/collapse helper for accordions (height + fade, interruptible). */
export function animateAccordion(panel, open) {
    if (window.matchMedia(REDUCED).matches) {
        gsap.set(panel, { height: open ? 'auto' : 0, autoAlpha: open ? 1 : 0 });
        return;
    }

    gsap.killTweensOf(panel);

    if (open) {
        gsap.fromTo(
            panel,
            { height: 0, autoAlpha: 0 },
            { height: 'auto', autoAlpha: 1, duration: 0.3, ease: 'power2.out' },
        );
    } else {
        gsap.to(panel, { height: 0, autoAlpha: 0, duration: 0.22, ease: 'power2.in' });
    }
}
