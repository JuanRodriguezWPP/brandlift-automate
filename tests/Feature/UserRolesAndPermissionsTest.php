<?php

namespace Tests\Feature;

use App\Mail\BrandliftTagsMail;
use App\Mail\WelcomeUserMail;
use App\Models\BrandliftEditLog;
use App\Models\BrandliftStudy;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class UserRolesAndPermissionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_login_via_valid_token(): void
    {
        $user = User::factory()->create([
            'status' => 'active',
            'login_token' => 'test-token-12345',
            'last_login_at' => null,
        ]);

        $response = $this->get('/login/token/test-token-12345');

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($user);

        $user->refresh();
        $this->assertNotNull($user->last_login_at);
    }

    public function test_inactive_user_cannot_login_via_token(): void
    {
        User::factory()->create([
            'status' => 'inactive',
            'login_token' => 'inactive-token-123',
        ]);

        $response = $this->get('/login/token/inactive-token-123');

        $response->assertRedirect('/login');
        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_user_can_switch_active_market(): void
    {
        $user = User::factory()->create([
            'role' => 'local',
            'markets' => ['PE', 'PRI', 'COL'],
        ]);

        $response = $this->actingAs($user)->postJson('/user/switch-market', [
            'market' => 'COL',
        ]);

        $response->assertOk();
        $response->assertJson(['success' => true, 'active_market' => 'COL']);
        $this->assertEquals('COL', session('active_market'));
    }

    public function test_local_user_only_sees_assigned_markets_in_dashboard_api(): void
    {
        $user = User::factory()->create([
            'role' => 'local',
            'markets' => ['PE', 'COL'],
        ]);

        BrandliftStudy::create([
            'market' => 'PE',
            'campaign_name' => 'Campa_PE',
            'client_name' => 'Client PE',
        ]);

        BrandliftStudy::create([
            'market' => 'MEX',
            'campaign_name' => 'Camp_MEX',
            'client_name' => 'Client MEX',
        ]);

        $response = $this->actingAs($user)->getJson('/api/brandlift/history');

        $response->assertOk();
        $data = $response->json('studies.data');

        $campaigns = collect($data)->pluck('campaign_name')->all();
        $this->assertContains('Campa_PE', $campaigns);
        $this->assertNotContains('Camp_MEX', $campaigns);
    }

    public function test_admin_user_sees_all_markets_in_dashboard_api(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'email' => 'camilo.serrato@wppmedia.com',
            'markets' => null,
        ]);

        BrandliftStudy::create(['market' => 'PE', 'campaign_name' => 'Camp_PE_Admin', 'client_name' => 'Client PE']);
        BrandliftStudy::create(['market' => 'MEX', 'campaign_name' => 'Camp_MEX_Admin', 'client_name' => 'Client MEX']);

        $response = $this->actingAs($admin)->getJson('/api/brandlift/history');

        $response->assertOk();
        $data = $response->json('studies.data');

        $campaigns = collect($data)->pluck('campaign_name')->all();
        $this->assertContains('Camp_PE_Admin', $campaigns);
        $this->assertContains('Camp_MEX_Admin', $campaigns);
    }

    public function test_can_send_tags_via_email_and_log_is_recorded(): void
    {
        Mail::fake();

        $user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@wppmedia.com',
            'role' => 'local',
            'markets' => ['PRI'],
        ]);

        $study = BrandliftStudy::create([
            'market' => 'PRI',
            'campaign_name' => 'Camp_Tags_Test',
            'client_name' => 'Client PRI',
        ]);

        $response = $this->actingAs($user)->postJson("/api/brandlift/{$study->id}/send-tags", [
            'emails' => 'recipient1@example.com, recipient2@example.com',
            'message' => 'Por favor implementar estos tags.',
        ]);

        $response->assertOk();
        $response->assertJson(['success' => true]);

        Mail::assertSent(BrandliftTagsMail::class, function ($mail) {
            return $mail->hasTo('recipient1@example.com') && $mail->hasTo('recipient2@example.com');
        });

        // Test study with Google Sheet includes sheet link in rendered email
        $studyWithSheet = BrandliftStudy::create([
            'market' => 'PRI',
            'campaign_name' => 'Camp_Tags_Sheet_Test',
            'client_name' => 'Client PRI',
            'sheet_id' => 'test-sheet-id-789',
        ]);

        $this->actingAs($user)->postJson("/api/brandlift/{$studyWithSheet->id}/send-tags", [
            'emails' => 'client@example.com',
        ]);

        Mail::assertSent(BrandliftTagsMail::class, function ($mail) {
            if ($mail->study->sheet_id === 'test-sheet-id-789') {
                $rendered = $mail->render();

                return str_contains($rendered, 'test-sheet-id-789') && str_contains($rendered, 'https://docs.google.com/spreadsheets/d/test-sheet-id-789/edit');
            }

            return true;
        });

        $this->assertDatabaseHas('brandlift_edit_logs', [
            'brandlift_study_id' => $study->id,
            'user_id' => $user->id,
        ]);

        $log = BrandliftEditLog::where('brandlift_study_id', $study->id)->first();
        $this->assertNotNull($log);
        $this->assertEquals('send_tags', $log->changes_made['action'] ?? null);
    }

    public function test_creating_user_sends_welcome_email(): void
    {
        Mail::fake();

        $admin = User::factory()->create([
            'role' => 'admin',
            'email' => 'camilo.serrato@wppmedia.com',
        ]);

        $response = $this->actingAs($admin)->post('/users', [
            'name' => 'Nuevo Usuario Local',
            'email' => 'nuevo.local@wppmedia.com',
            'role' => 'local',
            'markets' => ['PE', 'ECU'],
            'status' => 'active',
        ]);

        $response->assertRedirect('/users');
        $this->assertDatabaseHas('users', [
            'email' => 'nuevo.local@wppmedia.com',
            'role' => 'local',
        ]);

        Mail::assertSent(WelcomeUserMail::class, function ($mail) {
            return $mail->hasTo('nuevo.local@wppmedia.com');
        });
    }

    public function test_admin_simulating_market_filters_dashboard_api(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'email' => 'camilo.serrato@wppmedia.com',
            'markets' => null,
        ]);

        BrandliftStudy::create(['market' => 'PE', 'campaign_name' => 'Camp_PE_Simulated', 'client_name' => 'Client PE']);
        BrandliftStudy::create(['market' => 'MEX', 'campaign_name' => 'Camp_MEX_Simulated', 'client_name' => 'Client MEX']);

        // When admin switches market to PE
        $response = $this->actingAs($admin)
            ->withSession(['active_market' => 'PE'])
            ->getJson('/api/brandlift/history');

        $response->assertOk();
        $data = $response->json('studies.data');

        $campaigns = collect($data)->pluck('campaign_name')->all();
        $this->assertContains('Camp_PE_Simulated', $campaigns);
        $this->assertNotContains('Camp_MEX_Simulated', $campaigns);
    }

    public function test_retry_sync_validates_permissions_and_required_parameters(): void
    {
        $user = User::factory()->create([
            'role' => 'local',
            'markets' => ['PE'],
        ]);

        $study = BrandliftStudy::create([
            'market' => 'MEX',
            'campaign_name' => 'Camp_MEX_Unauthorized',
            'client_name' => 'Client MEX',
        ]);

        // Attempting to retry sync on an unauthorized market returns 403
        $response = $this->actingAs($user)->postJson("/api/brandlift/{$study->id}/retry-sync");
        $response->assertForbidden();

        // With authorized market but missing profile/advertiser ID
        $authorizedStudy = BrandliftStudy::create([
            'market' => 'PE',
            'campaign_name' => 'Camp_PE_No_IDs',
            'client_name' => 'Client PE',
        ]);

        $responseAuthorized = $this->actingAs($user)->postJson("/api/brandlift/{$authorizedStudy->id}/retry-sync");
        $responseAuthorized->assertStatus(422);
        $responseAuthorized->assertJson(['success' => false]);
    }

    public function test_link_sheet_manually_updates_study_and_logs_action(): void
    {
        $user = User::factory()->create([
            'role' => 'local',
            'markets' => ['PE'],
        ]);

        $study = BrandliftStudy::create([
            'market' => 'PE',
            'campaign_name' => 'Camp_PE_Sheet_Resolution',
            'client_name' => 'Client PE',
            'status' => 'error',
            'error_message' => 'Error al crear Google Sheet',
        ]);

        $response = $this->actingAs($user)->postJson("/api/brandlift/{$study->id}/link-sheet", [
            'sheet_url_or_id' => 'https://docs.google.com/spreadsheets/d/1BxiMVs0XRA5nFMdKvBdBZjgmUUqptlbs74OgvE2upms/edit',
        ]);

        $response->assertOk();
        $response->assertJson(['success' => true]);

        $study->refresh();
        $this->assertEquals('1BxiMVs0XRA5nFMdKvBdBZjgmUUqptlbs74OgvE2upms', $study->sheet_id);
        $this->assertNull($study->error_message);

        $this->assertDatabaseHas('brandlift_edit_logs', [
            'brandlift_study_id' => $study->id,
            'user_id' => $user->id,
        ]);
        $log = BrandliftEditLog::where('brandlift_study_id', $study->id)->first();
        $this->assertEquals('link_sheet', $log->changes_made['action'] ?? null);
    }
}
