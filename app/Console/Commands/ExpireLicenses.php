<?php

namespace App\Console\Commands;

use App\Services\LicenseService;
use Illuminate\Console\Command;

/** 毎日実行し、有効期限を過ぎたライセンスを期限切れにする */
class ExpireLicenses extends Command
{
    protected $signature = 'licenses:expire';

    protected $description = '有効期限を過ぎたライセンスの status を expired にする';

    public function handle(LicenseService $service): int
    {
        $count = $service->expireOverdue();

        $this->info("{$count} 件のライセンスを期限切れにしました。");

        return self::SUCCESS;
    }
}
