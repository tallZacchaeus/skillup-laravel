import { useId, useRef, useState } from 'react';
import { ChevronDown } from 'lucide-react';
import { animateAccordion } from '@/lib/animations';
import { cn } from '@/lib/utils';

export default function FaqAccordion({ items = [] }) {
    const [openIndex, setOpenIndex] = useState(0);
    const baseId = useId();

    return (
        <div className="space-y-4">
            {items.map((item, index) => (
                <FaqItem
                    key={`${baseId}-${index}`}
                    id={`${baseId}-${index}`}
                    item={item}
                    open={openIndex === index}
                    highlighted={index === 0}
                    onToggle={() => setOpenIndex(openIndex === index ? null : index)}
                />
            ))}
        </div>
    );
}

function FaqItem({ id, item, open, highlighted, onToggle }) {
    const panelRef = useRef(null);
    const first = useRef(true);

    const handleToggle = () => {
        onToggle();
        requestAnimationFrame(() => {
            if (panelRef.current) {
                animateAccordion(panelRef.current, !open);
            }
        });
    };

    // The panel starts at its natural height for the initially-open item,
    // collapsed for the rest; GSAP drives every change after that.
    const initialStyle = first.current ? { height: open ? 'auto' : 0, opacity: open ? 1 : 0 } : undefined;

    return (
        <div
            className={cn(
                'rounded-xl border px-6 transition-colors duration-300',
                open ? 'border-skillup-blue/30 bg-skillup-soft shadow-card' : 'border-slate-200 bg-white hover:border-slate-300',
            )}
        >
            <h3>
                <button
                    type="button"
                    id={`${id}-trigger`}
                    aria-expanded={open}
                    aria-controls={`${id}-panel`}
                    onClick={handleToggle}
                    className="flex min-h-[60px] w-full items-center justify-between gap-4 py-4 text-left text-base font-semibold text-skillup-navy focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-skillup-blue/40 sm:text-lg"
                >
                    {item.question}
                    <span
                        className={cn(
                            'flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-full transition-all duration-300',
                            open ? 'bg-skillup-blue text-white' : 'bg-slate-100 text-slate-500',
                        )}
                    >
                        <ChevronDown className={cn('h-5 w-5 transition-transform duration-300', open && 'rotate-180')} aria-hidden="true" />
                    </span>
                </button>
            </h3>
            <div
                ref={panelRef}
                id={`${id}-panel`}
                role="region"
                aria-labelledby={`${id}-trigger`}
                className="overflow-hidden"
                style={initialStyle}
            >
                <p className="pb-5 pt-1 text-sm leading-6 text-gray-600">{item.answer}</p>
            </div>
        </div>
    );
}
