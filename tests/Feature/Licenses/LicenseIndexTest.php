<?php

namespace Tests\Feature\Licenses;

use App\Models\License;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LicenseIndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_login(): void
    {
        $this->get(route('licenses.index'))->assertRedirect(route('login'));
    }

    public function test_index_lists_licenses_ordered_by_expiry(): void
    {
        $user = User::factory()->create();
        License::factory()->expiresOn(today()->addDays(90)->toDateString())->create(['customer_name' => '後の会社']);
        License::factory()->expiresOn(today()->addDays(30)->toDateString())->create(['customer_name' => '先の会社']);

        $response = $this->actingAs($user)->getJson(route('licenses.index'));

        $response->assertOk()
            ->assertJsonPath('data.0.customer_name', '先の会社')
            ->assertJsonPath('data.1.customer_name', '後の会社');
    }

    public function test_expiring_within_filter_keeps_near_and_drops_far(): void
    {
        $user = User::factory()->create();
        License::factory()->expiresOn(today()->addDays(10)->toDateString())->create(['customer_name' => '10日後']);
        License::factory()->expiresOn(today()->addDays(60)->toDateString())->create(['customer_name' => '60日後']);

        $response = $this->actingAs($user)->getJson(route('licenses.index', ['expiring_within' => 30]));

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.customer_name', '10日後');
    }

    public function test_index_page_renders_html(): void
    {
        $user = User::factory()->create();
        License::factory()->create(['customer_name' => '画面表示テスト']);

        $this->actingAs($user)->get(route('licenses.index'))
            ->assertOk()
            ->assertSee('画面表示テスト');
    }
}
