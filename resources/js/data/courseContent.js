/**
 * Curated, accurate landing-page content per track (value proposition, target
 * audiences, and real career paths). Keyed by track slug. Anything not listed
 * falls back to sensible, track-derived defaults so FUTURE courses inherit the
 * same experience automatically. Career titles are real roles for each field —
 * no fabricated salaries, counts, or statistics.
 */
const CONTENT = {
    'product-management': {
        valueProp: 'Learn to research, plan, and ship products customers love — the practical way employers actually hire for.',
        whoFor: ['Aspiring product managers', 'Ops & delivery professionals', 'Founders & entrepreneurs', 'Analysts moving into product'],
        careers: ['Product Manager', 'Associate Product Manager', 'Product Owner', 'Business Analyst'],
    },
    'software-development': {
        valueProp: 'Build responsive, real-world web applications from scratch with HTML, CSS, JavaScript, and modern tooling.',
        whoFor: ['Complete beginners', 'Career changers into tech', 'Students', 'Self-taught coders levelling up'],
        careers: ['Frontend Developer', 'Junior Web Developer', 'Software Engineer (entry)', 'Web Designer'],
    },
    'product-design': {
        valueProp: 'Turn user problems into clear, tested, and polished product experiences with research, prototyping, and design systems.',
        whoFor: ['Aspiring product designers', 'Graphic designers moving into UX', 'Career changers', 'Founders shaping their product'],
        careers: ['Product Designer', 'UI/UX Designer', 'UX Researcher', 'Interaction Designer'],
    },
    'virtual-assistance': {
        valueProp: 'Develop the remote-work operations skills that make you a reliable, in-demand virtual assistant.',
        whoFor: ['Aspiring virtual assistants', 'Remote-work starters', 'Admin & support professionals', 'Freelancers'],
        careers: ['Virtual Assistant', 'Executive Assistant (remote)', 'Operations Support', 'Client Success Assistant'],
    },
    'data-analysis': {
        valueProp: 'Clean, analyse, and communicate business data with Excel, SQL, and Power BI to drive real decisions.',
        whoFor: ['Aspiring data analysts', 'Ops & finance professionals', 'Career changers', 'Students'],
        careers: ['Data Analyst', 'Business Intelligence Analyst', 'Operations Analyst', 'Reporting Analyst'],
    },
};

const DEFAULT_AUDIENCES = ['Beginners', 'Career changers', 'Students', 'Working professionals'];

/**
 * Resolve landing content for a track. Uses curated content when available,
 * otherwise derives safe defaults from the track's own data.
 */
export function getCourseContent(track) {
    const curated = CONTENT[track.slug] || {};
    return {
        valueProp: curated.valueProp || track.summary || '',
        whoFor: curated.whoFor || DEFAULT_AUDIENCES,
        // Careers only shown when we have accurate, field-specific roles.
        careers: curated.careers || [],
    };
}

/**
 * Build truthful, data-derived FAQs from the track itself. Never invented.
 */
export function buildCourseFaqs(track) {
    const faqs = [
        {
            question: 'Do I need prior experience?',
            answer: 'No — the Basic level starts from the fundamentals and builds up, so beginners are welcome.',
        },
    ];

    if (track.duration && track.duration !== 'TBA' && track.duration !== 'Coming soon') {
        faqs.push({
            question: 'How long does it take?',
            answer: `The ${track.title} track runs for about ${track.duration}, with cohort dates confirmed at enrolment.`,
        });
    }

    faqs.push({
        question: 'Will I receive a certificate?',
        answer: 'Yes — you earn a SkillUp certificate on completion that you can share and verify.',
    });

    if (Array.isArray(track.tools) && track.tools.length > 0) {
        faqs.push({
            question: 'What tools will I use?',
            answer: `You’ll work hands-on with ${track.tools.join(', ')}.`,
        });
    }

    faqs.push({
        question: 'How is the course delivered?',
        answer: 'Training is online and cohort-based, so you can learn from anywhere with mentor support along the way.',
    });

    return faqs;
}
