<?php

namespace Tests\Feature\Licenses;

use App\Models\License;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LicenseAssignTest extends TestCase
{
    use RefreshDatabase;

    public function test_assigning_a_seat_increments_seats_used(): void
    {
        $user = User::factory()->create();
        $license = License::factory()->create(['seats' => 5, 'seats_used' => 0]);

        $this->actingAs($user)
            ->postJson(route('licenses.assign', $license), ['user_email' => 'sato@example.com'])
            ->assertCreated()
            ->assertJsonPath('data.user_email', 'sato@example.com');

        $this->assertSame(1, $license->refresh()->seats_used);
        $this->assertDatabaseHas('license_assignments', ['license_id' => $license->id, 'user_email' => 'sato@example.com', 'released_at' => null]);
    }

    public function test_same_email_cannot_be_assigned_twice(): void
    {
        $user = User::factory()->create();
        $license = License::factory()->create(['seats' => 5]);

        $this->actingAs($user)->postJson(route('licenses.assign', $license), ['user_email' => 'sato@example.com'])->assertCreated();
        $this->actingAs($user)->postJson(route('licenses.assign', $license), ['user_email' => 'sato@example.com'])->assertStatus(422);

        $this->assertSame(1, $license->refresh()->seats_used);
    }

    public function test_expired_license_rejects_assignment(): void
    {
        $user = User::factory()->create();
        $license = License::factory()->expired()->expiresOn(today()->subDay()->toDateString())->create(['seats' => 5]);

        $this->actingAs($user)
            ->postJson(route('licenses.assign', $license), ['user_email' => 'sato@example.com'])
            ->assertStatus(422);
    }
}
