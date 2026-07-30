/** Loading placeholder mirroring ResourceCard dimensions (stable layout, no CLS). */
export default function ResourceCardSkeleton() {
    return (
        <div className="flex h-full flex-col overflow-hidden rounded-2xl bg-white ring-1 ring-slate-100" aria-hidden="true">
            <div className="aspect-[16/10] w-full bg-slate-200 motion-safe:animate-pulse" />
            <div className="flex flex-1 flex-col gap-3 p-6">
                <div className="h-3 w-24 rounded bg-slate-100 motion-safe:animate-pulse" />
                <div className="h-5 w-3/4 rounded bg-slate-200 motion-safe:animate-pulse" />
                <div className="h-4 w-full rounded bg-slate-100 motion-safe:animate-pulse" />
                <div className="mt-4 h-11 w-full rounded-md bg-slate-200 motion-safe:animate-pulse" />
            </div>
        </div>
    );
}
