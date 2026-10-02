<?php

namespace Tests\Feature;

use App\Models\BrandliftQuestion;
use App\Models\BrandliftStudy;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardStudyDetailTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test history endpoint requires authentication.
     */
    public function test_show_study_detail_requires_authentication(): void
    {
        $response = $this->getJson('/api/brandlift/history/1');

        $response->assertUnauthorized();
    }

    /**
     * Test history endpoint returns all saved study data with questions and relations.
     */
    public function test_show_study_detail_returns_all_saved_data_and_relations(): void
    {
        $user = User::factory()->create([
            'market' => 'PRI',
        ]);

        $study = BrandliftStudy::create([
            'market' => 'PRI',
            'campaign_name' => '2026_10_MCS_PRI_ACME_Study',
            'client_name' => 'ACME Corp',
            'question_count' => 2,
            'creative_width' => 300,
            'creative_height' => 250,
            'sheet_id' => '1SheetIdXYZ1234567890',
            'status' => 'cm360_pushed',
            'cm360_pushed' => true,
            'cm360_campaign_id' => '99887766',
            'cm360_advertiser_id' => '554433',
            'cm360_site_id' => '221100',
            'audiences' => ['Millennials', 'Shoppers'],
            'dps_tags' => ['DV360', 'TTD'],
            'end_date' => '2026-12-31',
        ]);

        BrandliftQuestion::create([
            'brandlift_study_id' => $study->id,
            'question_number' => 1,
            'question_text' => '¿Conoces la marca ACME?',
            'answers' => ['Sí', 'No', 'Tal vez'],
            'creative_html' => '<div>Survey Preview</div>',
        ]);

        BrandliftQuestion::create([
            'brandlift_study_id' => $study->id,
            'question_number' => 2,
            'question_text' => '¿Comprarías productos ACME?',
            'answers' => ['Definitivamente sí', 'Probablemente', 'No'],
        ]);

        $response = $this->actingAs($user)->getJson("/api/brandlift/history/{$study->id}");

        $response->assertOk()
            ->assertJsonPath('study.campaign_name', '2026_10_MCS_PRI_ACME_Study')
            ->assertJsonPath('study.client_name', 'ACME Corp')
            ->assertJsonPath('study.sheet_id', '1SheetIdXYZ1234567890')
            ->assertJsonPath('study.end_date', '2026-12-31')
            ->assertJsonPath('study.audiences', ['Millennials', 'Shoppers'])
            ->assertJsonPath('study.dps_tags', ['DV360', 'TTD'])
            ->assertJsonCount(2, 'study.questions')
            ->assertJsonPath('study.questions.0.question_text', '¿Conoces la marca ACME?')
            ->assertJsonPath('study.questions.0.answers', ['Sí', 'No', 'Tal vez'])
            ->assertJsonPath('study.questions.1.question_text', '¿Comprarías productos ACME?');
    }

    /**
     * Test delete study removes record and handles Google Sheet deletion.
     */
    public function test_destroy_deletes_study_and_sheet(): void
    {
        $user = User::factory()->create([
            'role' => 'admin',
        ]);

        $study = BrandliftStudy::create([
            'market' => 'PRI',
            'campaign_name' => '2026_10_MCS_PRI_ACME_Study_To_Delete',
            'client_name' => 'ACME Corp',
            'question_count' => 1,
            'creative_width' => 300,
            'creative_height' => 250,
            'sheet_id' => '1DummySheetId',
            'status' => 'created',
        ]);

        BrandliftQuestion::create([
            'brandlift_study_id' => $study->id,
            'question_number' => 1,
            'question_text' => '¿Conoces la marca ACME?',
            'answers' => ['Sí', 'No'],
        ]);

        $response = $this->actingAs($user)->deleteJson("/api/brandlift/history/{$study->id}");

        $response->assertOk()
            ->assertJson(['success' => true]);

        $this->assertDatabaseMissing('brandlift_studies', [
            'id' => $study->id,
        ]);
        $this->assertDatabaseMissing('brandlift_questions', [
            'brandlift_study_id' => $study->id,
        ]);
    }
}
