/**
 * Reusable social sign-in buttons for FUTURE providers (Google / Microsoft /
 * GitHub). Intentionally NOT rendered anywhere yet — SkillUp has no social-auth
 * backend. When OAuth routes exist, pass `providers` (e.g. from a shared Inertia
 * prop) and drop <SocialAuthButtons providers={...} /> into the auth form:
 *
 *   providers = [{ key: 'google', label: 'Continue with Google', url: '/auth/google' }]
 *
 * Renders nothing when the list is empty, so it is safe to always include.
 */
export default function SocialAuthButtons({ providers = [] }) {
    if (!providers || providers.length === 0) {
        return null;
    }

    return (
        <div className="space-y-3">
            <div className="relative py-2 text-center">
                <span className="relative z-10 bg-white px-3 text-xs font-medium uppercase tracking-wide text-slate-400">
                    or continue with
                </span>
                <span className="absolute left-0 top-1/2 h-px w-full -translate-y-1/2 bg-slate-200" aria-hidden="true" />
            </div>
            {providers.map((provider) => (
                <a
                    key={provider.key}
                    href={provider.url}
                    className="inline-flex h-11 w-full items-center justify-center gap-2 rounded-md border border-slate-300 bg-white text-sm font-semibold text-slate-700 transition-colors hover:bg-slate-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-skillup-blue/40"
                >
                    {provider.label}
                </a>
            ))}
        </div>
    );
}
