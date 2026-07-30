/**
 * Performance-friendly image.
 *
 * For local assets under /images/*.{jpg,jpeg,png,JPG}, it serves a WebP sibling
 * (generated at build time) through a <picture> element, keeping the original
 * raster as the fallback source for any browser that lacks WebP. Remote or
 * dynamic sources (e.g. catalogue thumbnails) render as a plain <img> and skip
 * the WebP swap.
 *
 * Defaults to lazy loading + async decoding so below-the-fold imagery never
 * blocks paint. Pass `eager` for the LCP hero so it is fetched with priority.
 */
export default function Img({
    src,
    alt = '',
    className = '',
    eager = false,
    width,
    height,
    ...props
}) {
    const isLocalRaster =
        typeof src === 'string' && /^\/images\/.+\.(jpe?g|png)$/i.test(src);
    const webpSrc = isLocalRaster ? src.replace(/\.(jpe?g|png)$/i, '.webp') : null;

    const img = (
        <img
            src={src}
            alt={alt}
            className={className}
            width={width}
            height={height}
            loading={eager ? 'eager' : 'lazy'}
            decoding={eager ? 'auto' : 'async'}
            fetchpriority={eager ? 'high' : undefined}
            {...props}
        />
    );

    if (!webpSrc) return img;

    // display:contents keeps <picture> from creating its own box, so the child
    // <img> keeps behaving exactly as it did before inside flex/grid parents.
    return (
        <picture className="contents">
            <source srcSet={webpSrc} type="image/webp" />
            {img}
        </picture>
    );
}
