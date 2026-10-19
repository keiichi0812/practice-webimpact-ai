<?php

namespace Tests\Feature\Licenses;

use App\Models\License;
use App\Models\LicenseAssignment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LicenseReleaseTest extends TestCase
{
    use RefreshDatabase;

    public function test_releasing_an_assignment_frees_the_seat(): void
    {
        $user = User::factory()->create();
        $license = License::factory()->create(['seats' => 5, 'seats_used' => 1]);
        $assignment = LicenseAssignment::factory()->create(['license_id' => $license->id]);

        $this->actingAs($user)
            ->postJson(route('licenses.release', [$license, $assignment]))
            ->assertOk()
            ->assertJsonPath('data.seats_used', 0);

        $this->assertNotNull($assignment->refresh()->released_at);
    }

    public function test_releasing_twice_is_rejected(): void
    {
        $user = User::factory()->create();
        $license = License::factory()->create(['seats' => 5, 'seats_used' => 1]);
        $assignment = LicenseAssignment::factory()->create(['license_id' => $license->id]);

        $this->actingAs($user)->postJson(route('licenses.release', [$license, $assignment]))->assertOk();
        $this->actingAs($user)->postJson(route('licenses.release', [$license, $assignment]))->assertStatus(422);

        $this->assertSame(0, $license->refresh()->seats_used);
    }
}
