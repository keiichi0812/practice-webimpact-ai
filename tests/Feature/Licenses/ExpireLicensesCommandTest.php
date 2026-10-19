<?php

namespace Tests\Feature\Licenses;

use App\Models\License;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExpireLicensesCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_overdue_licenses_become_expired_and_future_ones_stay_active(): void
    {
        $overdue = License::factory()->expiresOn(today()->subDay()->toDateString())->create();
        $future = License::factory()->expiresOn(today()->addDays(10)->toDateString())->create();

        $this->artisan('licenses:expire')->assertSuccessful();

        $this->assertSame(License::STATUS_EXPIRED, $overdue->refresh()->status);
        $this->assertSame(License::STATUS_ACTIVE, $future->refresh()->status);
    }

    public function test_cancelled_licenses_are_left_alone(): void
    {
        $cancelled = License::factory()->cancelled()->expiresOn(today()->subDays(30)->toDateString())->create();

        $this->artisan('licenses:expire')->assertSuccessful();

        $this->assertSame(License::STATUS_CANCELLED, $cancelled->refresh()->status);
    }
}
