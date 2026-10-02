<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserCountryTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test country name and flag accessors on User model.
     */
    public function test_user_country_and_flag_attributes(): void
    {
        $userPR = new User(['market' => 'PRI']);
        $this->assertSame('Puerto Rico', $userPR->country_name);
        $this->assertStringContainsString('<svg', $userPR->country_flag_svg);
        $this->assertSame('🇵🇷', $userPR->country_flag_emoji);

        $userPE = new User(['market' => 'PE']);
        $this->assertSame('Perú', $userPE->country_name);
        $this->assertStringContainsString('#D91023', $userPE->country_flag_svg);
        $this->assertSame('🇵🇪', $userPE->country_flag_emoji);

        $userARG = new User(['market' => 'ARG']);
        $this->assertSame('Argentina', $userARG->country_name);
        $this->assertSame('🇦🇷', $userARG->country_flag_emoji);

        $userCOL = new User(['market' => 'COL']);
        $this->assertSame('Colombia', $userCOL->country_name);
        $this->assertSame('🇨🇴', $userCOL->country_flag_emoji);

        $userCHL = new User(['market' => 'CHL']);
        $this->assertSame('Chile', $userCHL->country_name);
        $this->assertSame('🇨🇱', $userCHL->country_flag_emoji);

        $userECU = new User(['market' => 'ECU']);
        $this->assertSame('Ecuador', $userECU->country_name);
        $this->assertSame('🇪🇨', $userECU->country_flag_emoji);

        $userMEX = new User(['market' => 'MEX']);
        $this->assertSame('México', $userMEX->country_name);
        $this->assertSame('🇲🇽', $userMEX->country_flag_emoji);

        $userMIA = new User(['market' => 'MIA']);
        $this->assertSame('Miami', $userMIA->country_name);
        $this->assertSame('🇺🇸', $userMIA->country_flag_emoji);

        $userNoMarket = new User(['market' => null]);
        $this->assertNull($userNoMarket->country_name);
    }

    /**
     * Test header displays assigned country with flag icon on brandlift view.
     */
    public function test_brandlift_header_shows_assigned_country_and_flag(): void
    {
        $user = User::factory()->create([
            'name' => 'Camilo Serrato',
            'email' => 'camilo.serrato@wppmedia.com',
            'role' => 'mercado',
            'market' => 'PRI',
        ]);

        $response = $this->actingAs($user)->get('/brandlift');

        $response->assertStatus(200);
        $response->assertSee('Camilo Serrato');
        $response->assertSee('Puerto Rico');
        $response->assertSee('top-header-user-country', false);
        $response->assertSee('top-header-user-flag', false);
    }

    /**
     * Test header displays assigned country with flag icon on dashboard view.
     */
    public function test_dashboard_header_shows_assigned_country_and_flag(): void
    {
        $user = User::factory()->create([
            'name' => 'Camilo Serrato',
            'email' => 'camilo.serrato@wppmedia.com',
            'role' => 'mercado',
            'market' => 'PRI',
        ]);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertSee('Camilo Serrato');
        $response->assertSee('Puerto Rico');
        $response->assertSee('top-header-user-country', false);
        $response->assertSee('top-header-user-flag', false);
    }

    /**
     * Test header falls back to email when non-admin user has no market assigned.
     */
    public function test_header_shows_email_when_no_market_assigned(): void
    {
        $user = User::factory()->create([
            'name' => 'Designer User',
            'email' => 'designer@wppmedia.com',
            'role' => 'diseñador',
            'market' => null,
            'markets' => null,
        ]);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertSee('Designer User');
        $response->assertSee('designer@wppmedia.com');
        $response->assertDontSee('top-header-user-country', false);
    }

    /**
     * Test header shows market switcher without Global (Admin) text when user is admin.
     */
    public function test_header_shows_market_switcher_for_admin_user(): void
    {
        $user = User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@wppmedia.com',
            'role' => 'admin',
            'market' => null,
            'markets' => null,
        ]);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertSee('Admin User');
        $response->assertSee('top-header-market-switcher', false);
        $response->assertSee('Todos los mercados');
        $response->assertDontSee('Global (Admin)');
        $response->assertDontSee('top-header-user-country', false);
    }
}
