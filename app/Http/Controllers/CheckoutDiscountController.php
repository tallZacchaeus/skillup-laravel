<?php

namespace App\Http\Controllers;

use App\Models\Catalog\Order;
use App\Models\Catalog\Product;
use App\Services\Discounts\DiscountEligibilityService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CheckoutDiscountController extends Controller
{
    public function validateDiscount(Request $request, DiscountEligibilityService $discounts): JsonResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'subtotal' => ['required', 'numeric', 'min:0'],
            'code' => ['nullable', 'string', 'max:255'],
            'uses_installments' => ['sometimes', 'boolean'],
            'lock' => ['sometimes', 'boolean'],
            'order_id' => ['required_if:lock,true', 'nullable', 'integer', 'exists:orders,id'],
        ]);

        $product = Product::query()->with(['track', 'level', 'cohort'])->findOrFail($data['product_id']);
        $user = $request->user();

        if ($request->boolean('lock')) {
            $order = Order::query()->findOrFail($data['order_id']);
            $redemption = $discounts->lockForCheckout(
                $order,
                $product,
                $data['email'],
                (float) $data['subtotal'],
                $data['code'] ?? null,
                $user,
                $request->boolean('uses_installments'),
            );

            return response()->json([
                'valid' => true,
                'locked' => true,
                'redemption_id' => $redemption->id,
                'snapshot' => $redemption->snapshot,
            ]);
        }

        $result = $discounts->validate(
            $data['email'],
            $product,
            (float) $data['subtotal'],
            $data['code'] ?? null,
            $user,
            $request->boolean('uses_installments'),
        );

        return response()->json([
            'valid' => $result->valid,
            'reason' => $result->reason,
            'discount_amount' => $result->discountAmount,
            'total_after_discount' => $result->totalAfterDiscount,
            'snapshot' => $result->snapshot,
        ], $result->valid ? 200 : 422);
    }
}
