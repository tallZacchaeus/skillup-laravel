/**
 * Loading placeholder that mirrors CourseCard's dimensions so the grid keeps a
 * stable layout (no CLS) while results update.
 */
export default function CourseCardSkeleton() {
    return (
        <div className="flex h-full flex-col overflow-hidden rounded-2xl bg-white ring-1 ring-slate-100" aria-hidden="true">
            <div className="aspect-[16/10] w-full bg-slate-200 motion-safe:animate-pulse" />
            <div className="flex flex-1 flex-col gap-3 p-5">
                <div className="h-3 w-24 rounded bg-slate-200 motion-safe:animate-pulse" />
                <div className="h-5 w-3/4 rounded bg-slate-200 motion-safe:animate-pulse" />
                <div className="h-4 w-full rounded bg-slate-100 motion-safe:animate-pulse" />
                <div className="h-4 w-5/6 rounded bg-slate-100 motion-safe:animate-pulse" />
                <div className="mt-2 flex gap-1.5">
                    <div className="h-6 w-16 rounded-full bg-slate-100 motion-safe:animate-pulse" />
                    <div className="h-6 w-16 rounded-full bg-slate-100 motion-safe:animate-pulse" />
                </div>
                <div className="mt-auto border-t border-slate-100 pt-4">
                    <div className="h-6 w-28 rounded bg-slate-200 motion-safe:animate-pulse" />
                    <div className="mt-4 h-11 w-full rounded-md bg-slate-200 motion-safe:animate-pulse" />
                    <div className="mt-2 h-11 w-full rounded-md bg-slate-100 motion-safe:animate-pulse" />
                </div>
            </div>
        </div>
    );
}
