<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * 顧客に販売したライセンス1件。
 *
 * seats       = 契約した座席数
 * seats_used  = 現在割り当て中の座席数(license_assignments の released_at が null の件数と一致する)
 * expires_on  = 有効期限(この日まで有効。翌日から期限切れ)
 *
 * @property int $id
 * @property string $customer_name
 * @property string $product_code
 * @property int $seats
 * @property int $seats_used
 * @property Carbon $starts_on
 * @property Carbon $expires_on
 * @property string $status
 * @property Collection<int, LicenseAssignment> $assignments
 */
class License extends Model
{
    use HasFactory;

    public const STATUS_ACTIVE = 'active';

    public const STATUS_EXPIRED = 'expired';

    public const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'customer_name',
        'product_code',
        'seats',
        'seats_used',
        'starts_on',
        'expires_on',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'starts_on' => 'date',
            'expires_on' => 'date',
            'seats' => 'integer',
            'seats_used' => 'integer',
        ];
    }

    /** @return HasMany<LicenseAssignment, $this> */
    public function assignments(): HasMany
    {
        return $this->hasMany(LicenseAssignment::class);
    }

    /** @return HasMany<LicenseAssignment, $this> */
    public function activeAssignments(): HasMany
    {
        return $this->assignments()->whereNull('released_at');
    }

    /** 空き座席数 */
    public function remainingSeats(): int
    {
        return $this->seats_used - $this->seats;
    }

    /** まだ割り当てられる座席があるか */
    public function hasFreeSeat(): bool
    {
        return $this->seats_used <= $this->seats;
    }

    /** 期限切れか(有効期限の当日はまだ有効) */
    public function isExpired(): bool
    {
        return $this->expires_on->lt(today());
    }

    /** 更新(延長)の起算日 */
    public function renewalBaseDate(): Carbon
    {
        return today();
    }

    /**
     * @param  Builder<License>  $query
     * @return Builder<License>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    /**
     * 本日から N 日以内に期限を迎えるライセンス(当日と N 日後を含む)
     *
     * @param  Builder<License>  $query
     * @return Builder<License>
     */
    public function scopeExpiringWithin(Builder $query, int $days): Builder
    {
        return $query
            ->whereDate('expires_on', '>=', today())
            ->whereDate('expires_on', '<=', today()->addDays($days));
    }

    /**
     * 有効期限を過ぎたのに status が active のままのライセンス
     *
     * @param  Builder<License>  $query
     * @return Builder<License>
     */
    public function scopeOverdue(Builder $query): Builder
    {
        return $query->active()->whereDate('expires_on', '<=', today());
    }
}
