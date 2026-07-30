import { Bell, BellOff } from 'lucide-react';

/**
 * Dashboard notifications panel. Renders real in-app notifications; when there
 * are none it shows a calm "all caught up" state rather than fabricating items.
 */
export default function NotificationWidget({ notifications = [] }) {
    return (
        <section id="notifications" className="scroll-mt-24 rounded-2xl border border-slate-200 bg-white p-6 shadow-card" aria-labelledby="notifications-heading">
            <div className="flex items-center gap-2">
                <Bell className="h-5 w-5 text-skillup-blue" aria-hidden="true" />
                <h2 id="notifications-heading" className="text-lg font-bold text-skillup-navy">Notifications</h2>
            </div>

            {notifications.length === 0 ? (
                <div className="mt-6 flex flex-col items-center py-6 text-center">
                    <BellOff className="h-8 w-8 text-slate-300" aria-hidden="true" />
                    <p className="mt-3 text-sm font-medium text-slate-600">You’re all caught up</p>
                    <p className="mt-1 text-xs text-slate-400">New updates will show up here.</p>
                </div>
            ) : (
                <ul className="mt-4 divide-y divide-slate-100">
                    {notifications.map((n) => {
                        const Wrapper = n.url ? 'a' : 'div';
                        return (
                            <li key={n.id}>
                                <Wrapper
                                    {...(n.url ? { href: n.url } : {})}
                                    className="flex gap-3 py-3 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-skillup-blue/40"
                                >
                                    <span className={`mt-1.5 h-2 w-2 flex-shrink-0 rounded-full ${n.read ? 'bg-slate-300' : 'bg-skillup-blue'}`} aria-hidden="true" />
                                    <div className="min-w-0">
                                        <p className={`text-sm ${n.read ? 'font-medium text-slate-600' : 'font-semibold text-skillup-navy'}`}>{n.title}</p>
                                        {n.body && <p className="mt-0.5 line-clamp-2 text-xs leading-5 text-slate-500">{n.body}</p>}
                                        {n.createdAt && <p className="mt-1 text-[11px] text-slate-400">{n.createdAt}</p>}
                                    </div>
                                    {!n.read && <span className="sr-only">Unread</span>}
                                </Wrapper>
                            </li>
                        );
                    })}
                </ul>
            )}
        </section>
    );
}
