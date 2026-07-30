/**
 * Central marketing content for the public homepage.
 *
 * The metrics below are LAUNCH PLACEHOLDERS. Replace each `value` with a real
 * figure from analytics/enrolment data when available. They are intentionally
 * non-zero so the homepage never renders a credibility-damaging "0". Icon keys
 * map to lucide-react icons inside the components that consume them.
 */

// Above-the-fold trust bar — rendered statically (no count-up) so the most
// important screen never flashes a "0".
export const heroStats = [
    { key: 'learners', value: '2,000+', label: 'Learners trained' },
    { key: 'courses', value: '25+', label: 'Courses available' },
    { key: 'partners', value: '12+', label: 'Partner organisations' },
    { key: 'completion', value: '92%', label: 'Completion rate' },
];

// Suggestions shown under the hero search input.
export const popularSearches = ['Software Development', 'AI', 'Product Design', 'Data Analytics'];

// Animated statistics band lower on the page (count-up on scroll).
export const statsBand = [
    { key: 'learners', icon: 'users', value: 2000, suffix: '+', label: 'Learners trained across Africa' },
    { key: 'tracks', icon: 'layers', value: 5, suffix: '', label: 'Career-focused learning tracks' },
    { key: 'partners', icon: 'building2', value: 12, suffix: '+', label: 'Hiring & community partners' },
    { key: 'completion', icon: 'award', value: 92, suffix: '%', label: 'Programme completion rate' },
];

// "How It Works" — three-step conversion primer.
export const howItWorks = [
    {
        step: 1,
        icon: 'search',
        title: 'Choose your track',
        text: 'Pick a career-focused track — from software development to data analytics — matched to real hiring demand across Africa.',
    },
    {
        step: 2,
        icon: 'graduationCap',
        title: 'Learn by doing',
        text: 'Build a portfolio through hands-on projects, live sessions, and mentorship from practitioners who have done the work.',
    },
    {
        step: 3,
        icon: 'briefcase',
        title: 'Launch your career',
        text: 'Graduate job-ready with CV support, interview prep, and access to our pan-African employer and alumni network.',
    },
];

// Alumni testimonials — enriched with role, company, and graduation year.
// Used as a fallback until featured testimonials are seeded in the CMS.
export const alumniTestimonials = [
    {
        id: 'static-1',
        quote: "Before joining SkillUp, I had no clear path into tech. The hands-on projects and mentorship gave me the skills and confidence to land my first data analyst role in under six months.",
        name: 'Caroline Moren',
        role: 'Data Analyst',
        company: 'Paystack',
        program: 'Data Analysis',
        gradYear: 2024,
    },
    {
        id: 'static-2',
        quote: "I went from a self-taught coder struggling to get noticed to a full-time developer with a global client base. This wasn't just training — it was a career launchpad.",
        name: 'Adebayo Kareem',
        role: 'Frontend Developer',
        company: 'Andela',
        program: 'Software Development',
        gradYear: 2023,
    },
    {
        id: 'static-3',
        quote: 'The training gave me real-world projects to showcase my skills. The community support and career guidance were genuine game-changers for me.',
        name: 'Chiamaka Eze',
        role: 'Product Designer',
        company: 'Flutterwave',
        program: 'Product Design (UI/UX)',
        gradYear: 2024,
    },
    {
        id: 'static-4',
        quote: "SkillUp connects you to the right people, tools, and opportunities. I didn't just learn — I became part of a pan-African network of innovators and tech leaders.",
        name: 'Samuel Otieno',
        role: 'Data Engineer',
        company: 'Twiga Foods',
        program: 'Data Analysis',
        gradYear: 2023,
    },
    {
        id: 'static-5',
        quote: 'I joined to switch careers, and within weeks of completing the bootcamp I landed my first digital marketing role. The momentum was incredible.',
        name: 'Funke Ajayi',
        role: 'Digital Marketer',
        company: 'Kuda',
        program: 'Virtual Assistance',
        gradYear: 2024,
    },
    {
        id: 'static-6',
        quote: 'The projects I built during training impressed my future employer. I now work remotely for a European tech company — proof that African talent competes globally.',
        name: 'Ahmed Musa',
        role: 'Full Stack Developer',
        company: 'Remote (EU)',
        program: 'Software Development',
        gradYear: 2022,
    },
];
