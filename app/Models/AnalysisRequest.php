<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AnalysisRequest extends Model
{
    use HasFactory;
    use HasUuids;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'email',
        'tiktok_username',
        'bio',
        'aspiring_niche',
        'video_url_1',
        'video_url_2',
        'video_url_3',
        'notes',
        'locale',
        'stripe_checkout_session_id',
        'stripe_payment_intent_id',
        'payment_status',
        'processing_status',
        'attempt_count',
        'last_error',
        'report_html',
        'sent_at',
        'discount_coupon_id',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'attempt_count' => 'integer',
        'sent_at' => 'datetime',
    ];

    protected $keyType = 'string';

    public $incrementing = false;

    public function scopePaid(Builder $query): Builder
    {
        return $query->where('payment_status', 'paid');
    }

    public function scopePendingPayment(Builder $query): Builder
    {
        return $query->where('payment_status', 'pending');
    }

    public function scopeProcessingStatus(Builder $query, string $status): Builder
    {
        return $query->where('processing_status', $status);
    }

    public function discountCoupon(): BelongsTo
    {
        return $this->belongsTo(DiscountCoupon::class, 'discount_coupon_id');
    }
}
