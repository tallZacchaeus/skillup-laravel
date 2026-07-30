<?php

namespace Database\Seeders;

use App\Enums\CohortStatus;
use App\Enums\ProductStatus;
use App\Models\Catalog\Cohort;
use App\Models\Catalog\CourseLevel;
use App\Models\Catalog\Product;
use App\Models\Catalog\ProductMedia;
use App\Models\Catalog\ProductMoodleMapping;
use App\Models\Catalog\ProductPaymentPlan;
use App\Models\Catalog\ProductPrice;
use App\Models\Catalog\ProductVisibilityRule;
use App\Models\Catalog\Track;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductCatalogueSeeder extends Seeder
{
    /**
     * Seed the MVP catalogue and future track placeholders.
     */
    public function run(): void
    {
        $tracks = [
            [
                'title' => 'Product Management',
                'slug' => 'product-management',
                'phase' => 'launch',
                'price' => 200000,
                'image_path' => '/images/Project%20Management.jpg',
                'card_subtitle' => 'Learn product research, strategy, planning, and delivery using practical industry tools.',
                'summary' => 'Learn product discovery, roadmapping, stakeholder communication, agile delivery, and product leadership.',
                'description' => 'A practical product management path for learners who want to understand customers, shape solutions, and lead delivery with confidence.',
                'outcomes' => ['Product discovery', 'Roadmap creation', 'Agile delivery', 'Portfolio case study'],
                'tools' => ['Notion', 'Jira', 'Miro', 'Analytics basics'],
            ],
            [
                'title' => 'Software Development',
                'slug' => 'software-development',
                'phase' => 'launch',
                'price' => 150000,
                'image_path' => '/images/Web%20Development.jpg',
                'card_subtitle' => 'Learn HTML, CSS, and JavaScript by building responsive, real-world web applications.',
                'summary' => 'Build responsive websites and application foundations with HTML, CSS, JavaScript, Git, and modern tooling.',
                'description' => 'A hands-on software development path for learners moving from fundamentals to deployable web projects.',
                'outcomes' => ['Responsive web pages', 'JavaScript projects', 'Git workflow', 'Deployment basics'],
                'tools' => ['HTML', 'CSS', 'JavaScript', 'Git'],
            ],
            [
                'title' => 'Product Design (UI/UX)',
                'slug' => 'product-design',
                'phase' => 'launch',
                'price' => 200000,
                'image_path' => '/images/Facilitators.jpg',
                'card_subtitle' => 'Master user research, wireframing, prototyping, and design systems using Figma and FigJam.',
                'summary' => 'Design usable digital products with research, wireframes, prototyping, usability testing, and design systems.',
                'description' => 'A product design track for learners who want to turn user problems into clear, tested, and polished product experiences.',
                'outcomes' => ['UX research', 'Wireframes', 'Clickable prototype', 'Design portfolio'],
                'tools' => ['Figma', 'FigJam', 'Design systems'],
            ],
            [
                'title' => 'Virtual Assistance',
                'slug' => 'virtual-assistance',
                'phase' => 'launch',
                'price' => 100000,
                'image_path' => '/images/Virtual%20Assistant.jpg',
                'card_subtitle' => 'Build practical administrative, communication, productivity, and client-management skills for remote work.',
                'summary' => 'Develop remote-work operations skills across scheduling, email, documentation, client support, and AI tools.',
                'description' => 'A practical remote operations path for learners who want to become reliable virtual assistants and support professionals.',
                'outcomes' => ['Admin workflows', 'Client communication', 'Productivity systems', 'Remote-work readiness'],
                'tools' => ['Google Workspace', 'Notion', 'Canva', 'AI tools'],
            ],
            [
                'title' => 'Data Analysis',
                'slug' => 'data-analysis',
                'phase' => 'launch',
                'price' => 200000,
                'image_path' => '/images/whygood.jpg',
                'card_subtitle' => 'Analyse and communicate business data using Excel, SQL, and Power BI.',
                'summary' => 'Use spreadsheets, SQL, dashboards, and storytelling to solve practical business problems with data.',
                'description' => 'A data analysis path for learners who want to clean data, build dashboards, and communicate business insights.',
                'outcomes' => ['Data cleaning', 'SQL analysis', 'Dashboard project', 'Insight presentation'],
                'tools' => ['Excel', 'SQL', 'Power BI', 'Python basics'],
            ],
            [
                'title' => 'Digital Marketing',
                'slug' => 'digital-marketing',
                'phase' => 'phase_2',
                'price' => null,
                'image_path' => null,
                'summary' => 'Future track for campaign strategy, content, analytics, paid channels, and growth operations.',
                'description' => 'A placeholder for the next phase of SKILLUP marketing and growth training.',
                'outcomes' => ['Campaign planning', 'Content systems', 'Analytics basics'],
                'tools' => ['Meta Ads', 'Google Analytics', 'Email tools'],
            ],
            [
                'title' => 'Cybersecurity',
                'slug' => 'cybersecurity',
                'phase' => 'phase_2',
                'price' => null,
                'image_path' => null,
                'summary' => 'Future track for security foundations, safe operations, risk thinking, and defensive workflows.',
                'description' => 'A placeholder for the next phase of SKILLUP cybersecurity training.',
                'outcomes' => ['Security foundations', 'Risk awareness', 'Defensive labs'],
                'tools' => ['Linux', 'Networking basics', 'Security labs'],
            ],
        ];

        foreach ($tracks as $index => $trackData) {
            $track = Track::updateOrCreate(
                ['slug' => $trackData['slug']],
                [
                    'title' => $trackData['title'],
                    'phase' => $trackData['phase'],
                    'status' => ProductStatus::Published,
                    'summary' => $trackData['summary'],
                    'description' => $trackData['description'],
                    'image_path' => $trackData['image_path'],
                    'outcomes' => $trackData['outcomes'],
                    'tools' => $trackData['tools'],
                    'is_featured' => $trackData['phase'] === 'launch',
                    'sort_order' => $index + 1,
                    'metadata' => [],
                ],
            );

            $levels = $this->seedLevels($track);

            if ($trackData['phase'] !== 'launch') {
                continue;
            }

            $basicLevel = $levels['basic'];
            $cohort = Cohort::updateOrCreate(
                ['slug' => "{$trackData['slug']}-basic-2026-q3"],
                [
                    'track_id' => $track->id,
                    'course_level_id' => $basicLevel->id,
                    'title' => "{$trackData['title']} Basic - 2026 Q3",
                    'status' => CohortStatus::Open,
                    'delivery_mode' => 'online',
                    'timezone' => 'Africa/Lagos',
                    'starts_at' => now()->addWeeks(3),
                    'ends_at' => now()->addMonths(3),
                    'enrollment_opens_at' => now()->subDay(),
                    'enrollment_closes_at' => now()->addWeeks(2),
                    'max_learners' => 40,
                    'enrolled_count' => 0,
                    'metadata' => [],
                ],
            );

            $product = Product::updateOrCreate(
                ['slug' => "{$trackData['slug']}-basic"],
                [
                    'track_id' => $track->id,
                    'course_level_id' => $basicLevel->id,
                    'cohort_id' => $cohort->id,
                    'title' => "{$trackData['title']} Basic",
                    'subtitle' => $trackData['card_subtitle'] ?? 'Foundations, practical projects, and guided learning.',
                    'description' => $trackData['description'],
                    'outcomes' => $trackData['outcomes'],
                    'syllabus' => [
                        ['week' => 1, 'title' => 'Foundations and tools'],
                        ['week' => 2, 'title' => 'Guided practice'],
                        ['week' => 3, 'title' => 'Portfolio project'],
                    ],
                    'requirements' => ['Laptop or tablet', 'Stable internet', 'Commitment to weekly practice'],
                    'status' => ProductStatus::Published,
                    'delivery_mode' => 'online',
                    'enrollment_cap' => 40,
                    'unlimited_enrollment' => false,
                    'published_at' => now()->subMinute(),
                    'is_featured' => true,
                    'sort_order' => $index + 1,
                    'metadata' => ['seeded' => true],
                ],
            );

            ProductMedia::updateOrCreate(
                ['product_id' => $product->id, 'is_primary' => true],
                [
                    'type' => 'image',
                    'disk' => 'public',
                    'path' => $trackData['image_path'],
                    'url' => null,
                    'alt_text' => "{$trackData['title']} course image",
                    'sort_order' => 0,
                    'metadata' => [],
                ],
            );

            ProductPrice::updateOrCreate(
                ['product_id' => $product->id, 'label' => 'Standard'],
                [
                    'currency' => 'NGN',
                    'amount' => $trackData['price'],
                    'compare_at_amount' => null,
                    'is_default' => true,
                    'is_active' => true,
                    'starts_at' => null,
                    'ends_at' => null,
                    'metadata' => [],
                ],
            );

            ProductPaymentPlan::updateOrCreate(
                ['product_id' => $product->id, 'slug' => 'two-part-installment'],
                [
                    'name' => 'Two-part installment',
                    'description' => 'Pay a deposit, then complete the balance in one monthly installment.',
                    'currency' => 'NGN',
                    'deposit_amount' => round($trackData['price'] / 2, 2),
                    'installment_amount' => round($trackData['price'] / 2, 2),
                    'installments_count' => 2,
                    'interval' => 'monthly',
                    'is_active' => true,
                    'metadata' => [],
                ],
            );

            ProductVisibilityRule::updateOrCreate(
                ['product_id' => $product->id, 'rule_type' => 'public'],
                [
                    'operator' => 'equals',
                    'value' => ['public' => true],
                    'starts_at' => null,
                    'ends_at' => null,
                    'is_active' => true,
                    'metadata' => [],
                ],
            );

            ProductMoodleMapping::updateOrCreate(
                ['product_id' => $product->id, 'moodle_course_id' => 'placeholder-'.$trackData['slug'].'-basic'],
                [
                    'moodle_category_id' => 'placeholder-'.$trackData['slug'],
                    'moodle_group_id' => null,
                    'moodle_cohort_id' => null,
                    'is_primary' => true,
                    'sync_enabled' => false,
                    'last_synced_at' => null,
                    'metadata' => ['placeholder' => true],
                ],
            );
        }
    }

    /**
     * @return array<string, CourseLevel>
     */
    private function seedLevels(Track $track): array
    {
        $levels = [
            'basic' => ['name' => 'Basic', 'rank' => 1, 'summary' => 'Foundations and first practical project.'],
            'intermediate' => ['name' => 'Intermediate', 'rank' => 2, 'summary' => 'Applied skills, real-world workflow, and portfolio depth.'],
            'advanced' => ['name' => 'Advanced', 'rank' => 3, 'summary' => 'Specialization, capstone work, mentorship, and leadership readiness.'],
        ];

        return collect($levels)
            ->mapWithKeys(function (array $level, string $slug) use ($track) {
                $model = CourseLevel::updateOrCreate(
                    ['track_id' => $track->id, 'slug' => $slug],
                    [
                        'name' => $level['name'],
                        'rank' => $level['rank'],
                        'status' => 'active',
                        'summary' => $level['summary'],
                        'metadata' => ['code' => Str::upper($slug)],
                    ],
                );

                return [$slug => $model];
            })
            ->all();
    }
}
