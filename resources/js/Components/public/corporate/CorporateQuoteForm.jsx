import { useRef, useState } from 'react';
import { Link } from '@inertiajs/react';
import { CheckCircle2, Loader2, Send } from 'lucide-react';
import { Input } from '@/Components/ui/input';
import { Textarea } from '@/Components/ui/textarea';
import { cn } from '@/lib/utils';

const MESSAGE_MAX = 2000;
const EMAIL_RE = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
const PHONE_RE = /^[+()\-\s\d]{7,20}$/;

const EMPTY = {
    name: '',
    email: '',
    company_name: '',
    employee_count: '<50',
    job_title: '',
    phone: '',
    preferred_track: '',
    start_timeframe: '',
    country: '',
    message: '',
    website: '', // honeypot
};

/**
 * Corporate quote request form. Client-side validation mirrors the backend
 * rules (which stay the source of truth); errors render below each field and
 * are wired with aria-describedby. Submits to leads.corporate via fetch with
 * idle / submitting / success / error states announced through aria-live.
 */
export default function CorporateQuoteForm({ tracks = [] }) {
    const [form, setForm] = useState(EMPTY);
    const [errors, setErrors] = useState({});
    const [status, setStatus] = useState('idle'); // idle | submitting | success | error
    const formRef = useRef(null);

    const submitting = status === 'submitting';

    const update = (event) => {
        const { name, value } = event.target;
        setForm((current) => ({ ...current, [name]: value }));
        // Clear a field's error as the user corrects it.
        setErrors((current) => (current[name] ? { ...current, [name]: undefined } : current));
    };

    const validate = () => {
        const next = {};
        const name = form.name.trim();
        const email = form.email.trim();
        const company = form.company_name.trim();
        const message = form.message.trim();

        if (!name) next.name = 'Please enter a contact name.';
        if (!email) next.email = 'Please enter your work email.';
        else if (!EMAIL_RE.test(email)) next.email = 'Enter a valid email address.';
        if (!company) next.company_name = 'Please enter your company name.';
        if (!message) next.message = 'Tell us a little about your training needs.';
        else if (message.length > MESSAGE_MAX) next.message = `Please keep this under ${MESSAGE_MAX} characters.`;
        if (form.phone.trim() && !PHONE_RE.test(form.phone.trim())) next.phone = 'Enter a valid phone number.';

        return next;
    };

    const submit = async (event) => {
        event.preventDefault();
        if (submitting) return;

        const found = validate();
        if (Object.keys(found).length > 0) {
            setErrors(found);
            const firstId = Object.keys(found)[0];
            window.requestAnimationFrame(() => document.getElementById(firstId)?.focus());
            return;
        }

        setErrors({});
        setStatus('submitting');

        // Trim string values so whitespace-only never reaches the backend.
        const payload = Object.fromEntries(
            Object.entries(form).map(([key, value]) => [key, typeof value === 'string' ? value.trim() : value]),
        );

        try {
            const response = await fetch(route('leads.corporate'), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                },
                body: JSON.stringify(payload),
            });

            if (response.status === 422) {
                // Map backend validation back to fields without exposing raw messages.
                const data = await response.json().catch(() => ({}));
                const serverErrors = data?.errors || {};
                const mapped = {};
                Object.keys(serverErrors).forEach((key) => {
                    mapped[key] = 'Please check this field and try again.';
                });
                setErrors(mapped);
                setStatus('idle');
                const firstId = Object.keys(mapped)[0];
                if (firstId) window.requestAnimationFrame(() => document.getElementById(firstId)?.focus());
                return;
            }

            // Only treat it as success when the JSON success flag is present — a
            // followed redirect (e.g. server-side validation) returns 200 HTML,
            // which must NOT be mistaken for a successful submission.
            if (response.ok) {
                const data = await response.json().catch(() => null);
                if (data?.success) {
                    setStatus('success');
                    setForm(EMPTY);
                    return;
                }
            }

            setStatus('error');
        } catch (error) {
            // Network / unexpected — data is preserved so the user can retry.
            setStatus('error');
        }
    };

    if (status === 'success') {
        return (
            <div className="rounded-2xl border border-emerald-200 bg-emerald-50 p-8 text-center" role="status" aria-live="polite">
                <CheckCircle2 className="mx-auto h-12 w-12 text-emerald-500" aria-hidden="true" />
                <h3 className="mt-4 text-xl font-bold text-skillup-navy">Your inquiry has been received</h3>
                <p className="mx-auto mt-2 max-w-md text-sm leading-6 text-slate-600">
                    Our corporate training team will review the information and contact you using the details provided.
                </p>
                <button
                    type="button"
                    onClick={() => setStatus('idle')}
                    className="mt-6 inline-flex h-11 items-center justify-center rounded-md border border-slate-300 px-5 text-sm font-semibold text-slate-700 transition-colors hover:bg-white focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-skillup-blue/40"
                >
                    Submit another inquiry
                </button>
            </div>
        );
    }

    return (
        <form ref={formRef} onSubmit={submit} noValidate className="space-y-5">
            {status === 'error' && (
                <div className="flex items-start gap-3 rounded-lg border border-red-200 bg-red-50 p-4" role="alert" aria-live="assertive">
                    <div className="flex-1">
                        <p className="text-sm font-medium text-red-800">
                            We could not submit your inquiry. Please check your connection and try again.
                        </p>
                        <button
                            type="button"
                            onClick={submit}
                            className="mt-2 text-sm font-semibold text-red-700 underline underline-offset-2 hover:text-red-900"
                        >
                            Retry
                        </button>
                    </div>
                </div>
            )}

            <div className="grid gap-5 sm:grid-cols-2">
                <TextField id="name" label="Contact person" required value={form.name} onChange={update} error={errors.name} autoComplete="name" placeholder="Full name" />
                <TextField id="email" label="Work email" required type="email" value={form.email} onChange={update} error={errors.email} autoComplete="email" placeholder="you@company.com" />
            </div>

            <div className="grid gap-5 sm:grid-cols-2">
                <TextField id="company_name" label="Company name" required value={form.company_name} onChange={update} error={errors.company_name} autoComplete="organization" placeholder="Company Ltd" />
                <SelectField id="employee_count" label="Estimated learners" value={form.employee_count} onChange={update}>
                    <option value="<50">Fewer than 50 learners</option>
                    <option value="50-200">50 to 200 learners</option>
                    <option value="200+">More than 200 learners</option>
                </SelectField>
            </div>

            <div className="grid gap-5 sm:grid-cols-2">
                <TextField id="job_title" label="Job title" value={form.job_title} onChange={update} autoComplete="organization-title" placeholder="e.g. L&amp;D Manager" />
                <TextField id="phone" label="Phone" type="tel" inputMode="tel" value={form.phone} onChange={update} error={errors.phone} autoComplete="tel" placeholder="+234 800 000 0000" />
            </div>

            <div className="grid gap-5 sm:grid-cols-2">
                <SelectField id="preferred_track" label="Preferred training track" value={form.preferred_track} onChange={update}>
                    <option value="">No preference yet</option>
                    {tracks.map((track) => (
                        <option key={track.slug} value={track.title}>{track.title}</option>
                    ))}
                </SelectField>
                <SelectField id="start_timeframe" label="Ideal start" value={form.start_timeframe} onChange={update}>
                    <option value="">Flexible</option>
                    <option value="Within 1 month">Within 1 month</option>
                    <option value="1–3 months">1–3 months</option>
                    <option value="3+ months">3+ months</option>
                </SelectField>
            </div>

            <TextField id="country" label="Country" value={form.country} onChange={update} autoComplete="country-name" placeholder="e.g. Nigeria" />

            <TextAreaField
                id="message"
                label="Training requirements & objectives"
                required
                value={form.message}
                onChange={update}
                error={errors.message}
                maxLength={MESSAGE_MAX}
                placeholder="Tell us about the skills you want to build, team roles, and any target timelines."
            />

            {/* Honeypot — hidden from people and assistive tech; bots that fill it are dropped. */}
            <div aria-hidden="true" className="absolute left-[-9999px] top-auto h-0 w-0 overflow-hidden">
                <label htmlFor="website">Leave this field empty</label>
                <input id="website" name="website" type="text" tabIndex={-1} autoComplete="off" value={form.website} onChange={update} />
            </div>

            <div className="flex flex-col gap-4 pt-1 sm:flex-row sm:items-center sm:justify-between">
                <button
                    type="submit"
                    disabled={submitting}
                    className="inline-flex h-12 w-full items-center justify-center gap-2 rounded-md bg-skillup-blue px-6 text-base font-semibold text-white shadow-sm transition-colors hover:bg-blue-700 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-skillup-blue focus-visible:ring-offset-2 disabled:opacity-70 sm:w-auto"
                >
                    {submitting ? (
                        <>
                            <Loader2 className="h-5 w-5 motion-safe:animate-spin" aria-hidden="true" />
                            Submitting inquiry…
                        </>
                    ) : (
                        <>
                            <Send className="h-5 w-5" aria-hidden="true" />
                            Submit inquiry
                        </>
                    )}
                </button>
                <p className="max-w-sm text-xs leading-5 text-slate-500">
                    By submitting this form, you agree that SkillUp may contact you about your corporate training request. See our{' '}
                    <Link href="/privacy" className="font-medium text-skillup-blue hover:underline">Privacy Policy</Link>.
                </p>
            </div>
        </form>
    );
}

function FieldLabel({ id, label, required }) {
    return (
        <label htmlFor={id} className="mb-1.5 block text-sm font-semibold text-slate-700">
            {label}
            {required ? (
                <span className="text-red-500" aria-hidden="true"> *</span>
            ) : (
                <span className="ml-1 text-xs font-normal text-slate-400">(optional)</span>
            )}
        </label>
    );
}

function describedBy(id, error, hint) {
    return [hint ? `${id}-hint` : null, error ? `${id}-error` : null].filter(Boolean).join(' ') || undefined;
}

function FieldError({ id, error }) {
    if (!error) return null;
    return (
        <p id={`${id}-error`} className="mt-1 text-xs font-medium text-red-600">
            {error}
        </p>
    );
}

function TextField({ id, label, required = false, error, hint, className, ...props }) {
    return (
        <div>
            <FieldLabel id={id} label={label} required={required} />
            <Input
                id={id}
                name={id}
                required={required}
                aria-required={required || undefined}
                aria-invalid={error ? 'true' : undefined}
                aria-describedby={describedBy(id, error, hint)}
                className={cn(error && 'border-red-400 focus:border-red-500 focus:ring-red-500/20', className)}
                {...props}
            />
            {hint && <p id={`${id}-hint`} className="mt-1 text-xs text-slate-500">{hint}</p>}
            <FieldError id={id} error={error} />
        </div>
    );
}

function SelectField({ id, label, required = false, error, children, value, onChange }) {
    return (
        <div>
            <FieldLabel id={id} label={label} required={required} />
            <select
                id={id}
                name={id}
                value={value}
                onChange={onChange}
                required={required}
                aria-required={required || undefined}
                aria-invalid={error ? 'true' : undefined}
                aria-describedby={describedBy(id, error)}
                className="flex h-11 w-full rounded-md border border-slate-300 bg-white px-3 text-sm text-slate-900 shadow-sm outline-none transition focus:border-skillup-blue focus:ring-2 focus:ring-skillup-blue/20"
            >
                {children}
            </select>
            <FieldError id={id} error={error} />
        </div>
    );
}

function TextAreaField({ id, label, required = false, error, value, onChange, maxLength, placeholder }) {
    return (
        <div>
            <div className="flex items-baseline justify-between">
                <FieldLabel id={id} label={label} required={required} />
                {maxLength && (
                    <span className={cn('text-xs', value.length > maxLength * 0.9 ? 'text-amber-600' : 'text-slate-400')}>
                        {value.length}/{maxLength}
                    </span>
                )}
            </div>
            <Textarea
                id={id}
                name={id}
                value={value}
                onChange={onChange}
                required={required}
                maxLength={maxLength}
                placeholder={placeholder}
                aria-required={required || undefined}
                aria-invalid={error ? 'true' : undefined}
                aria-describedby={describedBy(id, error)}
                className={cn('min-h-36', error && 'border-red-400 focus:border-red-500 focus:ring-red-500/20')}
            />
            <FieldError id={id} error={error} />
        </div>
    );
}
