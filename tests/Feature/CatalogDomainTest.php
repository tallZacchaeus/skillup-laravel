<?php

namespace Tests\Feature;

use App\Enums\CohortStatus;
use App\Enums\EnrollmentStatus;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Enums\ProductStatus;
use App\Models\Catalog\Cohort;
use App\Models\Catalog\CourseLevel;
use App\Models\Catalog\Enrollment;
use App\Models\Catalog\Order;
use App\Models\Catalog\Product;
use App\Models\Catalog\ProductMedia;
use App\Models\Catalog\ProductMoodleMapping;
use App\Models\Catalog\ProductPaymentPlan;
use App\Models\Catalog\ProductPrice;
use App\Models\Catalog\Track;
use Database\Seeders\ProductCatalogueSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CatalogDomainTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_represents_track_level_cohort_prices_payment_plans_media_and_moodle_mapping(): void
    {
        $track = Track::factory()->create(['status' => ProductStatus::Published]);
        $level = CourseLevel::factory()->create([
            'track_id' => $track->id,
            'slug' => 'basic',
            'rank' => 1,
        ]);
        $cohort = Cohort::factory()->create([
            'track_id' => $track->id,
            'course_level_id' => $level->id,
            'status' => CohortStatus::Open,
        ]);
        $product = Product::factory()->published()->create([
            'track_id' => $track->id,
            'course_level_id' => $level->id,
            'cohort_id' => $cohort->id,
            'title' => 'Product Management Basic',
            'slug' => 'product-management-basic',
        ]);

        ProductPrice::factory()->create([
            'product_id' => $product->id,
            'amount' => 200000,
            'is_default' => true,
        ]);
        ProductPaymentPlan::factory()->create(['product_id' => $product->id]);
        ProductMedia::factory()->create(['product_id' => $product->id]);
        ProductMoodleMapping::factory()->create([
            'product_id' => $product->id,
            'moodle_course_id' => 'moodle-product-management-basic',
        ]);

        $loaded = Product::query()
            ->with(['track', 'level', 'cohort', 'defaultPrice', 'paymentPlans', 'media', 'primaryMoodleMapping'])
            ->findOrFail($product->id);

        $this->assertSame('Product Management Basic', $loaded->title);
        $this->assertTrue($loaded->track->is($track));
        $this->assertTrue($loaded->level->is($level));
        $this->assertTrue($loaded->cohort->is($cohort));
        $this->assertSame('200000.00', $loaded->defaultPrice->amount);
        $this->assertCount(1, $loaded->paymentPlans);
        $this->assertCount(1, $loaded->media);
        $this->assertSame('moodle-product-management-basic', $loaded->primaryMoodleMapping->moodle_course_id);
    }

    public function test_published_scope_only_returns_publicly_available_products(): void
    {
        $published = Product::factory()->published()->create(['slug' => 'published-product']);
        $draft = Product::factory()->create(['slug' => 'draft-product']);
        $hidden = Product::factory()->hidden()->create(['slug' => 'hidden-product']);
        $future = Product::factory()->create([
            'slug' => 'future-product',
            'status' => ProductStatus::Published,
            'published_at' => now()->addDay(),
        ]);

        $ids = Product::published()->pluck('id')->all();

        $this->assertContains($published->id, $ids);
        $this->assertNotContains($draft->id, $ids);
        $this->assertNotContains($hidden->id, $ids);
        $this->assertNotContains($future->id, $ids);
    }

    public function test_product_publication_requires_active_default_price(): void
    {
        $product = Product::factory()->create([
            'status' => ProductStatus::Draft,
            'published_at' => null,
        ]);

        $this->assertContains('active default price', $product->missingPublicationFields());
        $this->assertFalse($product->publish());

        ProductPrice::factory()->create([
            'product_id' => $product->id,
            'is_default' => true,
            'is_active' => true,
            'amount' => 200000,
        ]);

        $this->assertTrue($product->fresh()->publish());
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'status' => ProductStatus::Published->value,
        ]);
    }

    public function test_status_fields_cast_to_enums(): void
    {
        $order = Order::factory()->paid()->create();
        $enrollment = Enrollment::factory()->active()->create();

        $this->assertSame(OrderStatus::Paid, $order->status);
        $this->assertSame(PaymentStatus::Paid, $order->payment_status);
        $this->assertSame(EnrollmentStatus::Active, $enrollment->status);
    }

    public function test_catalogue_seeder_creates_mvp_tracks_and_published_products(): void
    {
        $this->seed(ProductCatalogueSeeder::class);

        foreach ([
            'product-management',
            'software-development',
            'product-design',
            'virtual-assistance',
            'data-analysis',
            'digital-marketing',
            'cybersecurity',
        ] as $slug) {
            $this->assertDatabaseHas('tracks', ['slug' => $slug]);
        }

        $this->assertSame(7, Track::count());
        $this->assertSame(21, CourseLevel::count());
        $this->assertSame(5, Product::published()->count());

        Product::published()->each(function (Product $product) {
            $this->assertNotNull($product->defaultPrice()->first());
            $this->assertNotNull($product->primaryMoodleMapping()->first());
            $this->assertGreaterThan(0, $product->paymentPlans()->count());
        });
    }
}
