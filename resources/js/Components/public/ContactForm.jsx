import { useState } from 'react';
import { Link } from '@inertiajs/react';
import { CheckCircle2, Loader2, Send } from 'lucide-react';
import { Input } from '@/Components/ui/input';
import { Textarea } from '@/Components/ui/textarea';
import { cn } from '@/lib/utils';

const MESSAGE_MAX = 2000;
const EMAIL_RE = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
const PHONE_RE = /^[+()\-\s\d]{7,20}$/;

const ENQUIRY_TYPES = ['General question', 'Course enquiry', 'Corporate training', 'Partnership', 'Technical support'];

const EMPTY = { name: '', email: '', phone: '', enquiry_type: 'General question', subject: '', message: '', website: '' };

/**
 * Contact form. Client validation mirrors the backend (name/email/message
 * required); errors render below each field with aria-describedby. Submits to
 * leads.contact via fetch and only treats a JSON {success:true} as sent — a
 * followed redirect (server-side validation) is never mistaken for success.
 */
export default function ContactForm({ defaultEnquiryType }) {
    const [form, setForm] = useState({ ...EMPTY, enquiry_type: defaultEnquiryType || EMPTY.enquiry_type });
    const [errors, setErrors] = useState({});
    const [status, setStatus] = useState('idle');

    const submitting = status === 'submitting';

    const update = (event) => {
        const { name, value } = event.target;
        setForm((current) => ({ ...current, [name]: value }));
        setErrors((current) => (current[name] ? { ...current, [name]: undefined } : current));
    };

    const validate = () => {
        const next = {};
        if (!form.name.trim()) next.name = 'Please enter your name.';
        const email = form.email.trim();
        if (!email) next.email = 'Please enter your email.';
        else if (!EMAIL_RE.test(email)) next.email = 'Enter a valid email address.';
        if (!form.message.trim()) next.message = 'Please write a short message.';
        else if (form.message.length > MESSAGE_MAX) next.message = `Please keep this under ${MESSAGE_MAX} characters.`;
        if (form.phone.trim() && !PHONE_RE.test(form.phone.trim())) next.phone = 'Enter a valid phone number.';
        return next;
    };

    const submit = async (event) => {
        event.preventDefault();
        if (submitting) return;

        const found = validate();
        if (Object.keys(found).length > 0) {
            setErrors(found);
            window.requestAnimationFrame(() => document.getElementById(Object.keys(found)[0])?.focus());
            return;
        }

        setErrors({});
        setStatus('submitting');

        try {
            const response = await fetch(route('leads.contact'), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                },
                body: JSON.stringify({
                    name: form.name.trim(),
                    email: form.email.trim(),
                    phone: form.phone.trim(),
                    enquiry_type: form.enquiry_type,
                    subject: form.subject.trim(),
                    message: form.message.trim(),
                    website: form.website,
                }),
            });

            const data = await response.json().catch(() => null);
            if (response.ok && data?.success) {
                setStatus('success');
                setForm({ ...EMPTY, enquiry_type: defaultEnquiryType || EMPTY.enquiry_type });
            } else {
                setStatus('error');
            }
        } catch (err) {
            setStatus('error');
        }
    };

    if (status === 'success') {
        return (
            <div className="rounded-2xl border border-emerald-200 bg-emerald-50 p-8 text-center motion-safe:animate-fade-in-up" role="status" aria-live="polite">
                <CheckCircle2 className="mx-auto h-12 w-12 text-emerald-500" aria-hidden="true" />
                <h3 className="mt-4 text-xl font-bold text-skillup-navy">Thank you! We’ve received your message.</h3>
                <p className="mx-auto mt-2 max-w-md text-sm leading-6 text-slate-600">We’ll respond as soon as possible.</p>
                <button type="button" onClick={() => setStatus('idle')} className="mt-6 inline-flex h-11 items-center justify-center rounded-md border border-slate-300 px-5 text-sm font-semibold text-slate-700 transition-colors hover:bg-white focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-skillup-blue/40">
                    Send another message
                </button>
            </div>
        );
    }

    return (
        <form onSubmit={submit} noValidate className="space-y-5">
            {status === 'error' && (
                <div className="flex items-start gap-3 rounded-lg border border-red-200 bg-red-50 p-4" role="alert" aria-live="assertive">
                    <div className="flex-1">
                        <p className="text-sm font-medium text-red-800">
                            We couldn’t send your message. Please try again, or contact us by email at{' '}
                            <a href="mailto:skilluplimited@gmail.com" className="font-semibold underline">skilluplimited@gmail.com</a>.
                        </p>
                        <button type="button" onClick={submit} className="mt-2 text-sm font-semibold text-red-700 underline underline-offset-2 hover:text-red-900">Retry</button>
                    </div>
                </div>
            )}

            <div className="grid gap-5 sm:grid-cols-2">
                <Field id="name" label="Name" required value={form.name} onChange={update} error={errors.name} autoComplete="name" placeholder="Full name" />
                <Field id="email" label="Email" required type="email" value={form.email} onChange={update} error={errors.email} autoComplete="email" placeholder="you@example.com" />
            </div>
            <div className="grid gap-5 sm:grid-cols-2">
                <SelectField id="enquiry_type" label="Enquiry type" value={form.enquiry_type} onChange={update}>
                    {ENQUIRY_TYPES.map((type) => <option key={type} value={type}>{type}</option>)}
                </SelectField>
                <Field id="phone" label="Phone" type="tel" inputMode="tel" value={form.phone} onChange={update} error={errors.phone} autoComplete="tel" placeholder="+234 800 000 0000" />
            </div>
            <Field id="subject" label="Subject" value={form.subject} onChange={update} placeholder="What’s this about?" />
            <TextAreaField id="message" label="Message" required value={form.message} onChange={update} error={errors.message} maxLength={MESSAGE_MAX} placeholder="How can we help?" />

            {/* Honeypot — hidden from people and assistive tech. */}
            <div aria-hidden="true" className="absolute left-[-9999px] top-auto h-0 w-0 overflow-hidden">
                <label htmlFor="website">Leave this field empty</label>
                <input id="website" name="website" type="text" tabIndex={-1} autoComplete="off" value={form.website} onChange={update} />
            </div>

            <div className="flex flex-col gap-4 pt-1 sm:flex-row sm:items-center sm:justify-between">
                <button type="submit" disabled={submitting} className="inline-flex h-12 w-full items-center justify-center gap-2 rounded-md bg-skillup-blue px-6 text-base font-semibold text-white shadow-sm transition-colors hover:bg-blue-700 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-skillup-blue focus-visible:ring-offset-2 disabled:opacity-70 sm:w-auto">
                    {submitting ? (<><Loader2 className="h-5 w-5 motion-safe:animate-spin" aria-hidden="true" />Sending…</>) : (<><Send className="h-5 w-5" aria-hidden="true" />Send message</>)}
                </button>
                <p className="max-w-xs text-xs leading-5 text-slate-500">
                    By submitting, you agree that SkillUp may contact you about your enquiry. See our{' '}
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
            {required ? <span className="text-red-500" aria-hidden="true"> *</span> : <span className="ml-1 text-xs font-normal text-slate-400">(optional)</span>}
        </label>
    );
}

function Field({ id, label, required = false, error, className, ...props }) {
    return (
        <div>
            <FieldLabel id={id} label={label} required={required} />
            <Input id={id} name={id} required={required} aria-required={required || undefined} aria-invalid={error ? 'true' : undefined} aria-describedby={error ? `${id}-error` : undefined} className={cn(error && 'border-red-400 focus:border-red-500 focus:ring-red-500/20', className)} {...props} />
            {error && <p id={`${id}-error`} className="mt-1 text-xs font-medium text-red-600">{error}</p>}
        </div>
    );
}

function SelectField({ id, label, value, onChange, children }) {
    return (
        <div>
            <FieldLabel id={id} label={label} />
            <select id={id} name={id} value={value} onChange={onChange} className="flex h-11 w-full rounded-md border border-slate-300 bg-white px-3 text-sm text-slate-900 shadow-sm outline-none transition focus:border-skillup-blue focus:ring-2 focus:ring-skillup-blue/20">
                {children}
            </select>
        </div>
    );
}

function TextAreaField({ id, label, required = false, error, value, onChange, maxLength, placeholder }) {
    return (
        <div>
            <div className="flex items-baseline justify-between">
                <FieldLabel id={id} label={label} required={required} />
                {maxLength && <span className={cn('text-xs', value.length > maxLength * 0.9 ? 'text-amber-600' : 'text-slate-400')}>{value.length}/{maxLength}</span>}
            </div>
            <Textarea id={id} name={id} value={value} onChange={onChange} required={required} maxLength={maxLength} placeholder={placeholder} aria-required={required || undefined} aria-invalid={error ? 'true' : undefined} aria-describedby={error ? `${id}-error` : undefined} className={cn('min-h-36', error && 'border-red-400 focus:border-red-500 focus:ring-red-500/20')} />
            {error && <p id={`${id}-error`} className="mt-1 text-xs font-medium text-red-600">{error}</p>}
        </div>
    );
}
