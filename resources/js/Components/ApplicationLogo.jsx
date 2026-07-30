import Img from '@/Components/Img';

export default function ApplicationLogo({ className, ...props }) {
    return (
        <Img
            src="/images/skillUp.png"
            alt="SkillUp"
            className={className}
            {...props}
        />
    );
}
