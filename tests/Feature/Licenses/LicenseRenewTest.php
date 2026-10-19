<?php

namespace Tests\Feature\Licenses;

use App\Models\License;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LicenseRenewTest extends TestCase
{
    use RefreshDatabase;

    public function test_renewing_an_expired_license_extends_from_today_and_reactivates(): void
    {
        $user = User::factory()->create();
        $license = License::factory()->expired()->expiresOn(today()->subDays(40)->toDateString())->create();

        $this->actingAs($user)
            ->postJson(route('licenses.renew', $license), ['months' => 12])
            ->assertOk()
            ->assertJsonPath('data.status', 'active')
            ->assertJsonPath('data.expires_on', today()->addMonths(12)->toDateString());
    }

    public function test_months_must_be_a_positive_integer(): void
    {
        $user = User::factory()->create();
        $license = License::factory()->create();

        $this->actingAs($user)
            ->postJson(route('licenses.renew', $license), ['months' => 0])
            ->assertStatus(422)
            ->assertJsonValidationErrors('months');
    }

    public function test_cancelled_license_cannot_be_renewed(): void
    {
        $user = User::factory()->create();
        $license = License::factory()->cancelled()->create();

        $this->actingAs($user)
            ->postJson(route('licenses.renew', $license), ['months' => 12])
            ->assertStatus(422);
    }
}
