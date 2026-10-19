<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * ライセンスの座席1つを、利用者(メールアドレス)に割り当てた記録
 *
 * @property int $id
 * @property int $license_id
 * @property string $user_email
 * @property Carbon $assigned_at
 * @property Carbon|null $released_at
 * @property-read License $license
 */
class LicenseAssignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'license_id',
        'user_email',
        'assigned_at',
        'released_at',
    ];

    protected function casts(): array
    {
        return [
            'assigned_at' => 'datetime',
            'released_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<License, $this> */
    public function license(): BelongsTo
    {
        return $this->belongsTo(License::class);
    }

    public function isReleased(): bool
    {
        return $this->released_at !== null;
    }
}
