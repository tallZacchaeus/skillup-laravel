export default function Checkbox({ className = '', ...props }) {
    return (
        <input
            {...props}
            type="checkbox"
            className={
                'rounded border-gray-300 text-skillup-blue shadow-sm focus:ring-skillup-blue dark:border-gray-700 dark:bg-gray-900 dark:focus:ring-skillup-blue dark:focus:ring-offset-gray-800 ' +
                className
            }
        />
    );
}
