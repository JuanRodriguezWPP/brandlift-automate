<?php

namespace Tests\Feature;

use App\Models\BrandliftStudy;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_reports_requires_authentication(): void
    {
        $response = $this->get(route('reports'));
        $response->assertRedirect('/login');
    }

    public function test_reports_scopes_to_user_market(): void
    {
        $user = User::factory()->create([
            'market' => 'PRI',
        ]);

        BrandliftStudy::create([
            'market' => 'PRI',
            'campaign_name' => '2026_10_MCS_PRI_Test_Campaign',
            'question_count' => 2,
            'client_name' => 'Client PRI',
            'status' => 'cm360_pushed',
            'cm360_pushed' => true,
            'end_date' => now()->addDays(5)->toDateString(),
        ]);

        BrandliftStudy::create([
            'market' => 'MEX',
            'campaign_name' => '2026_10_MCS_MEX_Other_Campaign',
            'question_count' => 3,
            'client_name' => 'Client MEX',
            'status' => 'cm360_pushed',
            'cm360_pushed' => true,
            'end_date' => now()->addDays(5)->toDateString(),
        ]);

        $response = $this->actingAs($user)->get(route('reports'));

        $response->assertOk();
        $response->assertSee('2026_10_MCS_PRI_Test_Campaign');
        $response->assertDontSee('2026_10_MCS_MEX_Other_Campaign');
    }

    public function test_reports_view_renders_expected_columns_and_no_inversion(): void
    {
        $user = User::factory()->create([
            'market' => 'PRI',
        ]);

        BrandliftStudy::create([
            'market' => 'PRI',
            'campaign_name' => '2026_10_MCS_PRI_Active_Campaign',
            'question_count' => 2,
            'client_name' => 'Test Client',
            'status' => 'cm360_pushed',
            'cm360_pushed' => true,
            'end_date' => now()->addDays(10)->toDateString(),
        ]);

        $response = $this->actingAs($user)->get(route('reports'));

        $response->assertOk();
        $response->assertSee('Fecha Fin');
        $response->assertSee('Estado');
        $response->assertDontSee('<th>Inversión</th>', false);
        $response->assertSee('Activa');
    }

    public function test_reports_view_renders_finalizada_for_past_campaign(): void
    {
        $user = User::factory()->create([
            'market' => 'PRI',
        ]);

        BrandliftStudy::create([
            'market' => 'PRI',
            'campaign_name' => '2026_09_MCS_PRI_Past_Campaign',
            'question_count' => 2,
            'client_name' => 'Past Client',
            'status' => 'cm360_pushed',
            'cm360_pushed' => true,
            'end_date' => now()->subDays(5)->toDateString(),
        ]);

        $response = $this->actingAs($user)->get(route('reports'));

        $response->assertOk();
        $response->assertSee('Finalizada');
    }

    public function test_reports_view_renders_campaigns(): void
    {
        $user = User::factory()->create([
            'market' => 'PRI',
        ]);

        BrandliftStudy::create([
            'market' => 'PRI',
            'campaign_name' => '2026_10_MCS_PRI_Kelloggs_Campaign',
            'question_count' => 2,
            'client_name' => "Kellogg's",
            'status' => 'cm360_pushed',
            'cm360_pushed' => true,
            'end_date' => now()->addDays(5)->toDateString(),
        ]);

        BrandliftStudy::create([
            'market' => 'PRI',
            'campaign_name' => '2026_10_MCS_PRI_RAC_Campaign',
            'question_count' => 2,
            'client_name' => 'RAC',
            'status' => 'cm360_pushed',
            'cm360_pushed' => true,
            'end_date' => now()->addDays(5)->toDateString(),
        ]);

        $response = $this->actingAs($user)->get(route('reports'));

        $response->assertOk();
        $response->assertSee('Campañas');
        $response->assertSee("Kellogg's");
        $response->assertSee('RAC');
    }
}
