import { useRef, useState } from 'react';
import { Head, Link, useForm } from '@inertiajs/react';
import { Loader2, Lock } from 'lucide-react';
import AuthSplitLayout from '@/Layouts/AuthSplitLayout';
import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import PasswordInput from '@/Components/PasswordInput';
import PrimaryButton from '@/Components/PrimaryButton';
import TextInput from '@/Components/TextInput';

const EMAIL_RE = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
const PASSWORD_MIN = 8;

export default function Register() {
    const { data, setData, post, processing, errors, reset, transform } = useForm({
        name: '',
        email: '',
        password: '',
        password_confirmation: '',
    });

    const [clientErrors, setClientErrors] = useState({});
    const refs = {
        name: useRef(null),
        email: useRef(null),
        password: useRef(null),
        password_confirmation: useRef(null),
    };

    transform((payload) => ({ ...payload, name: payload.name.trim(), email: payload.email.trim() }));

    const validate = () => {
        const errs = {};
        if (!data.name.trim()) errs.name = 'Please enter your name.';
        const email = data.email.trim();
        if (!email) errs.email = 'Please enter your email.';
        else if (!EMAIL_RE.test(email)) errs.email = 'Enter a valid email address.';
        if (!data.password) errs.password = 'Please create a password.';
        else if (data.password.length < PASSWORD_MIN) errs.password = `Use at least ${PASSWORD_MIN} characters.`;
        if (data.password && data.password_confirmation !== data.password) {
            errs.password_confirmation = 'Passwords do not match.';
        }
        return errs;
    };

    // Show the first available error for a field: client-side first, then the server's.
    const errorFor = (field) => clientErrors[field] || errors[field];

    const submit = (e) => {
        e.preventDefault();
        if (processing) return;

        const errs = validate();
        if (Object.keys(errs).length > 0) {
            setClientErrors(errs);
            const firstInvalid = ['name', 'email', 'password', 'password_confirmation'].find((f) => errs[f]);
            refs[firstInvalid]?.current?.focus();
            return;
        }

        setClientErrors({});
        post(route('register'), {
            onFinish: () => reset('password', 'password_confirmation'),
        });
    };

    const describedBy = (field) => (errorFor(field) ? `${field}-error` : undefined);

    return (
        <AuthSplitLayout
            title="Start your learning journey"
            subtitle="Create a free account to enrol in courses, track your progress, and earn certificates."
        >
            <Head title="Create your account" />

            <div className="motion-safe:animate-fade-in-up">
                <h1 className="text-2xl font-bold tracking-tight text-skillup-navy sm:text-3xl">Create your account</h1>
                <p className="mt-2 text-sm text-slate-600">Start learning with practical, mentor-led tech courses.</p>

                <form onSubmit={submit} className="mt-6 space-y-5" noValidate>
                    <div>
                        <InputLabel required htmlFor="name" value="Name" />
                        <TextInput
                            id="name"
                            ref={refs.name}
                            name="name"
                            value={data.name}
                            className="mt-1 block h-11 w-full"
                            autoComplete="name"
                            isFocused
                            aria-required="true"
                            aria-invalid={errorFor('name') ? 'true' : undefined}
                            aria-describedby={describedBy('name')}
                            onChange={(e) => setData('name', e.target.value)}
                        />
                        <InputError id="name-error" message={errorFor('name')} className="mt-1.5" />
                    </div>

                    <div>
                        <InputLabel required htmlFor="email" value="Email" />
                        <TextInput
                            id="email"
                            ref={refs.email}
                            type="email"
                            name="email"
                            value={data.email}
                            className="mt-1 block h-11 w-full"
                            autoComplete="email"
                            aria-required="true"
                            aria-invalid={errorFor('email') ? 'true' : undefined}
                            aria-describedby={describedBy('email')}
                            onChange={(e) => setData('email', e.target.value)}
                        />
                        <InputError id="email-error" message={errorFor('email')} className="mt-1.5" />
                    </div>

                    <div>
                        <InputLabel required htmlFor="password" value="Password" />
                        <PasswordInput
                            id="password"
                            ref={refs.password}
                            name="password"
                            value={data.password}
                            className="mt-1 block h-11 w-full"
                            autoComplete="new-password"
                            aria-required="true"
                            aria-invalid={errorFor('password') ? 'true' : undefined}
                            aria-describedby={errorFor('password') ? 'password-error' : 'password-hint'}
                            onChange={(e) => setData('password', e.target.value)}
                        />
                        {errorFor('password') ? (
                            <InputError id="password-error" message={errorFor('password')} className="mt-1.5" />
                        ) : (
                            <p id="password-hint" className="mt-1.5 text-xs text-slate-500">Use at least {PASSWORD_MIN} characters.</p>
                        )}
                    </div>

                    <div>
                        <InputLabel required htmlFor="password_confirmation" value="Confirm password" />
                        <PasswordInput
                            id="password_confirmation"
                            ref={refs.password_confirmation}
                            name="password_confirmation"
                            value={data.password_confirmation}
                            className="mt-1 block h-11 w-full"
                            autoComplete="new-password"
                            aria-required="true"
                            aria-invalid={errorFor('password_confirmation') ? 'true' : undefined}
                            aria-describedby={describedBy('password_confirmation')}
                            onChange={(e) => setData('password_confirmation', e.target.value)}
                        />
                        <InputError id="password_confirmation-error" message={errorFor('password_confirmation')} className="mt-1.5" />
                    </div>

                    <PrimaryButton type="submit" className="h-11 w-full" disabled={processing}>
                        {processing ? (
                            <>
                                <Loader2 className="mr-2 h-4 w-4 motion-safe:animate-spin" aria-hidden="true" />
                                Creating account…
                            </>
                        ) : (
                            'Create account'
                        )}
                    </PrimaryButton>
                </form>

                <p className="mt-6 text-center text-sm text-slate-600">
                    Already have an account?{' '}
                    <Link href={route('login')} className="font-semibold text-skillup-blue hover:underline">
                        Sign in
                    </Link>
                </p>

                <p className="mt-6 flex items-center justify-center gap-1.5 text-xs text-slate-400">
                    <Lock className="h-3.5 w-3.5" aria-hidden="true" />
                    Secure sign-up — your information is protected.
                </p>
            </div>
        </AuthSplitLayout>
    );
}
