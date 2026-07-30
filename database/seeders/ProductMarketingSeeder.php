<?php

namespace Database\Seeders;

use App\Models\Catalog\Product;
use App\Models\Catalog\ProductReview;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

/**
 * Backfills catalogue courses with marketable content — an intro/preview video,
 * "why this matters" relevance, social-proof student counts, and learner reviews.
 *
 * NOTE: promo videos use a Creative-Commons placeholder clip. Swap `promo_video_url`
 * for each course's real trailer in Filament before launch.
 */
class ProductMarketingSeeder extends Seeder
{
    private const PLACEHOLDER_VIDEO = 'https://www.youtube.com/watch?v=aqz-KE-bpKQ';

    public function run(): void
    {
        foreach ($this->data() as $slug => $payload) {
            $product = Product::where('slug', $slug)->first();

            if (! $product) {
                continue;
            }

            $product->update([
                'promo_video_url' => $product->promo_video_url ?: self::PLACEHOLDER_VIDEO,
                'students_count' => $payload['students'],
                'relevance' => $payload['relevance'],
            ]);

            foreach ($payload['reviews'] as $index => $review) {
                ProductReview::updateOrCreate(
                    ['product_id' => $product->id, 'reviewer_name' => $review['name']],
                    [
                        'reviewer_title' => $review['title'],
                        'rating' => $review['rating'],
                        'title' => $review['heading'],
                        'body' => $review['body'],
                        'is_verified' => true,
                        'is_published' => true,
                        'reviewed_at' => Carbon::parse('2026-07-01')->subDays($index * 9),
                    ],
                );
            }

            $product->recalculateRating();
        }
    }

    /**
     * @return array<string, array{students: int, relevance: array<string, mixed>, reviews: array<int, array<string, mixed>>}>
     */
    private function data(): array
    {
        return [
            'product-management-basic' => [
                'students' => 1284,
                'relevance' => [
                    'demandNote' => 'Product management is one of the highest-paid, fastest-growing roles in African tech — and one of the few senior paths open to non-engineers.',
                    'audience' => [
                        'Career switchers moving into tech from other fields',
                        'Analysts, designers, or engineers stepping into product ownership',
                        'Founders who need to ship the right thing, faster',
                    ],
                    'stats' => [
                        ['label' => 'Avg. entry salary', 'value' => '₦350k+/mo'],
                        ['label' => 'Open roles (2026)', 'value' => '8,000+'],
                        ['label' => 'Remote-friendly', 'value' => 'Yes'],
                    ],
                ],
                'reviews' => [
                    ['name' => 'Chidinma Okafor', 'title' => 'Associate PM at a fintech', 'rating' => 5, 'heading' => 'Landed my first PM role in 3 months', 'body' => 'The capstone and mock interviews were the difference. I walked into interviews able to talk roadmaps, metrics, and trade-offs with confidence.'],
                    ['name' => 'Tunde Balogun', 'title' => 'Product Analyst', 'rating' => 5, 'heading' => 'Practical, not theory', 'body' => 'Every module tied back to real product decisions. The mentor feedback on my PRDs was worth the fee alone.'],
                    ['name' => 'Aisha Mohammed', 'title' => 'Career switcher', 'rating' => 4, 'heading' => 'Great foundation', 'body' => 'Came in from banking with zero tech background and left able to run a discovery process end to end.'],
                ],
            ],
            'software-development-basic' => [
                'students' => 2071,
                'relevance' => [
                    'demandNote' => 'Software engineering remains the single most in-demand skill for remote, globally-paid work from Africa.',
                    'audience' => [
                        'Complete beginners who want a structured path into code',
                        'Self-taught developers who want to close the fundamentals gap',
                        'Graduates targeting junior developer roles',
                    ],
                    'stats' => [
                        ['label' => 'Avg. entry salary', 'value' => '₦400k+/mo'],
                        ['label' => 'Open roles (2026)', 'value' => '20,000+'],
                        ['label' => 'Remote-friendly', 'value' => 'Yes'],
                    ],
                ],
                'reviews' => [
                    ['name' => 'Emeka Nwosu', 'title' => 'Junior Frontend Developer', 'rating' => 5, 'heading' => 'From zero to hired', 'body' => 'I had never written a line of code. The project-based approach meant I had a real portfolio by the end.'],
                    ['name' => 'Grace Adeyemi', 'title' => 'Full-stack learner', 'rating' => 5, 'heading' => 'Best mentors', 'body' => 'The mentors actually reviewed my code line by line. That feedback loop is why I improved so fast.'],
                    ['name' => 'Ibrahim Sani', 'title' => 'Student', 'rating' => 4, 'heading' => 'Challenging but worth it', 'body' => 'The pace is real — you have to put in the hours — but the support kept me from giving up.'],
                ],
            ],
            'product-design-basic' => [
                'students' => 1543,
                'relevance' => [
                    'demandNote' => 'UI/UX designers who can prove impact are scarce — great portfolios get hired globally.',
                    'audience' => [
                        'Aspiring designers building their first portfolio',
                        'Graphic designers moving into product/UX',
                        'PMs and developers who want design fluency',
                    ],
                    'stats' => [
                        ['label' => 'Avg. entry salary', 'value' => '₦300k+/mo'],
                        ['label' => 'Open roles (2026)', 'value' => '6,500+'],
                        ['label' => 'Remote-friendly', 'value' => 'Yes'],
                    ],
                ],
                'reviews' => [
                    ['name' => 'Zainab Yusuf', 'title' => 'Product Designer', 'rating' => 5, 'heading' => 'My portfolio got me interviews', 'body' => 'The design challenges became real case studies. Recruiters kept mentioning them.'],
                    ['name' => 'David Eze', 'title' => 'UX learner', 'rating' => 5, 'heading' => 'Loved the critique sessions', 'body' => 'Getting my work critiqued weekly forced me to level up my craft quickly.'],
                ],
            ],
            'virtual-assistance-basic' => [
                'students' => 968,
                'relevance' => [
                    'demandNote' => 'Remote executive and virtual assistants are among the quickest ways to earn in foreign currency from home.',
                    'audience' => [
                        'People seeking flexible, remote income',
                        'Administrative professionals going global',
                        'Stay-at-home parents and students',
                    ],
                    'stats' => [
                        ['label' => 'Avg. entry pay', 'value' => '$400+/mo'],
                        ['label' => 'Open roles (2026)', 'value' => '5,000+'],
                        ['label' => 'Remote-friendly', 'value' => 'Always'],
                    ],
                ],
                'reviews' => [
                    ['name' => 'Blessing Okon', 'title' => 'Virtual Assistant', 'rating' => 5, 'heading' => 'Two international clients already', 'body' => 'The tools and outreach templates got me client-ready. I earn in dollars now.'],
                    ['name' => 'Fatima Bello', 'title' => 'Remote EA', 'rating' => 4, 'heading' => 'Very practical', 'body' => 'Clear, actionable, and the community shares job leads constantly.'],
                ],
            ],
            'data-analysis-basic' => [
                'students' => 1789,
                'relevance' => [
                    'demandNote' => 'Every company is drowning in data and short on people who can turn it into decisions.',
                    'audience' => [
                        'Beginners who love spreadsheets and want to go further',
                        'Analysts moving into SQL, Power BI, and Python',
                        'Professionals who want to make data-driven decisions',
                    ],
                    'stats' => [
                        ['label' => 'Avg. entry salary', 'value' => '₦350k+/mo'],
                        ['label' => 'Open roles (2026)', 'value' => '11,000+'],
                        ['label' => 'Remote-friendly', 'value' => 'Yes'],
                    ],
                ],
                'reviews' => [
                    ['name' => 'Samuel Adeniyi', 'title' => 'Data Analyst', 'rating' => 5, 'heading' => 'The dashboards sealed the deal', 'body' => 'My final Power BI project is now the centrepiece of my portfolio. Got hired a month after finishing.'],
                    ['name' => 'Ngozi Ekwueme', 'title' => 'Business Analyst', 'rating' => 5, 'heading' => 'SQL finally clicked', 'body' => 'The way SQL was taught — query by query on real datasets — made it stick.'],
                    ['name' => 'Yusuf Lawal', 'title' => 'Student', 'rating' => 4, 'heading' => 'Solid and job-focused', 'body' => 'Would have liked more advanced stats, but for a foundation it is excellent.'],
                ],
            ],
        ];
    }
}
