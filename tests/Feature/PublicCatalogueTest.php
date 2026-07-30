<?php

namespace Tests\Feature;

use App\Models\Catalog\Product;
use Database\Seeders\ProductCatalogueSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class PublicCatalogueTest extends TestCase
{
    use RefreshDatabase;

    public function test_courses_index_serves_published_catalogue_products(): void
    {
        $this->seed(ProductCatalogueSeeder::class);

        $this->get('/courses')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Public/Courses/Index')
                ->has('products', 5)
                ->where('products.0.slug', 'product-management-basic')
                ->where('products.0.price', 'NGN 200,000'));
    }

    public function test_track_page_serves_published_track_products(): void
    {
        $this->seed(ProductCatalogueSeeder::class);

        $this->get('/courses/product-management')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Public/Courses/Show')
                ->where('track.slug', 'product-management')
                ->has('track.products', 1)
                ->where('track.products.0.slug', 'product-management-basic')
                // Premium landing payload: related courses, SEO, and Course schema.
                ->has('related')
                ->has('seo.canonical')
                ->where('structuredData.0.@type', 'Course')
                ->where('structuredData.1.@type', 'BreadcrumbList'));
    }

    public function test_product_page_serves_course_detail_from_database(): void
    {
        $this->seed(ProductCatalogueSeeder::class);

        $this->get('/courses/product-management/product-management-basic')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Public/Courses/Product')
                ->where('product.slug', 'product-management-basic')
                ->where('product.trackSlug', 'product-management')
                ->where('product.price', 'NGN 200,000')
                ->has('product.paymentPlans', 1));
    }

    public function test_hidden_product_detail_returns_not_found(): void
    {
        $this->seed(ProductCatalogueSeeder::class);

        Product::where('slug', 'product-management-basic')->update([
            'status' => 'hidden',
        ]);

        $this->get('/courses/product-management/product-management-basic')
            ->assertNotFound();
    }
}
