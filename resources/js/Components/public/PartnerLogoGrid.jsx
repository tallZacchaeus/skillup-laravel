import Img from '@/Components/Img';

/** Grayscale-until-hover partner logo grid. Renders nothing when empty. */
export default function PartnerLogoGrid({ logos = [] }) {
    if (!logos || logos.length === 0) return null;

    return (
        <ul className="grid grid-cols-2 items-center gap-x-8 gap-y-10 sm:grid-cols-4">
            {logos.map((logo) => (
                <li key={logo.alt} className="flex items-center justify-center">
                    <Img src={logo.src} alt={logo.alt} className="logo-muted h-10 w-auto max-w-[150px] object-contain sm:h-12" loading="lazy" />
                </li>
            ))}
        </ul>
    );
}
