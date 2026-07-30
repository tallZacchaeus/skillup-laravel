<?php

namespace Database\Seeders;

use App\Models\Platform\FutureModule;
use Illuminate\Database\Seeder;

class FutureModuleSeeder extends Seeder
{
    public function run(): void
    {
        $modules = [
            [
                'key' => 'school_youth_program',
                'name' => 'School / Youth Program',
                'summary' => 'B2B school partnerships, student cohorts, guardian consent, and school-level reporting.',
                'module_group' => 'schools',
                'public_path' => '/schools',
                'sort_order' => 10,
            ],
            [
                'key' => 'career_center',
                'name' => 'Career Center',
                'summary' => 'Career support hub for portfolio support, CV guidance, interviews, and career readiness.',
                'module_group' => 'career',
                'public_path' => '/career-center',
                'sort_order' => 20,
            ],
            [
                'key' => 'portfolio_cv_reviews',
                'name' => 'Portfolio / CV Reviews',
                'summary' => 'Learner request workflow for portfolio, CV, and resume review feedback.',
                'module_group' => 'career',
                'public_path' => null,
                'sort_order' => 30,
            ],
            [
                'key' => 'mock_interviews',
                'name' => 'Mock Interviews',
                'summary' => 'Interview scheduling, mentor assignment, preparation notes, and feedback.',
                'module_group' => 'career',
                'public_path' => null,
                'sort_order' => 40,
            ],
            [
                'key' => 'job_board',
                'name' => 'Job Board',
                'summary' => 'Curated job opportunities, applications, and employer-managed postings.',
                'module_group' => 'employers',
                'public_path' => '/jobs',
                'sort_order' => 50,
            ],
            [
                'key' => 'employer_portal',
                'name' => 'Employer Portal',
                'summary' => 'Employer account area for posting roles and discovering learner talent.',
                'module_group' => 'employers',
                'public_path' => '/employer',
                'sort_order' => 60,
            ],
            [
                'key' => 'alumni_directory',
                'name' => 'Alumni Directory',
                'summary' => 'Alumni profiles, cohort identity, success stories, and alumni community routing.',
                'module_group' => 'alumni',
                'public_path' => '/alumni',
                'sort_order' => 70,
            ],
            [
                'key' => 'certificate_builder_verification',
                'name' => 'Certificate Builder And Verification',
                'summary' => 'Certificate template management, issuance, credential wallet, and public verification.',
                'module_group' => 'credentials',
                'public_path' => '/certificates/verify',
                'sort_order' => 80,
            ],
            [
                'key' => 'ambassador_referral_program',
                'name' => 'Ambassador / Referral Program',
                'summary' => 'Ambassador applications, referral attribution, campaign links, and payout tracking.',
                'module_group' => 'growth',
                'public_path' => '/ambassadors',
                'sort_order' => 90,
            ],
        ];

        $defaultChecks = [
            'Core enrollment/payment/Moodle workflows are stable',
            'Phase 14 critical-flow tests pass',
            'Operations team approves launch scope',
            'Required policies, privacy copy, and support process are defined',
        ];

        foreach ($modules as $module) {
            FutureModule::updateOrCreate(
                ['key' => $module['key']],
                [
                    ...$module,
                    'status' => 'planned',
                    'is_publicly_visible' => false,
                    'readiness_checks' => $defaultChecks,
                ]
            );
        }
    }
}
