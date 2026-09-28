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
    public function index(Request $request)
    {
        $editId = $request->query('edit_id');
        $editStudy = null;
        if ($editId) {
            $editStudy = \App\Models\BrandliftStudy::with('questions', 'creatives')->find($editId);
        }
        return view('brandlift-form', compact('editStudy'));
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
        // Limpiar strings vacíos para evitar que Laravel falle las reglas de validación (ej. end_date)
        $inputs = $request->all();
        foreach (['end_date', 'investment', 'cm360_site_id', 'cm360_profile_id', 'cm360_advertiser_id'] as $field) {
            if (isset($inputs[$field]) && $inputs[$field] === '') {
                $inputs[$field] = null;
            }
        }
        $request->replace($inputs);

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
            'end_date' => 'nullable|date',
            'investment' => 'nullable|string',
            'cm360_site_id' => 'nullable|string',
            'cm360_profile_id' => 'nullable|string',
            'cm360_advertiser_id' => 'nullable|string',
            'theme_colors' => 'nullable',
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
                $market = $request->input('market');
                $campaignNameRaw = $request->input('campaign_name');
                $clientNameInput = $request->input('client_name');
                if (empty($clientNameInput)) {
                    $clientNameInput = 'Client';
                }
                $clientName = str_replace(' ', '_', (string) $clientNameInput);
                
                $year = date('Y');
                $month = date('m');
                
                // Construimos: aaaa_mm_MCS_Mercado_advertaiser_campaña_brandlift
                $formattedCampaignName = "{$year}_{$month}_MCS_{$market}_{$clientName}_{$campaignNameRaw}_brandlift";

                $study = BrandliftStudy::create([
                    'market' => $market,
                    'campaign_name' => $formattedCampaignName,
                    'question_count' => $request->input('question_count'),
                    'creative_width' => $request->input('creative_width'),
                    'creative_height' => $request->input('creative_height'),
                    'client_name' => $request->input('client_name'),
                    'audiences' => $request->input('audiences'),
                    'dps_tags' => $request->input('dps_tags'),
                    'sheet_id' => $request->input('sheet_id'),
                    'end_date' => $request->input('end_date'),
                    'investment' => $request->input('investment'),
                    'cm360_site_id' => $request->input('cm360_site_id'),
                    'cm360_profile_id' => $request->input('cm360_profile_id'),
                    'cm360_advertiser_id' => $request->input('cm360_advertiser_id'),
                    'theme_colors' => $request->input('theme_colors'),
                    'created_by' => auth()->id() ?? null,
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
     * Update existing creatives in CM360 for an edited study.
     */
    public function updateCreatives(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'study_id' => 'required|integer|exists:brandlift_studies,id',
            'creatives' => 'required|array|min:1',
            'creatives.*.question_number' => 'required|integer|min:1',
            'creatives.*.html' => 'required|string',
            'creatives.*.variant_key' => 'nullable|string|max:255',
            'questions' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Datos de entrada inválidos',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $study = \App\Models\BrandliftStudy::with('creatives')->findOrFail($request->input('study_id'));
            
            // 1. Update questions in DB if provided
            if ($request->has('questions')) {
                // Capture old questions for the log
                $oldQuestions = \App\Models\BrandliftQuestion::where('brandlift_study_id', $study->id)
                    ->orderBy('question_number')
                    ->get()
                    ->map(function($q) {
                        return ['text' => $q->question_text, 'answers' => $q->answers];
                    })->toArray();

                \App\Models\BrandliftQuestion::where('brandlift_study_id', $study->id)->delete();
                $newQuestions = [];
                foreach ($request->input('questions') as $index => $q) {
                    $newQuestions[] = ['text' => $q['text'] ?? '', 'answers' => $q['answers'] ?? []];
                    \App\Models\BrandliftQuestion::create([
                        'brandlift_study_id' => $study->id,
                        'question_number' => $index + 1,
                        'question_text' => $q['text'] ?? '',
                        'answers' => $q['answers'] ?? [],
                        'creative_html' => $q['creative_html'] ?? ($request->input('creatives')[0]['html'] ?? ''),
                    ]);
                }

                // Log the changes
                \App\Models\BrandliftEditLog::create([
                    'brandlift_study_id' => $study->id,
                    'user_id' => auth()->id(),
                    'changes_made' => [
                        'action' => 'edit_questions',
                        'old_questions' => $oldQuestions,
                        'new_questions' => $newQuestions
                    ]
                ]);
            }

            // 2. Update Creatives in CM360
            $updatedCount = 0;
            $errors = [];
            
            foreach ($request->input('creatives') as $creativeData) {
                $qNum = $creativeData['question_number'];
                $vKey = $creativeData['variant_key'] ?? null;
                $newHtml = $creativeData['html'];
                
                // Add clickTag exactly like pushToCM360
                $clickTagScript = '<script type="text/javascript">' .
                    'var clickTag = "https://www.wppmedia.com/es";' .
                    'function handleClick(){window.open(clickTag,"_blank");}' .
                    '</script>';

                if (stripos($newHtml, '<head>') !== false) {
                    $newHtml = str_ireplace('<head>', '<head>' . $clickTagScript, $newHtml);
                } else {
                    $newHtml = $clickTagScript . $newHtml;
                }

                if (stripos($newHtml, 'onclick') === false && stripos($newHtml, '<body') !== false) {
                    $newHtml = preg_replace('/<body([^>]*)>/i', '<body$1 onclick="handleClick()">', $newHtml, 1);
                }

                // Find matching creative in DB
                $query = $study->creatives()->where('question_number', $qNum);
                if ($vKey) {
                    $query->where('variant_key', $vKey);
                } else {
                    $query->whereNull('variant_key');
                }
                
                $dbCreative = $query->first();
                
                if ($dbCreative && $dbCreative->cm360_creative_id) {
                    try {
                        $creativeName = "{$study->campaign_name}_Q{$qNum}";
                        if ($vKey) $creativeName .= "_{$vKey}";
                        
                        $this->cmService->updateCreativeHtml(
                            $study->cm360_profile_id,
                            $study->cm360_advertiser_id,
                            $dbCreative->cm360_creative_id,
                            $newHtml,
                            $creativeName
                        );
                        
                        // Update HTML in DB
                        $dbCreative->update(['creative_html' => $newHtml]);
                        $updatedCount++;
                    } catch (\Exception $e) {
                        $errors[] = "Error en Q{$qNum}: " . $e->getMessage();
                        Log::error("Failed to update creative Q{$qNum}", ['error' => $e->getMessage()]);
                    }
                }
            }
            
            if (empty($errors)) {
                return response()->json(['success' => true, 'message' => "Creativos actualizados exitosamente ($updatedCount)"]);
            } else {
                return response()->json(['success' => false, 'message' => 'Algunos creativos fallaron al actualizar', 'errors' => $errors], 500);
            }

        } catch (\Exception $e) {
            Log::error('CM360 update creatives failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Error interno al actualizar en CM360: ' . $e->getMessage()
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

    /**
     * Guarda los tags y resultados devueltos por CM360 en la base de datos.
     */
    public function storeTags(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'study_id' => 'required|exists:brandlift_studies,id',
            'tags' => 'required|array',
            'tags.*.status' => 'required|string',
            'tags.*.question_number' => 'nullable|integer',
            'tags.*.creative_name' => 'nullable|string',
            'tags.*.tag_type' => 'nullable|string',
            'tags.*.placement_id' => 'nullable|string',
            'tags.*.tag_script' => 'nullable|string',
            'tags.*.error' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Datos inválidos',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::transaction(function () use ($request) {
                $study = BrandliftStudy::findOrFail($request->input('study_id'));
                
                // Actualizar status del study si fue exitoso
                $study->update([
                    'cm360_pushed' => true,
                    'cm360_pushed_at' => now(),
                    'status' => 'cm360_pushed',
                ]);

                foreach ($request->input('tags') as $tagData) {
                    $study->tags()->create([
                        'status' => $tagData['status'],
                        'question_number' => $tagData['question_number'] ?? null,
                        'creative_name' => $tagData['creative_name'] ?? null,
                        'tag_type' => $tagData['tag_type'] ?? null,
                        'placement_id' => $tagData['placement_id'] ?? null,
                        'tag_script' => $tagData['tag_script'] ?? null,
                        'error' => $tagData['error'] ?? null,
                    ]);
                }
            });

            return response()->json(['success' => true, 'message' => 'Tags guardados exitosamente.']);
        } catch (\Exception $e) {
            Log::error('Error guardando tags: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error guardando tags.'], 500);
        }
    }
}
