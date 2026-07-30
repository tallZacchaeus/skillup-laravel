<?php

namespace Tests\Feature\Quality;

use App\Enums\DiscountRedemptionStatus;
use App\Enums\DiscountType;
use App\Filament\Resources\DiscourseConnectionResource;
use App\Filament\Resources\LmsSyncLogResource;
use App\Filament\Resources\Notifications\EmailMessageResource;
use App\Filament\Resources\PaymentWebhookEventResource;
use App\Filament\Resources\ProductResource;
use App\Models\Catalog\CourseLevel;
use App\Models\Catalog\DiscountCode;
use App\Models\Catalog\DiscountEligibleEmail;
use App\Models\Catalog\DiscountEligibilityList;
use App\Models\Catalog\DiscountRule;
use App\Models\Catalog\Product;
use App\Models\Catalog\ProductPaymentPlan;
use App\Models\Catalog\ProductPrice;
use App\Models\Catalog\Track;
use App\Models\User;
use App\Services\Payments\CheckoutOrderService;
use App\Services\Payments\PaymentService;
use Database\Seeders\ProductCatalogueSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class Phase14QualityTest extends TestCase
{
    use RefreshDatabase;

    public function test_checkout_integrates_email_discount_and_installment_pricing(): void
    {
        $user = User::factory()->create(['email' => 'buyer@example.com']);
        $product = $this->publishedProductWithPrice(200000);
        $plan = ProductPaymentPlan::factory()->create([
            'product_id' => $product->id,
            'deposit_amount' => 50000,
            'installment_amount' => 50000,
            'installments_count' => 3,
            'is_active' => true,
        ]);
        $rule = DiscountRule::factory()->active()->create([
            'product_id' => $product->id,
            'type' => DiscountType::FixedAmount,
            'value' => 50000,
            'requires_email_eligibility' => true,
            'installment_compatible' => true,
        ]);
        DiscountCode::factory()->create([
            'discount_rule_id' => $rule->id,
            'code' => 'TEAM50',
        ]);
        $list = DiscountEligibilityList::factory()->create(['discount_rule_id' => $rule->id]);
        DiscountEligibleEmail::factory()->create([
            'discount_eligibility_list_id' => $list->id,
            'email' => 'Buyer@Example.com',
        ]);

        $this->actingAs($user);

        $order = app(CheckoutOrderService::class)->create($product, [
            'name' => 'Buyer One',
            'email' => 'buyer@example.com',
            'phone' => '08000000000',
            'discount_code' => 'team50',
            'payment_mode' => 'installment',
            'product_payment_plan_id' => $plan->id,
        ])->fresh(['paymentPlan.installments', 'invoices']);

        $installments = $order->paymentPlan->installments
            ->sortBy('installment_number')
            ->pluck('amount')
            ->map(fn ($amount) => (float) $amount)
            ->values()
            ->all();

        $this->assertSame('200000.00', $order->subtotal);
        $this->assertSame('50000.00', $order->discount_total);
        $this->assertSame('150000.00', $order->total);
        $this->assertSame('150000.00', $order->balance_due);
        $this->assertSame([50000.0, 50000.0, 50000.0], $installments);
        $this->assertSame(50000.0, app(PaymentService::class)->payableAmount($order));
        $this->assertSame('150000.00', $order->invoices->first()->total);
        $this->assertDatabaseHas('discount_redemptions', [
            'order_id' => $order->id,
            'email' => 'buyer@example.com',
            'status' => DiscountRedemptionStatus::Locked->value,
            'discount_amount' => 50000,
            'total_after_discount' => 150000,
        ]);
    }

    public function test_checkout_rejects_hidden_products(): void
    {
        $this->seed(ProductCatalogueSeeder::class);

        $product = Product::where('slug', 'product-management-basic')->firstOrFail();
        $product->update(['status' => 'hidden']);

        $this->get(route('checkout.details', $product))
            ->assertNotFound();

        $this->post(route('checkout.store', $product), [
            'name' => 'Buyer One',
            'email' => 'buyer@example.com',
            'phone' => '08000000000',
            'payment_mode' => 'full',
        ])->assertNotFound();
    }

    public function test_admin_filament_resources_are_available_to_admins_and_blocked_from_learners(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);

        $admin = User::factory()->create();
        $admin->assignRole('Admin');

        $learner = User::factory()->create();
        $learner->assignRole('Learner');

        $adminUrls = [
            ProductResource::getUrl('index', [], false, 'admin'),
            PaymentWebhookEventResource::getUrl('index', [], false, 'admin'),
            LmsSyncLogResource::getUrl('index', [], false, 'admin'),
            EmailMessageResource::getUrl('index', [], false, 'admin'),
            DiscourseConnectionResource::getUrl('index', [], false, 'admin'),
        ];

        $this->assertTrue($admin->canAccessPanel(Filament::getPanel('admin')));
        $this->assertFalse($learner->canAccessPanel(Filament::getPanel('admin')));

        foreach ($adminUrls as $url) {
            $this->assertStringStartsWith('/admin/', $url);
            $this->actingAs($learner)->get($url)->assertForbidden();

            if (extension_loaded('intl')) {
                $this->actingAs($admin)->get($url)->assertOk();
            }
        }
    }

    public function test_public_ui_motion_and_color_contracts_are_enforced(): void
    {
        $css = File::get(resource_path('css/app.css'));
        $this->assertStringContainsString('@media (prefers-reduced-motion: reduce)', $css);
        $colorBlendSuffix = 'grad'.'ient';
        $forbiddenColorTokens = [
            'bg-'.$colorBlendSuffix,
            'linear-'.$colorBlendSuffix,
            'radial-'.$colorBlendSuffix,
            'fr'.'om-',
            'vi'.'a-',
            'to'.'-',
        ];

        foreach (File::allFiles(resource_path('js')) as $file) {
            $contents = File::get($file->getPathname());

            foreach ($forbiddenColorTokens as $token) {
                $this->assertStringNotContainsString($token, $contents, "Unexpected blended color token in {$file->getRelativePathname()}.");
            }

            $this->assertDoesNotMatchRegularExpression(
                '/(?<!motion-safe:)animate-[a-z0-9-]+/',
                $contents,
                "Animation must be motion-safe in {$file->getRelativePathname()}.",
            );
        }
    }

    public function test_phase_14_manual_qa_checklist_covers_required_flows(): void
    {
        $checklist = File::get(base_path('docs/qa/phase-14-manual-checklist.md'));

        foreach ([
            'Public pages render on mobile and desktop',
            'Course catalogue filters work',
            'Product publish/hide works',
            'Checkout is clear and calm',
            'Promo code works',
            'Email-list discount works',
            'Paystack test payment',
            'Payment webhook is idempotent',
            'Moodle enrollment succeeds',
            'Failed Moodle enrollment is retryable',
            'ZeptoMail sends transactional email',
            'SES fallback is configured',
            'WhatsApp critical alert path is logged',
            'Discourse SSO works',
            'Role boundaries are enforced',
            'Reduced motion is respected',
        ] as $requiredItem) {
            $this->assertStringContainsString($requiredItem, $checklist);
        }
    }

    public function test_local_frontend_images_have_webp_variants(): void
    {
        $missing = [];

        foreach (File::allFiles(resource_path('js')) as $file) {
            $contents = File::get($file->getPathname());

            if (! preg_match_all('#/images/[\w \-.]+\.(?:jpe?g|png)#i', $contents, $matches)) {
                continue;
            }

            foreach (array_unique($matches[0]) as $reference) {
                $webpReference = preg_replace('/\.(jpe?g|png)$/i', '.webp', $reference);
                $webpPath = public_path(ltrim($webpReference, '/'));

                if (! File::exists($webpPath)) {
                    $missing[] = $reference.' (referenced in '.$file->getRelativePathname().')';
                }
            }
        }

        $this->assertSame(
            [],
            $missing,
            "Every local /images/*.{jpg,png} referenced in resources/js must ship a WebP sibling ".
            "(run: cwebp -q 82 <file> -o <file>.webp). Missing:\n".implode("\n", $missing),
        );
    }

    private function publishedProductWithPrice(float $amount): Product
    {
        $track = Track::factory()->create(['status' => 'published']);
        $level = CourseLevel::factory()->create(['track_id' => $track->id]);
        $product = Product::factory()->published()->create([
            'track_id' => $track->id,
            'course_level_id' => $level->id,
            'cohort_id' => null,
        ]);

        ProductPrice::factory()->create([
            'product_id' => $product->id,
            'amount' => $amount,
            'is_default' => true,
            'is_active' => true,
        ]);

        return $product->refresh();
    }
}
