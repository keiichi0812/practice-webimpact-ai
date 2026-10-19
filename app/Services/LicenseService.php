<?php

namespace App\Services;

use App\Exceptions\LicenseException;
use App\Models\License;
use App\Models\LicenseAssignment;
use Illuminate\Support\Facades\DB;

class LicenseService
{
    /** 有効期限を months か月延長する。期限切れのライセンスは有効に戻す */
    public function renew(License $license, int $months): License
    {
        if ($license->status === License::STATUS_CANCELLED) {
            throw new LicenseException('解約済みのライセンスは更新できません。');
        }

        $license->expires_on = $license->renewalBaseDate()->addMonths($months);
        $license->status = License::STATUS_ACTIVE;
        $license->save();

        return $license;
    }

    /** 座席を1つ割り当てる */
    public function assignSeat(License $license, string $userEmail): LicenseAssignment
    {
        if ($license->status !== License::STATUS_ACTIVE || $license->isExpired()) {
            throw new LicenseException('有効なライセンスではないため割り当てできません。');
        }

        if (! $license->hasFreeSeat()) {
            throw new LicenseException('空き座席がありません。');
        }

        if ($license->activeAssignments()->where('user_email', $userEmail)->exists()) {
            throw new LicenseException('このメールアドレスには既に割り当て済みです。');
        }

        return DB::transaction(function () use ($license, $userEmail) {
            $assignment = $license->assignments()->create([
                'user_email' => $userEmail,
                'assigned_at' => now(),
            ]);
            $license->increment('seats_used');

            return $assignment;
        });
    }

    /** 割り当てを解除し、座席を空ける */
    public function releaseSeat(License $license, LicenseAssignment $assignment): LicenseAssignment
    {
        if ($assignment->isReleased()) {
            throw new LicenseException('この割り当ては既に解除されています。');
        }

        DB::transaction(function () use ($license, $assignment) {
            $assignment->update(['released_at' => now()]);
            $license->decrement('seats_used');
        });

        return $assignment->refresh();
    }

    /** 有効期限を過ぎたライセンスを期限切れにする。戻り値は件数 */
    public function expireOverdue(): int
    {
        return License::overdue()->update(['status' => License::STATUS_EXPIRED]);
    }
}
