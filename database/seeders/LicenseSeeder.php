<?php

namespace Database\Seeders;

use App\Models\License;
use Illuminate\Database\Seeder;

/** 画面で動作を確かめるための見本データ(すべて架空) */
class LicenseSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['customer_name' => '株式会社サンプル商事', 'product_code' => 'WI-CORE', 'seats' => 10, 'starts_on' => today()->subMonths(9), 'expires_on' => today()->addMonths(3)],
            ['customer_name' => '有限会社テスト工業', 'product_code' => 'WI-PLUS', 'seats' => 5, 'starts_on' => today()->subMonths(11), 'expires_on' => today()->addDays(20)],
            ['customer_name' => 'ダミー物流株式会社', 'product_code' => 'WI-ENT', 'seats' => 30, 'starts_on' => today()->subYear(), 'expires_on' => today()],
            ['customer_name' => '架空システムズ株式会社', 'product_code' => 'WI-CORE', 'seats' => 3, 'starts_on' => today()->subMonths(13), 'expires_on' => today()->subDays(10)],
            ['customer_name' => '見本ホールディングス', 'product_code' => 'WI-PLUS', 'seats' => 8, 'starts_on' => today()->subMonths(6), 'expires_on' => today()->addDays(5), 'status' => License::STATUS_CANCELLED],
            ['customer_name' => 'サンプル製作所', 'product_code' => 'WI-ENT', 'seats' => 2, 'starts_on' => today()->subMonths(2), 'expires_on' => today()->addMonths(10)],
        ];

        foreach ($rows as $row) {
            $license = License::create($row + ['status' => License::STATUS_ACTIVE]);

            // 座席の割り当て見本: 1件目は2席、6件目は満席にしておく
            $emails = match ($license->customer_name) {
                '株式会社サンプル商事' => ['sato@example.com', 'suzuki@example.com'],
                'サンプル製作所' => ['tanaka@example.com', 'ito@example.com'],
                default => [],
            };

            foreach ($emails as $email) {
                $license->assignments()->create(['user_email' => $email, 'assigned_at' => now()]);
            }
            $license->update(['seats_used' => count($emails)]);
        }
    }
}
