<?php

namespace Tests\Feature;

use App\Models\Content\Post;
use App\Models\Content\PostCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BlogIndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_blog_shows_empty_state_when_no_posts(): void
    {
        $this->get('/blog')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Public/Blog/Index')
                ->where('posts', [])
                ->where('featuredPost', null)
                ->where('pagination.total', 0));
    }

    public function test_blog_lists_published_posts_with_featured_and_reading_time(): void
    {
        $category = PostCategory::factory()->create(['name' => 'Career', 'slug' => 'career']);
        Post::factory()->count(3)->create([
            'post_category_id' => $category->id,
            'status' => 'published',
            'published_at' => now()->subDay(),
        ]);

        $this->get('/blog')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Public/Blog/Index')
                ->has('featuredPost')
                ->has('posts', 2) // 3 total minus the featured one
                ->has('categories', 1)
                ->where('pagination.total', 3)
                ->has('posts.0.readingMinutes')
                ->has('seo.canonical'));
    }

    public function test_blog_search_filters_posts(): void
    {
        $category = PostCategory::factory()->create();
        Post::factory()->create(['title' => 'Laravel Testing Guide', 'post_category_id' => $category->id, 'status' => 'published', 'published_at' => now()->subDay()]);
        Post::factory()->create(['title' => 'React Hooks Deep Dive', 'post_category_id' => $category->id, 'status' => 'published', 'published_at' => now()->subDay()]);

        $this->get('/blog?search=Laravel')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->where('pagination.total', 1)
                ->where('featuredPost', null)
                ->where('filters.search', 'Laravel'));
    }

    public function test_blog_filters_by_category(): void
    {
        $ai = PostCategory::factory()->create(['slug' => 'ai']);
        $career = PostCategory::factory()->create(['slug' => 'career']);
        Post::factory()->count(2)->create(['post_category_id' => $ai->id, 'status' => 'published', 'published_at' => now()->subDay()]);
        Post::factory()->create(['post_category_id' => $career->id, 'status' => 'published', 'published_at' => now()->subDay()]);

        $this->get('/blog?category=ai')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->where('pagination.total', 2)
                ->where('featuredPost', null));
    }

    public function test_blog_post_page_renders_with_schema(): void
    {
        $category = PostCategory::factory()->create();
        Post::factory()->create([
            'post_category_id' => $category->id,
            'slug' => 'my-first-post',
            'status' => 'published',
            'published_at' => now()->subDay(),
        ]);

        $this->get('/blog/my-first-post')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Public/Blog/Show')
                ->where('post.slug', 'my-first-post')
                ->has('post.readingMinutes')
                ->has('structuredData'));
    }
}
