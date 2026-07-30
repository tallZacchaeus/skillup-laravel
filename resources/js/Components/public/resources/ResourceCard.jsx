import { useState } from 'react';
import { Download, FileText } from 'lucide-react';
import Img from '@/Components/Img';
import DownloadDialog from '@/Components/public/resources/DownloadDialog';
import { cn } from '@/lib/utils';

const csrfToken = () => document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

/**
 * Resource / downloadable card. Equal-height flex column. Only shows metadata
 * that exists (file size / last-updated are hidden when unavailable). Gated
 * resources open an email dialog; ungated ones download directly via a native
 * form POST so the browser handles the streamed file.
 */
export default function ResourceCard({ resource, priority = false }) {
    const [dialogOpen, setDialogOpen] = useState(false);

    const meta = [resource.fileType, resource.fileSize, resource.updatedLabel ? `Updated ${resource.updatedLabel}` : null].filter(Boolean);

    return (
        <article className="group flex h-full flex-col overflow-hidden rounded-2xl bg-white shadow-card ring-1 ring-slate-100 transition-all duration-300 hover:-translate-y-1 hover:shadow-card-hover">
            <div className="relative aspect-[16/10] overflow-hidden bg-slate-100">
                <Img
                    src={resource.image}
                    alt=""
                    className="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105"
                    eager={priority}
                />
                {resource.fileType && (
                    <span className="absolute left-4 top-4 inline-flex items-center gap-1.5 rounded-full bg-white/90 px-3 py-1 text-xs font-semibold text-skillup-navy shadow-sm backdrop-blur-sm">
                        <FileText className="h-3.5 w-3.5" aria-hidden="true" />
                        {resource.fileType}
                    </span>
                )}
            </div>

            <div className="flex flex-1 flex-col p-6">
                {resource.category && (
                    <p className="text-xs font-semibold uppercase tracking-wide text-skillup-blue">{resource.category.name}</p>
                )}
                <h3 className="mt-1.5 line-clamp-2 text-lg font-bold leading-snug text-skillup-navy">{resource.title}</h3>
                {resource.description && <p className="mt-2 line-clamp-2 text-sm leading-6 text-slate-600">{resource.description}</p>}

                {meta.length > 0 && (
                    <p className="mt-3 flex flex-wrap items-center gap-x-1.5 text-xs font-medium text-slate-500">
                        {meta.map((item, i) => (
                            <span key={item} className="inline-flex items-center gap-1.5">
                                {i > 0 && <span aria-hidden="true" className="text-slate-300">·</span>}
                                {item}
                            </span>
                        ))}
                    </p>
                )}

                <div className="mt-5 flex-1" />

                {resource.isGated ? (
                    <button
                        type="button"
                        onClick={() => setDialogOpen(true)}
                        className={cn(
                            'inline-flex h-11 w-full items-center justify-center gap-2 rounded-md border-2 border-skillup-blue text-sm font-semibold text-skillup-navy transition-colors hover:bg-skillup-blue hover:text-white focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-skillup-blue/40',
                        )}
                        aria-haspopup="dialog"
                    >
                        <Download className="h-4 w-4" aria-hidden="true" />
                        Download{resource.fileType ? ` (${resource.fileType})` : ''}
                    </button>
                ) : (
                    <form method="POST" action={resource.downloadUrl}>
                        <input type="hidden" name="_token" value={csrfToken()} />
                        <button
                            type="submit"
                            className="inline-flex h-11 w-full items-center justify-center gap-2 rounded-md border-2 border-skillup-blue text-sm font-semibold text-skillup-navy transition-colors hover:bg-skillup-blue hover:text-white focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-skillup-blue/40"
                            aria-label={`Download ${resource.title}`}
                        >
                            <Download className="h-4 w-4" aria-hidden="true" />
                            Download{resource.fileType ? ` (${resource.fileType})` : ''}
                        </button>
                    </form>
                )}
            </div>

            {dialogOpen && (
                <DownloadDialog resource={resource} csrfToken={csrfToken()} onClose={() => setDialogOpen(false)} />
            )}
        </article>
    );
}
