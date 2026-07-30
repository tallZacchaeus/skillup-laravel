import { forwardRef, useState } from 'react';
import { Eye, EyeOff } from 'lucide-react';
import TextInput from '@/Components/TextInput';

/**
 * Password field with an accessible show/hide toggle. Toggling only flips the
 * input `type`, so the value, cursor position, and password-manager hooks
 * (name + autocomplete) are all preserved. Forwards a ref for focus management.
 */
export default forwardRef(function PasswordInput({ className = '', ...props }, ref) {
    const [visible, setVisible] = useState(false);

    return (
        <div className="relative">
            <TextInput
                ref={ref}
                type={visible ? 'text' : 'password'}
                className={`pr-11 ${className}`}
                {...props}
            />
            <button
                type="button"
                onClick={() => setVisible((value) => !value)}
                aria-label={visible ? 'Hide password' : 'Show password'}
                aria-pressed={visible}
                className="absolute right-1 top-1/2 inline-flex h-9 w-9 -translate-y-1/2 items-center justify-center rounded-md text-slate-400 transition-colors hover:text-slate-600 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-skillup-blue"
            >
                {visible ? <EyeOff className="h-5 w-5" aria-hidden="true" /> : <Eye className="h-5 w-5" aria-hidden="true" />}
            </button>
        </div>
    );
});
