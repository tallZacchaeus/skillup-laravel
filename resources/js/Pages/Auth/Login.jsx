import { useRef, useState } from 'react';
import { Head, Link, useForm } from '@inertiajs/react';
import { AlertCircle, CheckCircle2, Loader2, Lock } from 'lucide-react';
import AuthSplitLayout from '@/Layouts/AuthSplitLayout';
import Checkbox from '@/Components/Checkbox';
import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import PasswordInput from '@/Components/PasswordInput';
import PrimaryButton from '@/Components/PrimaryButton';
import TextInput from '@/Components/TextInput';

const EMAIL_RE = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

export default function Login({ status, canResetPassword }) {
    const { data, setData, post, processing, reset, transform } = useForm({
        email: '',
        password: '',
        remember: false,
    });

    const [clientErrors, setClientErrors] = useState({});
    const [authFailed, setAuthFailed] = useState(false);
    const emailRef = useRef(null);
    const passwordRef = useRef(null);

    // Trim the email before it reaches the backend (prevents whitespace-only / padded values).
    transform((payload) => ({ ...payload, email: payload.email.trim() }));

    const validate = () => {
        const errs = {};
        const email = data.email.trim();
        if (!email) errs.email = 'Please enter your email.';
        else if (!EMAIL_RE.test(email)) errs.email = 'Enter a valid email address.';
        if (!data.password) errs.password = 'Please enter your password.';
        return errs;
    };

    const submit = (e) => {
        e.preventDefault();
        if (processing) return;

        const errs = validate();
        if (Object.keys(errs).length > 0) {
            setClientErrors(errs);
            (errs.email ? emailRef : passwordRef).current?.focus();
            return;
        }

        setClientErrors({});
        setAuthFailed(false);
        post(route('login'), {
            onError: () => setAuthFailed(true),
            onFinish: () => reset('password'),
        });
    };

    return (
        <AuthSplitLayout>
            <Head title="Log in" />

            <div className="motion-safe:animate-fade-in-up">
                <h1 className="text-2xl font-bold tracking-tight text-skillup-navy sm:text-3xl">Welcome back</h1>
                <p className="mt-2 text-sm text-slate-600">Sign in to continue learning and access your courses.</p>

                {status && (
                    <div className="mt-6 flex items-start gap-2 rounded-lg border border-emerald-200 bg-emerald-50 p-3" role="status" aria-live="polite">
                        <CheckCircle2 className="mt-0.5 h-4 w-4 flex-shrink-0 text-emerald-500" aria-hidden="true" />
                        <p className="text-sm font-medium text-emerald-800">{status}</p>
                    </div>
                )}

                {authFailed && (
                    <div className="mt-6 flex items-start gap-2 rounded-lg border border-red-200 bg-red-50 p-3" role="alert" aria-live="assertive">
                        <AlertCircle className="mt-0.5 h-4 w-4 flex-shrink-0 text-red-500" aria-hidden="true" />
                        <p className="text-sm font-medium text-red-800">
                            Incorrect email or password. Try again or reset your password.
                        </p>
                    </div>
                )}

                <form onSubmit={submit} className="mt-6 space-y-5" noValidate>
                    <div>
                        <InputLabel required htmlFor="email" value="Email" />
                        <TextInput
                            id="email"
                            ref={emailRef}
                            type="email"
                            name="email"
                            value={data.email}
                            className="mt-1 block h-11 w-full"
                            autoComplete="email"
                            isFocused
                            aria-required="true"
                            aria-invalid={clientErrors.email ? 'true' : undefined}
                            aria-describedby={clientErrors.email ? 'email-error' : undefined}
                            onChange={(e) => setData('email', e.target.value)}
                        />
                        <InputError id="email-error" message={clientErrors.email} className="mt-1.5" />
                    </div>

                    <div>
                        <div className="flex items-center justify-between">
                            <InputLabel required htmlFor="password" value="Password" />
                            {canResetPassword && (
                                <Link
                                    href={route('password.request')}
                                    className="rounded text-sm font-medium text-skillup-blue transition-colors hover:underline focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-skillup-blue/40"
                                >
                                    Forgot password?
                                </Link>
                            )}
                        </div>
                        <PasswordInput
                            id="password"
                            ref={passwordRef}
                            name="password"
                            value={data.password}
                            className="mt-1 block h-11 w-full"
                            autoComplete="current-password"
                            aria-required="true"
                            aria-invalid={clientErrors.password ? 'true' : undefined}
                            aria-describedby={clientErrors.password ? 'password-error' : undefined}
                            onChange={(e) => setData('password', e.target.value)}
                        />
                        <InputError id="password-error" message={clientErrors.password} className="mt-1.5" />
                    </div>

                    <label className="flex min-h-11 cursor-pointer items-center gap-2.5">
                        <Checkbox
                            name="remember"
                            checked={data.remember}
                            onChange={(e) => setData('remember', e.target.checked)}
                            className="h-4 w-4"
                        />
                        <span className="text-sm text-slate-700">Remember me for 30 days</span>
                    </label>

                    <PrimaryButton type="submit" className="h-11 w-full" disabled={processing}>
                        {processing ? (
                            <>
                                <Loader2 className="mr-2 h-4 w-4 motion-safe:animate-spin" aria-hidden="true" />
                                Signing in…
                            </>
                        ) : (
                            'Sign in'
                        )}
                    </PrimaryButton>
                </form>

                <p className="mt-6 text-center text-sm text-slate-600">
                    Don’t have an account?{' '}
                    <Link href={route('register')} className="font-semibold text-skillup-blue hover:underline">
                        Create an account
                    </Link>
                </p>

                <p className="mt-6 flex items-center justify-center gap-1.5 text-xs text-slate-400">
                    <Lock className="h-3.5 w-3.5" aria-hidden="true" />
                    Secure sign-in — your information is protected.
                </p>
            </div>
        </AuthSplitLayout>
    );
}
