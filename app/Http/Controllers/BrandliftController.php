<?php

namespace App\Http\Controllers;

use App\Models\BrandliftStudy;
use App\Models\BrandliftQuestion;
use App\Services\CampaignManagerService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

/**
 * BrandliftController
 *
 * Handles Brandlift creative operations:
 * - Rendering the form view
 * - Storing brandlift studies in the database
 * - Pushing generated creatives to Campaign Manager 360
 */
class BrandliftController extends Controller
{
    public function __construct(
        private CampaignManagerService $cmService,
        private \App\Services\GoogleWorkspaceService $googleService
    ) {}

    /**
     * Show the Brandlift creator form.
     */
    public function index()
    {
        return view('brandlift-form');
    }

    /**
     * Handle incoming survey responses from the creatives.
     * Appends the answers directly to the Google Sheet.
     */
    public function submitResponse(Request $request): JsonResponse
    {
        try {
            $sheetId = $request->input('sheetId');
            
            if (!$sheetId) {
                return response()->json(['error' => 'Missing sheetId'], 400);
            }

            // Extract data from the request
            $q1 = $request->input('q1', '');
            $a1 = $request->input('a1', '');
            $q2 = $request->input('q2', '');
            $a2 = $request->input('a2', '');
            $campaign = $request->input('campaign', '');
            $market = $request->input('market', '');
            $group = $request->input('group', '');
            $tagType = $request->input('tagType', '');
            $date = now()->format('Y-m-d H:i:s');

            // Format row: [Date, Campaign, Market, Group, TagType, Q1, A1, Q2, A2]
            $values = [$date, $campaign, $market, $group, $tagType, $q1, $a1, $q2, $a2];

            // Append to Google Sheet using the service account
            $success = $this->googleService->appendRowToSheet($sheetId, $values);

            if ($success) {
                return response()->json(['success' => true])
                    ->header('Access-Control-Allow-Origin', '*') // Allow CORS from CM360
                    ->header('Access-Control-Allow-Methods', 'POST, OPTIONS')
                    ->header('Access-Control-Allow-Headers', 'Content-Type');
            } else {
                return response()->json(['error' => 'Failed to write to sheet'], 500)
                    ->header('Access-Control-Allow-Origin', '*');
            }

        } catch (\Exception $e) {
            Log::error('API Response Submit Error', [
                'error' => $e->getMessage()
            ]);
            return response()->json(['error' => 'Server error'], 500)
                ->header('Access-Control-Allow-Origin', '*');
        }
    }

    /**
     * Store a new brandlift study in the database.
     *
     * Expects JSON payload with:
     * - market: Market code (PE, MEX, COL, etc.)
     * - campaign_name: Campaign name
     * - question_count: Number of questions (1 or 2)
     * - creative_width: Creative width in px
     * - creative_height: Creative height in px
     * - sheet_id: Google Sheet ID (optional)
     * - questions: Array of [{question_number, question_text, answers, creative_html}]
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'market' => 'required|string|max:50',
            'campaign_name' => 'required|string|max:255',
            'question_count' => 'required|integer|min:1|max:2',
            'creative_width' => 'required|integer|min:1',
            'creative_height' => 'required|integer|min:1',
            'sheet_id' => 'nullable|string',
            'questions' => 'required|array|min:1|max:2',
            'questions.*.question_number' => 'required|integer|min:1',
            'questions.*.question_text' => 'required|string',
            'questions.*.answers' => 'required|array|min:2',
            'questions.*.creative_html' => 'nullable|string',
            'client_name' => 'nullable|string|max:255',
            'audiences' => 'nullable|array',
            'dps_tags' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Datos inválidos para guardar el brandlift.',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $study = DB::transaction(function () use ($request) {
                $study = BrandliftStudy::create([
                    'market' => $request->input('market'),
                    'campaign_name' => $request->input('campaign_name'),
                    'question_count' => $request->input('question_count'),
                    'creative_width' => $request->input('creative_width'),
                    'creative_height' => $request->input('creative_height'),
                    'client_name' => $request->input('client_name'),
                    'audiences' => $request->input('audiences'),
                    'dps_tags' => $request->input('dps_tags'),
                    'sheet_id' => $request->input('sheet_id'),
                    'status' => 'created',
                ]);

                foreach ($request->input('questions') as $q) {
                    BrandliftQuestion::create([
                        'brandlift_study_id' => $study->id,
                        'question_number' => $q['question_number'],
                        'question_text' => $q['question_text'],
                        'answers' => $q['answers'],
                        'creative_html' => $q['creative_html'] ?? null,
                    ]);
                }

                return $study;
            });

            return response()->json([
                'success' => true,
                'message' => 'Brandlift guardado exitosamente.',
                'study_id' => $study->id,
            ]);
        } catch (\Exception $e) {
            Log::error('Error al guardar brandlift', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al guardar el brandlift: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Push generated HTML creatives to Campaign Manager 360.
     *
     * Expects JSON payload with:
     * - profile_id: CM360 user profile ID
     * - advertiser_id: CM360 advertiser ID
     * - site_id: CM360 site ID
     * - campaign_name: Campaign name for the new CM360 campaign
     * - creative_name: Base name for the creatives
     * - creatives: Array of [{question_number, html, width, height}]
     * - study_id: (optional) Brandlift study ID to update in the DB
     */
    public function pushToCM360(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'profile_id' => 'required|string',
            'advertiser_id' => 'required|string',
            'site_id' => 'required|string',
            'market' => 'required|string|max:50',
            'client_name' => 'nullable|string|max:255',
            'campaign_name' => 'required|string|max:255',
            'creative_name' => 'nullable|string|max:255',
            'backup_image' => 'nullable|string',
            'creatives' => 'required|array|min:1|max:10',
            'creatives.*.question_number' => 'required|integer|min:1',
            'creatives.*.html' => 'required|string',
            'creatives.*.width' => 'required|integer|min:1',
            'creatives.*.height' => 'required|integer|min:1',
            'creatives.*.variant_key' => 'nullable|string|max:255',
            'study_id' => 'nullable|integer|exists:brandlift_studies,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Datos de entrada inválidos',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $result = $this->cmService->uploadCreatives(
                profileId: $request->input('profile_id'),
                advertiserId: $request->input('advertiser_id'),
                siteId: $request->input('site_id'),
                campaignName: $request->input('campaign_name', ''),
                creativeName: $request->input('creative_name', 'Brandlift Creative'),
                creatives: $request->input('creatives'),
                market: $request->input('market'),
                clientName: $request->input('client_name', 'Client'),
                backupImageBase64: $request->input('backup_image')
            );

            // Update the brandlift study record if study_id was provided
            if ($studyId = $request->input('study_id')) {
                $study = BrandliftStudy::find($studyId);
                if ($study) {
                    $campaignId = null;
                    if ($result['success'] && !empty($result['results'])) {
                        $campaignId = $result['results'][0]['campaign_id'] ?? null;
                    }

                    $study->update([
                        'cm360_profile_id' => $request->input('profile_id'),
                        'cm360_advertiser_id' => $request->input('advertiser_id'),
                        'cm360_pushed' => $result['success'],
                        'cm360_pushed_at' => $result['success'] ? now() : null,
                        'cm360_campaign_id' => $campaignId,
                        'status' => $result['success'] ? 'pushed' : 'error',
                        'cm360_tags' => $result['success'] ? json_encode($result['results']) : null,
                    ]);

                    // Store creatives mapping for future updates
                    if ($result['success'] && !empty($result['results'])) {
                        foreach ($result['results'] as $resItem) {
                            if (!empty($resItem['creative_id']) && $resItem['status'] === 'success') {
                                \App\Models\BrandliftCreative::create([
                                    'brandlift_study_id' => $study->id,
                                    'question_number' => $resItem['question_number'],
                                    'variant_key' => $resItem['variant_key'] ?? null,
                                    'cm360_creative_id' => $resItem['creative_id'],
                                    'cm360_asset_id' => $resItem['asset_id'] ?? null,
                                    'creative_html' => $resItem['html'] ?? null,
                                ]);
                            }
                        }
                    }
                }
            }

            return response()->json($result, $result['success'] ? 200 : 500);
        } catch (\Exception $e) {
            Log::error('CM360 push failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            // Mark as error in DB if study_id was provided
            if ($studyId = $request->input('study_id')) {
                BrandliftStudy::where('id', $studyId)->update(['status' => 'error']);
            }

            return response()->json([
                'success' => false,
                'message' => 'Error interno al conectar con Campaign Manager 360: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Automate Google Sheet cloning and folder generation
     */
    public function automateSheet(Request $request, \App\Services\GoogleWorkspaceService $googleService): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'market' => 'required|string|max:50',
            'campaign_name' => 'required|string|max:255',
            'client_name' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Faltan datos de mercado o campaña.',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $market = $request->input('market');
            $campaignName = $request->input('campaign_name');
            // Reemplazamos espacios en el cliente por guiones bajos si es necesario
            $clientName = str_replace(' ', '_', $request->input('client_name', 'Client'));
            
            $year = date('Y');
            $month = date('m');
            
            // Construimos: aaaa_mm_MCS_Mercado_advertaiser_campaña_brandlift
            $newFileName = "{$year}_{$month}_MCS_{$market}_{$clientName}_{$campaignName}_brandlift";

            // 1. Encontrar o crear la carpeta del mercado
            $marketFolderId = $googleService->findOrCreateMarketFolder($market);

            // 2. Duplicar el template en esa carpeta
            $newSheetId = $googleService->duplicateTemplate($newFileName, $marketFolderId);

            return response()->json([
                'success' => true,
                'sheet_id' => $newSheetId
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error en automatización de Google Workspace: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error al crear el Sheet: ' . $e->getMessage()
            ], 500);
        }
    }
}
