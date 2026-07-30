<?php

namespace App\Http\Controllers;

use App\Models\Catalog\Product;
use App\Models\Catalog\ProductReview;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function store(Request $request, Product $product): RedirectResponse
    {
        $data = $request->validate([
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'title' => ['nullable', 'string', 'max:120'],
            'body' => ['required', 'string', 'min:10', 'max:2000'],
        ]);

        $user = $request->user();

        // Only learners enrolled in the course may review it (verified reviews).
        abort_unless(
            $user->enrollments()->where('product_id', $product->id)->exists(),
            403,
            'Only enrolled learners can review this course.',
        );

        ProductReview::updateOrCreate(
            ['user_id' => $user->id, 'product_id' => $product->id],
            [
                'reviewer_name' => $user->name,
                'reviewer_title' => 'Verified learner',
                'rating' => $data['rating'],
                'title' => $data['title'] ?? null,
                'body' => $data['body'],
                'is_verified' => true,
                'is_published' => true,
                'reviewed_at' => now(),
            ],
        );

        return back()->with('status', 'Thanks — your review has been posted.');
    }
}
