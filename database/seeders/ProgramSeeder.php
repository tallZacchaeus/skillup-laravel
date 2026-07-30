<?php

namespace Database\Seeders;

use App\Enums\DiscountRuleStatus;
use App\Enums\DiscountType;
use App\Enums\ProductStatus;
use App\Enums\ProgramEditionStatus;
use App\Models\Catalog\DiscountRule;
use App\Models\Catalog\Product;
use App\Models\Catalog\ProductPrice;
use App\Models\Catalog\Track;
use App\Models\Programs\Program;
use App\Models\Programs\ProgramEdition;
use App\Models\Programs\ProgramEditionTrack;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Summer AI 2026 — the first Annual Programs edition, seeded from the
 * official programme document. Idempotent: safe to re-run.
 */
class ProgramSeeder extends Seeder
{
    public function run(): void
    {
        $program = Program::updateOrCreate(
            ['slug' => 'summer-ai'],
            [
                'name' => 'Summer AI',
                'tagline' => 'A yearly, project-based AI & Python summer programme for children and teens.',
                'description' => 'Every summer, SkillUp Plus runs an intensive, in-person AI bootcamp where 8–18 year-olds build something real — a game, a calculator, or an AI-powered mini-application.',
                'is_active' => true,
            ],
        );

        $edition = ProgramEdition::updateOrCreate(
            ['program_id' => $program->id, 'slug' => '2026'],
            [
                'year' => 2026,
                'title' => 'Summer AI Bootcamp 2026',
                'theme' => 'Learn Python. Build AI. Create the future.',
                'status' => ProgramEditionStatus::RegistrationOpen,
                'starts_on' => '2026-08-03',
                'ends_on' => '2026-08-28',
                'schedule_text' => 'Mon–Fri, 9:30 AM – 2:30 PM',
                'delivery_mode' => 'in_person',
                'venue_name' => 'Expression Nation Hub, House of Favour',
                'venue_address' => 'Redemption City, Mowe, Ogun State',
                'capacity_total' => 100,
                'payment_mode' => 'immediate',
                'seat_hold_minutes' => 45,
                'allow_installments' => false,
                'refund_policy' => 'Full refund up to 7 days before the programme starts; 50% refund during the first week; no refunds afterwards. Waitlisted registrations are never charged.',
                'registration_fields' => [
                    ['key' => 'tshirt_size', 'label' => "Child's T-shirt size", 'type' => 'select', 'options' => ['XS (6-8)', 'S (10-12)', 'M (14-16)', 'L (Adult S)', 'XL (Adult M)'], 'required' => true],
                    ['key' => 'has_laptop', 'label' => 'Will your child bring a laptop?', 'type' => 'select', 'options' => ['Yes', 'No — please advise'], 'required' => true],
                    ['key' => 'prior_experience', 'label' => 'Any prior coding experience? (optional)', 'type' => 'textarea', 'options' => [], 'required' => false],
                ],
                'contact_whatsapp' => '+2348012345678',
                'contact_email' => 'hello@skillupedtech.com',
                'seo_title' => 'Summer AI Bootcamp 2026 — SkillUp Plus | Ages 8–18',
                'seo_description' => 'A 4-week, in-person AI & Python bootcamp for ages 8–18 at Expression Nation Hub, Redemption City. Learn Python. Build AI. Create the future.',
                'content' => $this->contentBlocks(),
            ],
        );

        $catalogTrack = Track::firstOrCreate(
            ['slug' => 'skillup-plus-programs'],
            [
                'title' => 'SkillUp Plus Programs',
                'phase' => 'programs',
                'status' => 'published',
                'summary' => 'Seasonal youth programmes run by SkillUp Plus.',
                'is_featured' => false,
                'sort_order' => 99,
                'metadata' => ['internal' => true],
            ],
        );

        $tracks = [
            [
                'slug' => 'alpha-ai',
                'name' => 'Alpha AI',
                'age_min' => 8,
                'age_max' => 13,
                'summary' => 'The pre-teen track: AI concepts and Python fundamentals taught through visual, game-based lessons. Every participant builds a working mini-project — a simple game or calculator.',
                'sort_order' => 0,
            ],
            [
                'slug' => 'ai-explorer',
                'name' => 'AI Explorer',
                'age_min' => 14,
                'age_max' => 18,
                'summary' => 'The teen track: structured, applied AI & Python content building progressively toward a functional AI-powered mini-application or automation script.',
                'sort_order' => 1,
            ],
        ];

        foreach ($tracks as $data) {
            $product = Product::updateOrCreate(
                ['slug' => "summer-ai-2026-{$data['slug']}"],
                [
                    'uuid' => (string) Str::uuid(),
                    'track_id' => $catalogTrack->id,
                    'title' => "Summer AI 2026 — {$data['name']}",
                    'subtitle' => "Ages {$data['age_min']}–{$data['age_max']} · 4 weeks · in person",
                    'description' => $data['summary'],
                    'status' => ProductStatus::Published,
                    'delivery_mode' => 'in_person',
                    'enrollment_cap' => 50,
                    'unlimited_enrollment' => false,
                    'published_at' => now(),
                    'metadata' => ['program' => 'summer-ai', 'edition' => 2026],
                ],
            );

            ProductPrice::updateOrCreate(
                ['product_id' => $product->id, 'label' => 'Standard'],
                [
                    'currency' => 'NGN',
                    'amount' => 100000,
                    'is_default' => true,
                    'is_active' => true,
                ],
            );

            ProgramEditionTrack::updateOrCreate(
                ['program_edition_id' => $edition->id, 'slug' => $data['slug']],
                [
                    'product_id' => $product->id,
                    'name' => $data['name'],
                    'age_min' => $data['age_min'],
                    'age_max' => $data['age_max'],
                    'capacity' => 50,
                    'summary' => $data['summary'],
                    'curriculum' => $this->journey(),
                    'sort_order' => $data['sort_order'],
                ],
            );

            // Early bird: 10% off, auto-applied, until two weeks before start.
            DiscountRule::updateOrCreate(
                ['slug' => "summer-ai-2026-early-bird-{$data['slug']}"],
                [
                    'name' => "Summer AI 2026 Early Bird — {$data['name']}",
                    'description' => '10% early-bird discount, applied automatically.',
                    'status' => DiscountRuleStatus::Active,
                    'type' => DiscountType::Percentage,
                    'value' => 10,
                    'currency' => 'NGN',
                    'product_id' => $product->id,
                    'starts_at' => now()->subDay(),
                    'ends_at' => '2026-07-31 23:59:59',
                    'per_email_limit' => 3,
                    'per_user_limit' => 3,
                    'requires_code' => false,
                    'requires_email_eligibility' => false,
                    'installment_compatible' => false,
                    'stackable' => false,
                    'is_public' => true,
                ],
            );
        }
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function contentBlocks(): array
    {
        return [
            ['type' => 'quick_facts', 'data' => ['items' => [
                ['label' => 'Dates', 'value' => 'Aug 3 – 28, 2026'],
                ['label' => 'Hours', 'value' => 'Mon–Fri · 9:30 AM – 2:30 PM'],
                ['label' => 'Venue', 'value' => 'Expression Nation Hub, Mowe'],
                ['label' => 'Tracks', 'value' => 'Alpha AI · AI Explorer'],
                ['label' => 'Fee', 'value' => '₦100,000 all-inclusive'],
                ['label' => 'Capacity', 'value' => '100 seats · 50 per track'],
            ]]],
            ['type' => 'overview', 'data' => [
                'title' => 'Programme Overview',
                'body' => 'Skillup Plus is running a four-week, in-person summer programme in AI and Python for children and teens across the Mowe–Redemption City community. Rather than a passive tutorial series, every session is project-based: participants leave the programme having personally built something real — a game, a calculator, or an AI-powered mini-application.',
            ]],
            ['type' => 'why', 'data' => [
                'title' => 'Why This Programme',
                'items' => [
                    ['title' => 'They build real projects', 'text' => 'Games, calculators, and AI-powered mini-apps, written in Python and working by the end of the programme.'],
                    ['title' => 'Rested, engaged facilitators', 'text' => 'Four facilitators rotate weekly — teach one week, rest the next — so every session is led by someone sharp and prepared.'],
                    ['title' => 'A safe, local, in-person hub', 'text' => 'Daily sessions at The Expression Nation Hub in Redemption City, Mowe.'],
                    ['title' => 'Age-matched instruction', 'text' => 'Alpha AI stays visual and gamified; AI Explorer moves at a sharper, applied pace.'],
                    ['title' => 'Certificate of Participation', 'text' => 'Awarded to every participant who completes the four weeks.'],
                    ['title' => 'An early start on digital fluency', 'text' => 'Meeting the 8–18 age window where curiosity and confidence with technology take shape.'],
                ],
            ]],
            ['type' => 'tracks', 'data' => [
                'title' => 'The Two Tracks',
                'subtitle' => 'Same hub, same schedule, same commitment to hands-on building — paced for each age group.',
            ]],
            ['type' => 'journey', 'data' => [
                'title' => 'The 4-Week Journey',
                'items' => $this->journey(),
            ]],
            ['type' => 'includes', 'data' => [
                'title' => "What's Included",
                'subtitle' => 'The ₦100,000 programme fee is all-inclusive — no hidden extras across the four weeks.',
                'items' => [
                    'Full facilitator instruction — daily teaching from track-matched facilitators for all 4 weeks',
                    'Learning materials — everything needed to follow along and build',
                    'In-class internet access — reliable connectivity for live coding and AI project testing',
                    'Daily refreshments — water and refreshments provided through each session day',
                    'Branded programme T-shirt — a Skillup Plus souvenir T-shirt for every participant',
                    'Certificate of Participation — awarded on completion of the 4-week programme',
                ],
            ]],
            ['type' => 'team', 'data' => [
                'title' => 'Programme Team',
                'items' => [
                    ['role' => 'Project Supervisor', 'text' => 'Oversees daily programme delivery and instruction quality across both tracks.'],
                    ['role' => 'Project Assistant', 'text' => 'Coordinates logistics, materials, and day-to-day programme operations.'],
                    ['role' => '4 Facilitators', 'text' => '2 per track, on a weekly rotation — teach one week, rest the next — keeping every session sharp and well-prepared.'],
                    ['role' => '2 Class Assistants', 'text' => 'Corps members providing additional in-class support to keep sessions running smoothly.'],
                ],
            ]],
            ['type' => 'faqs', 'data' => [
                'title' => 'Frequently Asked Questions',
                'items' => [
                    ['question' => 'Does my child need prior coding experience?', 'answer' => 'No. Both tracks start from the basics — Alpha AI introduces AI and Python visually for younger learners, while AI Explorer builds up progressively for teens with no assumed prior experience.'],
                    ['question' => 'Is this in-person or online?', 'answer' => 'In-person, daily, Monday to Friday, at The Expression Nation Hub, House of Favour, Redemption City, Mowe, Ogun State — from 9:30 AM to 2:30 PM.'],
                    ['question' => 'How is Alpha AI different from AI Explorer?', 'answer' => 'Alpha AI (ages 8–13) uses visual, gamified instruction suited to younger learners. AI Explorer (ages 14–18) is more structured and applied, building progressively toward a functional project. Both end with a real, working project the participant builds.'],
                    ['question' => 'What will my child actually build?', 'answer' => 'Simple games built with Python logic, functional calculators, or AI-powered mini-applications and automation scripts — a real, working project your child can show off.'],
                    ['question' => 'What does the ₦100,000 fee cover?', 'answer' => "It's all-inclusive: facilitator instruction, learning materials, in-class internet access, daily refreshments, and a branded programme T-shirt — for the full 4-week programme."],
                    ['question' => 'Will my child receive a certificate?', 'answer' => 'Yes — every participant who completes the 4-week programme (with a completed onboarding form) receives a Certificate of Participation.'],
                    ['question' => 'How many spots are available?', 'answer' => '100 total seats across both tracks — 50 for Alpha AI and 50 for AI Explorer. Payment secures your child\'s seat.'],
                    ['question' => 'What happens after I register?', 'answer' => 'Confirm your email, pay to secure the seat, then complete a short onboarding form. Our team is also available on WhatsApp for any questions.'],
                ],
            ]],
            ['type' => 'venue', 'data' => [
                'title' => 'Venue',
                'note' => 'A safe, supervised, in-person learning hub — drop-off from 9:00 AM, pickup by 3:00 PM.',
            ]],
            ['type' => 'cta', 'data' => [
                'title' => 'Limited slots available',
                'subtitle' => 'Enjoy 10% off with early-bird registration. 100 seats total — 50 per track.',
                'cta' => 'Register Now',
            ]],
        ];
    }

    /**
     * @return array<int, array<string, string>>
     */
    private function journey(): array
    {
        return [
            ['week' => 'Week 1 — Foundations', 'focus' => "Getting comfortable with Python logic and core AI ideas, introduced through visual and interactive exercises matched to each track's pace."],
            ['week' => 'Week 2 — Building blocks', 'focus' => "Applying what's been learned to small working pieces of code — the early logic behind a game, calculator, or automation script."],
            ['week' => 'Week 3 — Assembling the project', 'focus' => "Combining what's been built into a functioning project: a real game, calculator, or AI-powered mini-app."],
            ['week' => 'Week 4 — Polish & Showcase Day', 'focus' => 'Final refinements, then each participant presents their finished project to family and friends.'],
        ];
    }
}
