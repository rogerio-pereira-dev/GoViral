<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class DiscountCoupon extends Model
{
    use HasFactory;
    use HasUuids;
    use SoftDeletes;

    protected $keyType = 'string';

    public $incrementing = false;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'code',
        'value',
        'expires_at',
        'max_uses',
        'times_used',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'expires_at' => 'date',
        'value' => 'integer',
        'max_uses' => 'integer',
        'times_used' => 'integer',
    ];

    public function analysisRequests(): HasMany
    {
        return $this->hasMany(AnalysisRequest::class, 'discount_coupon_id');
    }

    public function scopeValidForCheckout(Builder $query): Builder
    {
        $today = now()->toDateString();

        return $query
            ->where(function (Builder $q): void {
                $q->whereNull('expires_at')
                    ->orWhereDate('expires_at', '>=', now()->toDateString());
            })
            ->where(function (Builder $q): void {
                $q->whereNull('max_uses')
                    ->orWhereColumn('times_used', '<', 'max_uses');
            });
    }

    public static function discountedAmountCents(int $baseCents, int $percentOff): int
    {
        $discounted = (int) floor($baseCents * (100 - $percentOff) / 100);

        return max(50, $discounted);
    }

    public static function findValidByCode(string $code): ?self
    {
        $normalized = strtoupper(trim($code));

        if ($normalized === '') {
            return null;
        }

        return self::query()
            ->whereRaw('UPPER(code) = ?', [$normalized])
            ->validForCheckout()
            ->first();
    }

    protected static function booted(): void
    {
        static::saving(function (self $coupon): void {
            $coupon->code = strtoupper(trim($coupon->code));
        });
    }
}
