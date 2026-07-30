<?php

namespace Tests\Feature;

use App\Enums\DiscountRedemptionStatus;
use App\Enums\DiscountRuleStatus;
use App\Enums\DiscountType;
use App\Models\Catalog\CourseLevel;
use App\Models\Catalog\DiscountCode;
use App\Models\Catalog\DiscountEligibleEmail;
use App\Models\Catalog\DiscountEligibilityList;
use App\Models\Catalog\DiscountRedemption;
use App\Models\Catalog\DiscountRule;
use App\Models\Catalog\Order;
use App\Models\Catalog\Product;
use App\Models\Catalog\ProductPrice;
use App\Models\Catalog\Track;
use App\Services\Discounts\DiscountEligibilityImporter;
use App\Services\Discounts\DiscountEligibilityService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class DiscountEligibilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_expired_discounts_fail_checkout_validation(): void
    {
        $product = $this->productWithPrice();
        $rule = DiscountRule::factory()->expired()->create([
            'product_id' => $product->id,
            'type' => DiscountType::Percentage,
            'value' => 20,
        ]);
        DiscountCode::factory()->create([
            'discount_rule_id' => $rule->id,
            'code' => 'EXPIRED20',
        ]);

        $result = app(DiscountEligibilityService::class)->validate(
            'learner@example.com',
            $product,
            200000,
            'EXPIRED20',
        );

        $this->assertFalse($result->valid);
        $this->assertSame('Discount is not active for the current date.', $result->reason);
    }

    public function test_email_list_discounts_reject_ineligible_emails_and_accept_eligible_emails(): void
    {
        $product = $this->productWithPrice();
        $rule = DiscountRule::factory()->active()->create([
            'product_id' => $product->id,
            'requires_email_eligibility' => true,
            'type' => DiscountType::FixedAmount,
            'value' => 50000,
        ]);
        DiscountCode::factory()->create([
            'discount_rule_id' => $rule->id,
            'code' => 'COHORT50',
        ]);
        $list = DiscountEligibilityList::factory()->create(['discount_rule_id' => $rule->id]);
        DiscountEligibleEmail::factory()->create([
            'discount_eligibility_list_id' => $list->id,
            'email' => 'Eligible@Example.com',
        ]);

        $service = app(DiscountEligibilityService::class);

        $ineligible = $service->validate('outside@example.com', $product, 200000, 'cohort50');
        $eligible = $service->validate('eligible@example.com', $product, 200000, 'cohort50');

        $this->assertFalse($ineligible->valid);
        $this->assertSame('This email is not eligible for this discount.', $ineligible->reason);
        $this->assertTrue($eligible->valid);
        $this->assertSame(50000.0, $eligible->discountAmount);
        $this->assertSame(150000.0, $eligible->totalAfterDiscount);
    }

    public function test_discount_is_locked_on_order_before_payment_initialization(): void
    {
        $product = $this->productWithPrice();
        $order = Order::factory()->create([
            'subtotal' => 200000,
            'total' => 200000,
            'balance_due' => 200000,
        ]);
        $rule = DiscountRule::factory()->active()->create([
            'product_id' => $product->id,
            'type' => DiscountType::Percentage,
            'value' => 10,
        ]);
        $code = DiscountCode::factory()->create([
            'discount_rule_id' => $rule->id,
            'code' => 'PAYLOCK10',
        ]);

        $redemption = app(DiscountEligibilityService::class)->lockForCheckout(
            $order,
            $product,
            'learner@example.com',
            200000,
            'paylock10',
        );

        $order->refresh();

        $this->assertSame(DiscountRedemptionStatus::Locked, $redemption->status);
        $this->assertSame('20000.00', $order->discount_total);
        $this->assertSame('180000.00', $order->total);
        $this->assertSame($redemption->uuid, $order->metadata['discount']['redemption_uuid']);

        app(DiscountEligibilityService::class)->markRedeemed($redemption->fresh());

        $this->assertSame(1, $code->fresh()->redeemed_count);
        $this->assertSame(DiscountRedemptionStatus::Redeemed, $redemption->fresh()->status);
    }

    public function test_email_import_normalizes_emails_and_reports_duplicates(): void
    {
        $rule = DiscountRule::factory()->active()->create([
            'requires_email_eligibility' => true,
        ]);
        $list = DiscountEligibilityList::factory()->create(['discount_rule_id' => $rule->id]);
        $path = storage_path('framework/testing/discount-emails.csv');

        File::ensureDirectoryExists(dirname($path));
        file_put_contents($path, implode(PHP_EOL, [
            'email,name',
            'STUDENT@example.com,Student One',
            'student@example.com,Duplicate Student',
            'other@example.com,Other Student',
            'not-an-email,Bad Row',
        ]));

        $result = app(DiscountEligibilityImporter::class)->import($list, $path, 'discount-emails.csv');

        $this->assertSame(2, $result['imported']);
        $this->assertSame(1, $result['duplicates']);
        $this->assertSame(1, $result['invalid']);
        $this->assertDatabaseHas('discount_eligible_emails', [
            'discount_eligibility_list_id' => $list->id,
            'email' => 'student@example.com',
            'normalized_email' => 'student@example.com',
        ]);
        $this->assertSame(2, $list->fresh()->total_emails);
    }

    public function test_checkout_discount_endpoint_validates_discount_without_locking(): void
    {
        $product = $this->productWithPrice();
        $rule = DiscountRule::factory()->active()->create([
            'product_id' => $product->id,
            'type' => DiscountType::Percentage,
            'value' => 25,
        ]);
        DiscountCode::factory()->create([
            'discount_rule_id' => $rule->id,
            'code' => 'SAVE25',
        ]);

        $this->postJson(route('checkout.discount.validate'), [
            'email' => 'buyer@example.com',
            'product_id' => $product->id,
            'subtotal' => 200000,
            'code' => 'save25',
        ])
            ->assertOk()
            ->assertJsonPath('valid', true)
            ->assertJsonPath('discount_amount', 50000)
            ->assertJsonPath('total_after_discount', 150000);

        $this->assertSame(0, DiscountRedemption::count());
    }

    private function productWithPrice(): Product
    {
        $track = Track::factory()->create();
        $level = CourseLevel::factory()->create(['track_id' => $track->id]);
        $product = Product::factory()->published()->create([
            'track_id' => $track->id,
            'course_level_id' => $level->id,
            'cohort_id' => null,
        ]);

        ProductPrice::factory()->create([
            'product_id' => $product->id,
            'amount' => 200000,
            'is_default' => true,
            'is_active' => true,
        ]);

        return $product;
    }
}
