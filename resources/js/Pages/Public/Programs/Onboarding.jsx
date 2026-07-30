import { Head, useForm } from '@inertiajs/react';
import { Plus, Trash2 } from 'lucide-react';
import PublicLayout from '@/Components/public/PublicLayout';

export default function Onboarding({ token, registration, fields = [] }) {
    const { data, setData, post, processing, errors } = useForm({
        emergency_contact_name: registration.emergencyContactName ?? '',
        emergency_contact_phone: registration.emergencyContactPhone ?? '',
        medical_notes: '',
        authorized_pickups: [{ name: '', phone: '' }],
        participant_gender: registration.participantGender ?? '',
        first_aid_consent: registration.firstAidConsent ?? false,
        guardian_consent: false,
        media_consent: registration.mediaConsent ?? false,
        custom_fields: registration.customFields ?? {},
    });

    const submit = (e) => {
        e.preventDefault();
        post(`/program-onboarding/${token}`);
    };

    const setPickup = (index, key, value) => {
        const pickups = [...data.authorized_pickups];
        pickups[index] = { ...pickups[index], [key]: value };
        setData('authorized_pickups', pickups);
    };

    return (
        <PublicLayout>
            <Head title={`Onboarding — ${registration.participantName}`} />

            <div className="bg-skillup-soft px-4 pb-16 pt-28">
                <div className="mx-auto max-w-2xl rounded-2xl bg-white p-8 shadow-lg">
                    <p className="text-sm font-semibold uppercase tracking-wide text-skillup-blue">{registration.editionTitle}</p>
                    <h1 className="mt-2 text-2xl font-bold text-skillup-navy">
                        {registration.participantName}'s onboarding
                    </h1>
                    <p className="mt-2 text-sm leading-6 text-gray-600">
                        These details keep {registration.participantName} safe during the programme. The Certificate of
                        Participation can only be issued once this form is complete — you can save and return anytime
                        with your email link.
                    </p>

                    <form onSubmit={submit} className="mt-8 space-y-6">
                        <fieldset className="space-y-4">
                            <legend className="text-lg font-bold text-gray-900">Emergency contact</legend>
                            <div className="grid gap-4 sm:grid-cols-2">
                                <Field label="Full name" error={errors.emergency_contact_name} required>
                                    <input
                                        type="text"
                                        required
                                        value={data.emergency_contact_name}
                                        onChange={(e) => setData('emergency_contact_name', e.target.value)}
                                        className="h-12 w-full rounded-md border-slate-300 focus:border-skillup-blue focus:ring-skillup-blue"
                                    />
                                </Field>
                                <Field label="Phone" error={errors.emergency_contact_phone} required>
                                    <input
                                        type="tel"
                                        required
                                        inputMode="tel"
                                        value={data.emergency_contact_phone}
                                        onChange={(e) => setData('emergency_contact_phone', e.target.value)}
                                        className="h-12 w-full rounded-md border-slate-300 focus:border-skillup-blue focus:ring-skillup-blue"
                                    />
                                </Field>
                            </div>
                        </fieldset>

                        <fieldset className="space-y-4">
                            <legend className="text-lg font-bold text-gray-900">Health & safety</legend>
                            <Field
                                label="Allergies or medical conditions we should know about"
                                hint="Kept confidential — visible only to programme supervisors."
                                error={errors.medical_notes}
                            >
                                <textarea
                                    rows={3}
                                    value={data.medical_notes}
                                    onChange={(e) => setData('medical_notes', e.target.value)}
                                    className="w-full rounded-md border-slate-300 focus:border-skillup-blue focus:ring-skillup-blue"
                                />
                            </Field>

                            <div>
                                <span className="mb-1 block text-sm font-semibold text-slate-800">
                                    Who may pick {registration.participantName} up? <span className="text-red-500">*</span>
                                </span>
                                <div className="space-y-3">
                                    {data.authorized_pickups.map((pickup, index) => (
                                        <div key={index} className="flex gap-3">
                                            <input
                                                type="text"
                                                placeholder="Full name"
                                                required
                                                value={pickup.name}
                                                onChange={(e) => setPickup(index, 'name', e.target.value)}
                                                className="h-12 w-full rounded-md border-slate-300 focus:border-skillup-blue focus:ring-skillup-blue"
                                            />
                                            <input
                                                type="tel"
                                                placeholder="Phone"
                                                required
                                                inputMode="tel"
                                                value={pickup.phone}
                                                onChange={(e) => setPickup(index, 'phone', e.target.value)}
                                                className="h-12 w-full rounded-md border-slate-300 focus:border-skillup-blue focus:ring-skillup-blue"
                                            />
                                            {data.authorized_pickups.length > 1 && (
                                                <button
                                                    type="button"
                                                    aria-label="Remove this person"
                                                    onClick={() => setData('authorized_pickups', data.authorized_pickups.filter((_, i) => i !== index))}
                                                    className="flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-md text-slate-500 hover:bg-red-50 hover:text-red-600"
                                                >
                                                    <Trash2 className="h-5 w-5" aria-hidden="true" />
                                                </button>
                                            )}
                                        </div>
                                    ))}
                                </div>
                                {data.authorized_pickups.length < 5 && (
                                    <button
                                        type="button"
                                        onClick={() => setData('authorized_pickups', [...data.authorized_pickups, { name: '', phone: '' }])}
                                        className="mt-2 inline-flex items-center gap-1 text-sm font-semibold text-skillup-blue hover:text-blue-800"
                                    >
                                        <Plus className="h-4 w-4" aria-hidden="true" /> Add another person
                                    </button>
                                )}
                            </div>
                        </fieldset>

                        {fields.length > 0 && (
                            <fieldset className="space-y-4">
                                <legend className="text-lg font-bold text-gray-900">Programme details</legend>
                                {fields.map((field) => (
                                    <CustomField
                                        key={field.key}
                                        field={field}
                                        value={data.custom_fields[field.key] ?? ''}
                                        onChange={(value) => setData('custom_fields', { ...data.custom_fields, [field.key]: value })}
                                        error={errors[`custom_fields.${field.key}`]}
                                    />
                                ))}
                            </fieldset>
                        )}

                        <fieldset className="space-y-3">
                            <legend className="text-lg font-bold text-gray-900">Consent</legend>
                            <Checkbox
                                checked={data.guardian_consent}
                                onChange={(v) => setData('guardian_consent', v)}
                                error={errors.guardian_consent}
                                required
                                label={`I am the parent/guardian of ${registration.participantName} and consent to their participation in this programme.`}
                            />
                            <Checkbox
                                checked={data.first_aid_consent}
                                onChange={(v) => setData('first_aid_consent', v)}
                                error={errors.first_aid_consent}
                                required
                                label="I consent to basic first aid being administered if needed."
                            />
                            <Checkbox
                                checked={data.media_consent}
                                onChange={(v) => setData('media_consent', v)}
                                label="Photos/videos of my child during sessions and Showcase Day may be used in programme communications. (Optional)"
                            />
                        </fieldset>

                        <button
                            type="submit"
                            disabled={processing}
                            className="flex h-12 w-full items-center justify-center rounded-md bg-blue-900 text-base font-semibold text-white transition-colors hover:bg-blue-700 disabled:bg-slate-300"
                        >
                            {processing ? 'Saving…' : 'Save onboarding details'}
                        </button>
                    </form>
                </div>
            </div>
        </PublicLayout>
    );
}

function Field({ label, hint, error, required = false, children }) {
    return (
        <label className="block">
            <span className="mb-1 block text-sm font-semibold text-slate-800">
                {label} {required && <span className="text-red-500">*</span>}
            </span>
            {children}
            {hint && !error && <span className="mt-1 block text-xs text-gray-500">{hint}</span>}
            {error && <span className="mt-1 block text-sm text-red-600">{error}</span>}
        </label>
    );
}

function Checkbox({ checked, onChange, label, error, required = false }) {
    return (
        <label className="flex items-start gap-3">
            <input
                type="checkbox"
                required={required}
                checked={checked}
                onChange={(e) => onChange(e.target.checked)}
                className="mt-1 h-5 w-5 rounded border-slate-300 text-skillup-blue focus:ring-skillup-blue"
            />
            <span className="text-sm leading-6 text-gray-700">
                {label}
                {error && <span className="block text-red-600">{error}</span>}
            </span>
        </label>
    );
}

function CustomField({ field, value, onChange, error }) {
    const base = 'w-full rounded-md border-slate-300 focus:border-skillup-blue focus:ring-skillup-blue';

    return (
        <Field label={field.label} required={field.required} error={error}>
            {field.type === 'select' ? (
                <select value={value} required={field.required} onChange={(e) => onChange(e.target.value)} className={`h-12 ${base}`}>
                    <option value="">Select…</option>
                    {(field.options ?? []).map((option) => (
                        <option key={option} value={option}>
                            {option}
                        </option>
                    ))}
                </select>
            ) : field.type === 'textarea' ? (
                <textarea rows={3} value={value} required={field.required} onChange={(e) => onChange(e.target.value)} className={base} />
            ) : (
                <input type="text" value={value} required={field.required} onChange={(e) => onChange(e.target.value)} className={`h-12 ${base}`} />
            )}
        </Field>
    );
}
