export default function InputLabel({
    value,
    required = false,
    className = '',
    children,
    ...props
}) {
    return (
        <label
            {...props}
            className={
                `block text-sm font-medium text-gray-700 dark:text-gray-300 ` +
                className
            }
        >
            {value ? value : children}
            {required && <span className="text-red-500" aria-hidden="true"> *</span>}
        </label>
    );
}
