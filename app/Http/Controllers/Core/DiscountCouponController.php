<?php

namespace App\Http\Controllers\Core;

use App\Http\Controllers\Controller;
use App\Http\Requests\Core\StoreDiscountCouponRequest;
use App\Http\Requests\Core\UpdateDiscountCouponRequest;
use App\Models\DiscountCoupon;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class DiscountCouponController extends Controller
{
    public function index(): Response
    {
        $coupons = DiscountCoupon::orderByDesc('created_at')->get();

        return Inertia::render('Core/DiscountCoupons/Index', [
            'coupons' => $coupons,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Core/DiscountCoupons/Create', [
            'coupon' => null,
        ]);
    }

    public function store(StoreDiscountCouponRequest $request): RedirectResponse
    {
        DiscountCoupon::create($request->couponAttributes());

        return redirect()->route('core.discount-coupons.index')
            ->with('success', 'Coupon created.');
    }

    public function edit(DiscountCoupon $discountCoupon): Response
    {
        $c = $discountCoupon;

        $expirationType = 'never';
        $expirationDays = 30;
        $maxUsesInput = 100;

        if ($c->expires_at !== null && $c->max_uses === null) {
            $expirationType = 'days';
            $seconds = $c->expires_at->getTimestamp() - now()->getTimestamp();
            $expirationDays = max(1, (int) ceil($seconds / 86400));
        } elseif ($c->max_uses !== null) {
            $expirationType = 'uses';
            $maxUsesInput = $c->max_uses;
        }

        return Inertia::render('Core/DiscountCoupons/Edit', [
            'coupon' => [
                'id' => $c->id,
                'code' => $c->code,
                'value' => $c->value,
                'expiration_type' => $expirationType,
                'expiration_days' => $expirationDays,
                'max_uses_input' => $maxUsesInput,
            ],
        ]);
    }

    public function update(UpdateDiscountCouponRequest $request, DiscountCoupon $discountCoupon): RedirectResponse
    {
        $attrs = $request->couponAttributes();
        unset($attrs['times_used']);

        $discountCoupon->update($attrs);

        return redirect()->route('core.discount-coupons.index')
            ->with('success', 'Coupon updated.');
    }

    public function destroy(DiscountCoupon $discountCoupon): RedirectResponse
    {
        $discountCoupon->delete();

        return redirect()->route('core.discount-coupons.index')
            ->with('success', 'Coupon removed.');
    }
}
