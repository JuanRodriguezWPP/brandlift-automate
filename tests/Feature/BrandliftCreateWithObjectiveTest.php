<?php

namespace Tests\Feature;

use App\Models\BrandliftStudy;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BrandliftCreateWithObjectiveTest extends TestCase
{
    use RefreshDatabase;

    public function test_brandlift_form_view_contains_campaign_objective_input(): void
    {
        $user = User::factory()->create([
            'role' => 'admin',
            'markets' => ['COL'],
        ]);

        $response = $this->actingAs($user)->get('/brandlift');

        $response->assertOk();
        $response->assertSee('Objetivo de campaña');
        $response->assertSee('id="bl-campaign-objective-step1"', false);
    }

    public function test_brandlift_stores_campaign_objective_in_database(): void
    {
        $user = User::factory()->create([
            'role' => 'admin',
            'markets' => ['COL'],
        ]);

        $payload = [
            'market' => 'COL',
            'campaign_name' => 'Verano 2026',
            'campaign_objective' => 'Recordación de Marca y Awareness',
            'question_count' => 1,
            'creative_width' => 300,
            'creative_height' => 250,
            'client_name' => 'Coca Cola',
            'audiences' => ['Audiencia A'],
            'dps_tags' => ['DV360'],
            'end_date' => now()->addDays(15)->format('Y-m-d'),
            'investment' => '5000',
            'cm360_site_id' => '123456',
            'cm360_profile_id' => '78910',
            'cm360_advertiser_id' => '111213',
            'questions' => [
                [
                    'question_number' => 1,
                    'question_text' => '¿Recuerdas haber visto este anuncio?',
                    'answers' => ['Sí', 'No'],
                    'creative_html' => '<div>Test</div>',
                ],
            ],
        ];

        $response = $this->actingAs($user)->postJson('/api/brandlift/store', $payload);

        $response->assertOk();
        $response->assertJson(['success' => true]);

        $this->assertDatabaseHas('brandlift_studies', [
            'market' => 'COL',
            'client_name' => 'Coca Cola',
            'campaign_objective' => 'Recordación de Marca y Awareness',
        ]);
    }

    public function test_brandlift_form_in_edit_mode_populates_campaign_objective(): void
    {
        $user = User::factory()->create([
            'role' => 'admin',
            'markets' => ['COL'],
        ]);

        $study = BrandliftStudy::create([
            'market' => 'COL',
            'campaign_name' => '2026_10_WMSCSLATAM_COL_Nike_Run2026_brandlift_b',
            'campaign_objective' => 'Consideración de producto',
            'client_name' => 'Nike',
            'question_count' => 1,
            'creative_width' => 300,
            'creative_height' => 250,
        ]);

        $response = $this->actingAs($user)->get('/brandlift?edit_id=' . $study->liquid_id);

        $response->assertOk();
        $response->assertSee('value="Consideración de producto"', false);
    }

    public function test_brandlift_form_view_contains_reserved_audiences_warning(): void
    {
        $user = User::factory()->create([
            'role' => 'admin',
            'markets' => ['COL'],
        ]);

        $response = $this->actingAs($user)->get('/brandlift');

        $response->assertOk();
        $response->assertSee('id="groups-reserved-warning"', false);
        $response->assertSee('No es necesario agregar');
        $response->assertSee('spelling-notice', false);
        $response->assertSee('Revisa y valida la ortografía');
    }

    public function test_brandlift_rejects_reserved_audience_names(): void
    {
        $user = User::factory()->create([
            'role' => 'admin',
            'markets' => ['COL'],
        ]);

        $payload = [
            'market' => 'COL',
            'campaign_name' => 'Camp_Reserved_Test',
            'question_count' => 1,
            'creative_width' => 300,
            'creative_height' => 250,
            'client_name' => 'Brand Test',
            'audiences' => ['AdExposed', 'Mujeres 25-34'],
            'dps_tags' => ['DV360'],
            'end_date' => now()->addDays(10)->format('Y-m-d'),
            'investment' => '1000',
            'cm360_site_id' => '111',
            'cm360_profile_id' => '222',
            'cm360_advertiser_id' => '333',
            'questions' => [
                [
                    'question_number' => 1,
                    'question_text' => '¿Conoce la marca?',
                    'answers' => ['Sí', 'No'],
                ],
            ],
        ];

        $response = $this->actingAs($user)->postJson('/api/brandlift/store', $payload);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['audiences.0']);

        // Test with Control
        $payload['audiences'] = ['Control'];
        $response2 = $this->actingAs($user)->postJson('/api/brandlift/store', $payload);
        $response2->assertStatus(422);
        $response2->assertJsonValidationErrors(['audiences.0']);
    }
}
