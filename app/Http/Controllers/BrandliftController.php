<?php

namespace App\Http\Controllers;

use App\Mail\BrandliftTagsMail;
use App\Models\BrandliftCreative;
use App\Models\BrandliftEditLog;
use App\Models\BrandliftQuestion;
use App\Models\BrandliftStudy;
use App\Services\CampaignManagerService;
use App\Services\GoogleWorkspaceService;
use App\Services\LiquidId;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
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
        private GoogleWorkspaceService $googleService
    ) {}

    /**
     * Show the Brandlift creator form.
     */
    public function index(Request $request)
    {
        $editId = $request->query('edit_id');
        $editStudy = null;
        if ($editId) {
            $editStudy = BrandliftStudy::findByLiquidId($editId, ['questions', 'creatives']);
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

            if (! $sheetId) {
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
                'error' => $e->getMessage(),
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
        if (isset($inputs['audiences']) && is_array($inputs['audiences'])) {
            $inputs['audiences'] = array_map(fn ($a) => is_string($a) ? trim($a) : $a, $inputs['audiences']);
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
            'audiences.*' => 'required|string|distinct:ignore_case',
            'dps_tags' => 'nullable|array',
            'end_date' => 'nullable|date|after_or_equal:today',
            'investment' => 'nullable|string',
            'cm360_site_id' => 'nullable|string',
            'cm360_profile_id' => 'nullable|string',
            'cm360_advertiser_id' => 'nullable|string',
            'theme_colors' => 'nullable',
        ], [
            'end_date.after_or_equal' => 'La fecha de finalización no puede ser una fecha pasada.',
            'audiences.*.required' => 'No se permiten grupos de audiencia con nombres vacíos.',
            'audiences.*.distinct' => 'No se pueden repetir los nombres de los grupos de audiencia.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Datos inválidos para guardar el brandlift.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $market = $request->input('market');
        $user = auth()->user();
        if ($user && ! $user->hasMarket($market)) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permisos para operar en el mercado seleccionado ('.$market.').',
            ], 403);
        }

        try {
            $study = DB::transaction(function () use ($request, $market, $user) {
                $campaignNameRaw = $request->input('campaign_name');
                $clientNameInput = $request->input('client_name');
                if (empty($clientNameInput)) {
                    $clientNameInput = 'Client';
                }
                $clientName = str_replace(' ', '_', (string) $clientNameInput);

                $year = date('Y');
                $month = date('m');

                // Construimos: aaaa_mm_WMSCSLATAM_Mercado_cliente_campaña_brandlift_b
                $formattedCampaignName = "{$year}_{$month}_WMSCSLATAM_{$market}_{$clientName}_{$campaignNameRaw}_brandlift_b";

                // Validar si ya existe un estudio activo o completado
                $existingActiveStudy = BrandliftStudy::where('campaign_name', $formattedCampaignName)
                    ->where('status', '!=', 'inactive')
                    ->where('cm360_pushed', true)
                    ->first();

                if ($existingActiveStudy) {
                    throw new \DomainException("DUPLICATE: Ya existe un Brand Lift activo registrado con el nombre '{$campaignNameRaw}' para este cliente y mercado.");
                }

                // Si ya existe un registro previo no finalizado o con error, reusarlo para evitar duplicados al reintentar
                $study = BrandliftStudy::where('campaign_name', $formattedCampaignName)
                    ->where(function ($q) {
                        $q->whereIn('status', ['created', 'error'])
                            ->orWhere('cm360_pushed', false);
                    })
                    ->first();

                $studyData = [
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
                ];

                $isNew = false;
                if ($study) {
                    $study->update($studyData);
                    $study->questions()->delete();
                } else {
                    $study = BrandliftStudy::create($studyData);
                    $isNew = true;
                }

                foreach ($request->input('questions') as $q) {
                    BrandliftQuestion::create([
                        'brandlift_study_id' => $study->id,
                        'question_number' => $q['question_number'],
                        'question_text' => $q['question_text'],
                        'answers' => $q['answers'],
                        'creative_html' => $q['creative_html'] ?? null,
                    ]);
                }

                // Audit Log
                BrandliftEditLog::create([
                    'brandlift_study_id' => $study->id,
                    'user_id' => auth()->id(),
                    'changes_made' => [
                        'action' => $isNew ? 'created' : 'updated',
                        'description' => $isNew ? 'BrandLift creado en la plataforma' : 'BrandLift actualizado',
                        'user_name' => $user?->name ?? 'Usuario',
                        'timestamp' => now()->toIso8601String(),
                    ],
                ]);

                return $study;
            });

            return response()->json([
                'success' => true,
                'message' => 'Brandlift guardado exitosamente.',
                'study_id' => $study->id,
            ]);
        } catch (\DomainException $de) {
            return response()->json([
                'success' => false,
                'duplicate' => true,
                'message' => str_replace('DUPLICATE: ', '', $de->getMessage()),
            ], 409);
        } catch (\Exception $e) {
            Log::error('Error al guardar brandlift', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al guardar el brandlift: '.$e->getMessage(),
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
        @set_time_limit(300);
        @ini_set('max_execution_time', '300');

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
                'errors' => $validator->errors(),
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
                    if ($result['success'] && ! empty($result['results'])) {
                        $campaignId = $result['results'][0]['campaign_id'] ?? null;
                    }

                    $study->update([
                        'cm360_profile_id' => $request->input('profile_id'),
                        'cm360_advertiser_id' => $request->input('advertiser_id'),
                        'cm360_pushed' => $result['success'],
                        'cm360_pushed_at' => $result['success'] ? now() : null,
                        'cm360_campaign_id' => $campaignId,
                        'status' => $result['success'] ? 'pushed' : 'error',
                        'error_message' => $result['success'] ? null : ($result['message'] ?? 'Falló la sincronización con Google Campaign Manager 360'),
                        'cm360_tags' => $result['success'] ? json_encode($result['results']) : null,
                    ]);

                    // Store creatives mapping for future updates
                    if ($result['success'] && ! empty($result['results'])) {
                        foreach ($result['results'] as $resItem) {
                            if (! empty($resItem['creative_id']) && $resItem['status'] === 'success') {
                                BrandliftCreative::create([
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
                'trace' => $e->getTraceAsString(),
            ]);

            // Mark as error in DB if study_id was provided
            if ($studyId = $request->input('study_id')) {
                BrandliftStudy::where('id', $studyId)->update([
                    'status' => 'error',
                    'error_message' => 'Error de conexión con Google Campaign Manager 360: '.$e->getMessage(),
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Error interno al conectar con Campaign Manager 360: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update existing creatives in CM360 for an edited study.
     */
    public function updateCreatives(Request $request): JsonResponse
    {
        @set_time_limit(300);
        @ini_set('max_execution_time', '300');

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
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $study = BrandliftStudy::with('creatives')->findOrFail($request->input('study_id'));

            // 1. Update questions in DB if provided
            if ($request->has('questions')) {
                // Capture old questions for the log
                $oldQuestions = BrandliftQuestion::where('brandlift_study_id', $study->id)
                    ->orderBy('question_number')
                    ->get()
                    ->map(function ($q) {
                        return ['text' => $q->question_text, 'answers' => $q->answers];
                    })->toArray();

                BrandliftQuestion::where('brandlift_study_id', $study->id)->delete();
                $newQuestions = [];
                foreach ($request->input('questions') as $index => $q) {
                    $newQuestions[] = ['text' => $q['text'] ?? '', 'answers' => $q['answers'] ?? []];
                    BrandliftQuestion::create([
                        'brandlift_study_id' => $study->id,
                        'question_number' => $index + 1,
                        'question_text' => $q['text'] ?? '',
                        'answers' => $q['answers'] ?? [],
                        'creative_html' => $q['creative_html'] ?? ($request->input('creatives')[0]['html'] ?? ''),
                    ]);
                }

                // Log the changes
                BrandliftEditLog::create([
                    'brandlift_study_id' => $study->id,
                    'user_id' => auth()->id(),
                    'changes_made' => [
                        'action' => 'edit_questions',
                        'old_questions' => $oldQuestions,
                        'new_questions' => $newQuestions,
                    ],
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
                $clickTagScript = '<script type="text/javascript">'.
                    'var clickTag = "https://www.wppmedia.com/es";'.
                    'function handleClick(){window.open(clickTag,"_blank");}'.
                    '</script>';

                if (stripos($newHtml, '<head>') !== false) {
                    $newHtml = str_ireplace('<head>', '<head>'.$clickTagScript, $newHtml);
                } else {
                    $newHtml = $clickTagScript.$newHtml;
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
                        if ($vKey) {
                            $creativeName .= "_{$vKey}";
                        }

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
                        $errors[] = "Error en Q{$qNum}: ".$e->getMessage();
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
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error interno al actualizar en CM360: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Automate Google Sheet cloning and folder generation
     */
    public function automateSheet(Request $request, GoogleWorkspaceService $googleService): JsonResponse
    {
        @set_time_limit(300);
        @ini_set('max_execution_time', '300');

        $validator = Validator::make($request->all(), [
            'market' => 'required|string|max:50',
            'campaign_name' => 'required|string|max:255',
            'client_name' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Faltan datos de mercado o campaña.',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $market = $request->input('market');
            $campaignName = $request->input('campaign_name');
            // Reemplazamos espacios en el cliente por guiones bajos si es necesario
            $clientName = str_replace(' ', '_', $request->input('client_name', 'Client'));

            $year = date('Y');
            $month = date('m');

            // Construimos: aaaa_mm_WMSCSLATAM_Mercado_cliente_campaña_brandlift_b
            $newFileName = "{$year}_{$month}_WMSCSLATAM_{$market}_{$clientName}_{$campaignName}_brandlift_b";

            // Validar si ya existe un estudio activo
            $existingActiveStudy = BrandliftStudy::where('campaign_name', $newFileName)
                ->where('status', '!=', 'inactive')
                ->where('cm360_pushed', true)
                ->first();

            if ($existingActiveStudy) {
                return response()->json([
                    'success' => false,
                    'duplicate' => true,
                    'message' => "Ya existe un Brand Lift activo registrado con el nombre '{$campaignName}' para este cliente y mercado.",
                ], 409);
            }

            // 1. Encontrar o crear la carpeta del mercado
            $marketFolderId = $googleService->findOrCreateMarketFolder($market);

            // 2. Duplicar el template en esa carpeta
            $newSheetId = $googleService->duplicateTemplate($newFileName, $marketFolderId);

            return response()->json([
                'success' => true,
                'sheet_id' => $newSheetId,
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error en automatización de Google Workspace: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al crear el Sheet: '.$e->getMessage(),
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
                'errors' => $validator->errors(),
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
            Log::error('Error guardando tags: '.$e->getMessage());

            return response()->json(['success' => false, 'message' => 'Error guardando tags.'], 500);
        }
    }

    /**
     * Public responsive website simulation preview for sharing.
     */
    public function publicPreview(string|int $id)
    {
        $study = BrandliftStudy::findOrFailByLiquidId($id, ['questions', 'creatives']);

        return view('public-preview', compact('study'));
    }

    /**
     * Serve the raw HTML of a creative for embedding inside an iframe.
     */
    public function publicCreativeHtml(string|int $id, string|int|null $creativeId = null)
    {
        $study = BrandliftStudy::findOrFailByLiquidId($id, ['questions', 'creatives']);

        $html = null;
        if ($creativeId) {
            $realCreativeId = is_numeric($creativeId) ? (int) $creativeId : LiquidId::decode((string) $creativeId);
            $creative = $study->creatives->firstWhere('id', $realCreativeId);
            $html = $creative?->creative_html;
        }

        if (! $html) {
            $creative = $study->creatives->first();
            $html = $creative?->creative_html;
        }

        if (! $html) {
            $question = $study->questions->first();
            $html = $question?->creative_html;
        }

        if (! $html) {
            return response('<div style="font-family:sans-serif;padding:20px;text-align:center;color:#64748b;">Creativo no disponible</div>', 404);
        }

        return response($html, 200, [
            'Content-Type' => 'text/html; charset=UTF-8',
        ]);
    }

    /**
     * Send generated tags and campaign excel to multiple email recipients.
     */
    public function sendTagsEmail(int|string $id, Request $request): JsonResponse
    {
        $study = is_numeric($id) ? BrandliftStudy::with(['questions', 'creatives', 'tags'])->findOrFail($id) : BrandliftStudy::findOrFailByLiquidId($id, ['questions', 'creatives', 'tags']);
        $user = auth()->user();

        if ($user && ! $user->hasMarket($study->market)) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permisos para enviar los tags de este Brandlift.',
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'emails' => 'required',
            'message' => 'nullable|string|max:3000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Por favor indica los correos electrónicos destinatarios.',
                'errors' => $validator->errors(),
            ], 422);
        }

        // Parse emails (can be array or string with commas/spaces/semicolons/newlines)
        $rawEmails = $request->input('emails');
        $emailList = [];
        if (is_array($rawEmails)) {
            $emailList = $rawEmails;
        } else {
            $emailList = preg_split('/[\s,;]+/', (string) $rawEmails, -1, PREG_SPLIT_NO_EMPTY);
        }

        $validEmails = [];
        foreach ($emailList as $email) {
            $clean = trim($email);
            if (filter_var($clean, FILTER_VALIDATE_EMAIL)) {
                $validEmails[] = $clean;
            }
        }
        $validEmails = array_values(array_unique($validEmails));

        if (empty($validEmails)) {
            return response()->json([
                'success' => false,
                'message' => 'No se encontraron correos electrónicos válidos en la lista ingresada.',
            ], 422);
        }

        $customMessage = $request->input('message') ?? 'Adjuntamos los tags generados para la implementación del estudio BrandLift.';

        try {
            $excelContent = $this->generateTagsExcel($study);
            $cleanCampaignName = preg_replace('/[^A-Za-z0-9_\-]/', '_', $study->campaign_name);
            $filename = "Tags_BrandLift_{$cleanCampaignName}.xls";

            // Send email to all recipients
            Mail::to($validEmails)->send(new BrandliftTagsMail(
                study: $study,
                customMessage: $customMessage,
                excelContent: $excelContent,
                filename: $filename
            ));

            // Log the action
            BrandliftEditLog::create([
                'brandlift_study_id' => $study->id,
                'user_id' => auth()->id(),
                'changes_made' => [
                    'action' => 'send_tags',
                    'description' => 'Tags enviados por correo a: '.implode(', ', $validEmails),
                    'recipients' => $validEmails,
                    'user_name' => $user?->name ?? 'Usuario',
                    'timestamp' => now()->toIso8601String(),
                ],
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Tags enviados exitosamente a '.count($validEmails).' destinatario(s).',
                'recipients' => $validEmails,
            ]);
        } catch (\Throwable $e) {
            Log::error('Error sending tags email: '.$e->getMessage(), [
                'study_id' => $study->id,
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Hubo un error al enviar el correo con los tags: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Generate standard Excel XML spreadsheet for tags.
     */
    public function generateTagsExcel(BrandliftStudy $study): string
    {
        $campaign = htmlspecialchars($study->campaign_name, ENT_XML1, 'UTF-8');
        $client = htmlspecialchars($study->client_name ?: 'N/A', ENT_XML1, 'UTF-8');
        $market = htmlspecialchars($study->market_name.' ('.$study->market.')', ENT_XML1, 'UTF-8');
        $size = "{$study->creative_width}x{$study->creative_height}";
        $endDate = $study->end_date ? Carbon::parse($study->end_date)->format('d/m/Y') : 'N/A';
        $sheetUrl = $study->sheet_id ? "https://docs.google.com/spreadsheets/d/{$study->sheet_id}/edit" : 'N/A';
        $previewUrl = route('brandlift.public-preview', $study->liquid_id);

        $tags = $study->tags;

        $xml = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
        $xml .= '<?mso-application progid="Excel.Sheet"?>'."\n";
        $xml .= '<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet" '
            .'xmlns:o="urn:schemas-microsoft-com:office:office" '
            .'xmlns:x="urn:schemas-microsoft-com:office:excel" '
            .'xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet" '
            .'xmlns:html="http://www.w3.org/TR/REC-html40">'."\n";

        $xml .= '<Styles>
          <Style ss:ID="Default" ss:Name="Normal">
           <Alignment ss:Vertical="Center"/>
           <Font ss:FontName="Calibri" ss:Size="11" ss:Color="#000000"/>
          </Style>
          <Style ss:ID="Header">
           <Font ss:FontName="Calibri" ss:Size="12" ss:Bold="1" ss:Color="#FFFFFF"/>
           <Interior ss:Color="#000050" ss:Pattern="Solid"/>
           <Alignment ss:Horizontal="Center" ss:Vertical="Center"/>
          </Style>
          <Style ss:ID="MetaLabel">
           <Font ss:FontName="Calibri" ss:Size="11" ss:Bold="1" ss:Color="#000050"/>
           <Interior ss:Color="#F1F5F9" ss:Pattern="Solid"/>
          </Style>
          <Style ss:ID="MetaValue">
           <Font ss:FontName="Calibri" ss:Size="11" ss:Color="#1E293B"/>
          </Style>
          <Style ss:ID="TagCell">
           <Font ss:FontName="Courier New" ss:Size="10" ss:Color="#0F172A"/>
           <Alignment ss:Vertical="Top" ss:WrapText="1"/>
          </Style>
         </Styles>'."\n";

        $xml .= '<Worksheet ss:Name="Tags BrandLift">'."\n";
        $xml .= '<Table ss:DefaultRowHeight="20">'."\n";
        $xml .= '<Column ss:Width="140"/>'."\n";
        $xml .= '<Column ss:Width="220"/>'."\n";
        $xml .= '<Column ss:Width="100"/>'."\n";
        $xml .= '<Column ss:Width="130"/>'."\n";
        $xml .= '<Column ss:Width="450"/>'."\n";
        $xml .= '<Column ss:Width="250"/>'."\n";

        // Info Header
        $xml .= '<Row><Cell ss:StyleID="Header" ss:MergeAcross="5"><Data ss:Type="String">WPP MEDIA SOLUTIONS — BRANDLIFT TAGS</Data></Cell></Row>'."\n";
        $xml .= '<Row><Cell ss:StyleID="MetaLabel"><Data ss:Type="String">Campaña:</Data></Cell><Cell ss:StyleID="MetaValue" ss:MergeAcross="4"><Data ss:Type="String">'.$campaign.'</Data></Cell></Row>'."\n";
        $xml .= '<Row><Cell ss:StyleID="MetaLabel"><Data ss:Type="String">Cliente:</Data></Cell><Cell ss:StyleID="MetaValue" ss:MergeAcross="4"><Data ss:Type="String">'.$client.'</Data></Cell></Row>'."\n";
        $xml .= '<Row><Cell ss:StyleID="MetaLabel"><Data ss:Type="String">Mercado:</Data></Cell><Cell ss:StyleID="MetaValue" ss:MergeAcross="4"><Data ss:Type="String">'.$market.'</Data></Cell></Row>'."\n";
        $xml .= '<Row><Cell ss:StyleID="MetaLabel"><Data ss:Type="String">Dimensiones:</Data></Cell><Cell ss:StyleID="MetaValue" ss:MergeAcross="4"><Data ss:Type="String">'.$size.'</Data></Cell></Row>'."\n";
        $xml .= '<Row><Cell ss:StyleID="MetaLabel"><Data ss:Type="String">Finaliza:</Data></Cell><Cell ss:StyleID="MetaValue" ss:MergeAcross="4"><Data ss:Type="String">'.$endDate.'</Data></Cell></Row>'."\n";
        $xml .= '<Row><Cell ss:StyleID="MetaLabel"><Data ss:Type="String">Hoja Respuestas:</Data></Cell><Cell ss:StyleID="MetaValue" ss:MergeAcross="4"><Data ss:Type="String">'.$sheetUrl.'</Data></Cell></Row>'."\n";
        $xml .= '<Row><Cell ss:StyleID="MetaLabel"><Data ss:Type="String">Vista Previa:</Data></Cell><Cell ss:StyleID="MetaValue" ss:MergeAcross="4"><Data ss:Type="String">'.$previewUrl.'</Data></Cell></Row>'."\n";
        $xml .= '<Row></Row>'."\n";

        // Table Columns Header
        $xml .= '<Row ss:Height="25">'."\n";
        $xml .= '<Cell ss:StyleID="Header"><Data ss:Type="String">Ítem / Pregunta</Data></Cell>'."\n";
        $xml .= '<Cell ss:StyleID="Header"><Data ss:Type="String">Nombre Creativo</Data></Cell>'."\n";
        $xml .= '<Cell ss:StyleID="Header"><Data ss:Type="String">Tipo Tag</Data></Cell>'."\n";
        $xml .= '<Cell ss:StyleID="Header"><Data ss:Type="String">Placement ID</Data></Cell>'."\n";
        $xml .= '<Cell ss:StyleID="Header"><Data ss:Type="String">Tag / Script</Data></Cell>'."\n";
        $xml .= '<Cell ss:StyleID="Header"><Data ss:Type="String">URL de Vista Previa</Data></Cell>'."\n";
        $xml .= '</Row>'."\n";

        // Rows
        if ($tags && $tags->isNotEmpty()) {
            foreach ($tags as $tag) {
                $item = $tag->question_number ? "Pregunta #{$tag->question_number}" : 'General';
                $crName = htmlspecialchars((string) ($tag->creative_name ?: $study->campaign_name), ENT_XML1, 'UTF-8');
                $type = htmlspecialchars((string) ($tag->tag_type ?: 'Standard'), ENT_XML1, 'UTF-8');
                $placement = htmlspecialchars((string) ($tag->placement_id ?: 'N/A'), ENT_XML1, 'UTF-8');
                $script = htmlspecialchars((string) ($tag->tag_script ?: ''), ENT_XML1, 'UTF-8');

                $xml .= '<Row ss:Height="50">'."\n";
                $xml .= '<Cell><Data ss:Type="String">'.$item.'</Data></Cell>'."\n";
                $xml .= '<Cell><Data ss:Type="String">'.$crName.'</Data></Cell>'."\n";
                $xml .= '<Cell><Data ss:Type="String">'.$type.'</Data></Cell>'."\n";
                $xml .= '<Cell><Data ss:Type="String">'.$placement.'</Data></Cell>'."\n";
                $xml .= '<Cell ss:StyleID="TagCell"><Data ss:Type="String">'.$script.'</Data></Cell>'."\n";
                $xml .= '<Cell><Data ss:Type="String">'.$previewUrl.'</Data></Cell>'."\n";
                $xml .= '</Row>'."\n";
            }
        } else {
            foreach ($study->questions as $q) {
                $xml .= '<Row ss:Height="40">'."\n";
                $xml .= '<Cell><Data ss:Type="String">Pregunta #'.$q->question_number.'</Data></Cell>'."\n";
                $xml .= '<Cell><Data ss:Type="String">'.$campaign.'_Q'.$q->question_number.'</Data></Cell>'."\n";
                $xml .= '<Cell><Data ss:Type="String">HTML5 In-App/Web</Data></Cell>'."\n";
                $xml .= '<Cell><Data ss:Type="String">N/A</Data></Cell>'."\n";
                $xml .= '<Cell ss:StyleID="TagCell"><Data ss:Type="String">Vista interactiva disponible en enlace de previsualización</Data></Cell>'."\n";
                $xml .= '<Cell><Data ss:Type="String">'.$previewUrl.'</Data></Cell>'."\n";
                $xml .= '</Row>'."\n";
            }
        }

        $xml .= '</Table>'."\n";
        $xml .= '</Worksheet>'."\n";
        $xml .= '</Workbook>';

        return $xml;
    }

    /**
     * Reintentar sincronización con Google Campaign Manager 360 para un estudio.
     */
    public function retrySync(Request $request, int $id): JsonResponse
    {
        @set_time_limit(300);
        @ini_set('max_execution_time', '300');

        $study = BrandliftStudy::with(['questions', 'creatives'])->findOrFail($id);

        // Validar permisos de mercado
        $user = auth()->user();
        if ($user && ! $user->isAdmin() && ! $user->hasMarket($study->market)) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permisos para sincronizar este estudio.',
            ], 403);
        }

        $profileId = $request->input('profile_id', $study->cm360_profile_id);
        $advertiserId = $request->input('advertiser_id', $study->cm360_advertiser_id);
        $siteId = $request->input('site_id', $study->cm360_site_id);

        if (empty($profileId) || empty($advertiserId) || empty($siteId)) {
            return response()->json([
                'success' => false,
                'message' => 'Faltan parámetros de CM360 (Perfil, Anunciante o Sitio). Por favor proporciónalos para sincronizar.',
                'missing_fields' => [
                    'profile_id' => empty($profileId),
                    'advertiser_id' => empty($advertiserId),
                    'site_id' => empty($siteId),
                ],
            ], 422);
        }

        // Obtener los creativos
        $creatives = [];
        if ($study->creatives->isNotEmpty()) {
            foreach ($study->creatives as $c) {
                if ($c->creative_html) {
                    $creatives[] = [
                        'question_number' => $c->question_number,
                        'html' => $c->creative_html,
                        'width' => $c->creative_width ?? $study->creative_width ?? 300,
                        'height' => $c->creative_height ?? $study->creative_height ?? 250,
                        'variant_key' => $c->variant_key ?? null,
                    ];
                }
            }
        }

        if (empty($creatives) && $study->questions->isNotEmpty()) {
            foreach ($study->questions as $q) {
                if ($q->creative_html) {
                    $creatives[] = [
                        'question_number' => $q->question_number,
                        'html' => $q->creative_html,
                        'width' => $study->creative_width ?? 300,
                        'height' => $study->creative_height ?? 250,
                        'variant_key' => null,
                    ];
                }
            }
        }

        if (empty($creatives)) {
            return response()->json([
                'success' => false,
                'message' => 'No hay creativos HTML almacenados para este estudio. Abre el configurador para regenerarlos.',
            ], 422);
        }

        try {
            $result = $this->cmService->uploadCreatives(
                profileId: $profileId,
                advertiserId: $advertiserId,
                siteId: $siteId,
                campaignName: $study->campaign_name,
                creativeName: $study->campaign_name.' Creative',
                creatives: $creatives,
                market: $study->market,
                clientName: $study->client_name ?? 'Client',
                backupImageBase64: null
            );

            if ($result['success']) {
                $campaignId = null;
                if (! empty($result['results'])) {
                    $campaignId = $result['results'][0]['campaign_id'] ?? null;
                }

                $study->update([
                    'cm360_profile_id' => $profileId,
                    'cm360_advertiser_id' => $advertiserId,
                    'cm360_site_id' => $siteId,
                    'cm360_pushed' => true,
                    'cm360_pushed_at' => now(),
                    'cm360_campaign_id' => $campaignId ?: $study->cm360_campaign_id,
                    'status' => 'pushed',
                    'error_message' => null,
                    'cm360_tags' => json_encode($result['results']),
                ]);

                BrandliftEditLog::create([
                    'brandlift_study_id' => $study->id,
                    'user_id' => auth()->id(),
                    'changes_made' => [
                        'action' => 'retry_sync',
                        'description' => 'Sincronización con CM360 completada exitosamente desde el panel de resolución',
                        'user_name' => auth()->user()?->name ?? 'Usuario',
                    ],
                ]);

                return response()->json([
                    'success' => true,
                    'message' => '¡Sincronización completada exitosamente!',
                    'study' => $study->fresh(),
                ]);
            } else {
                $errMsg = $result['message'] ?? 'Falló la sincronización con Google Campaign Manager 360';
                $study->update([
                    'error_message' => $errMsg,
                    'status' => 'error',
                ]);

                return response()->json([
                    'success' => false,
                    'message' => $errMsg,
                ], 500);
            }
        } catch (\Exception $e) {
            $errMsg = 'Error de conexión con CM360: '.$e->getMessage();
            $study->update([
                'error_message' => $errMsg,
                'status' => 'error',
            ]);

            return response()->json([
                'success' => false,
                'message' => $errMsg,
            ], 500);
        }
    }

    /**
     * Vincular o generar hoja de cálculo de Google Sheets para un estudio.
     */
    public function linkSheet(Request $request, int $id, GoogleWorkspaceService $googleService): JsonResponse
    {
        $study = BrandliftStudy::findOrFail($id);

        // Validar permisos de mercado
        $user = auth()->user();
        if ($user && ! $user->isAdmin() && ! $user->hasMarket($study->market)) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permisos para modificar este estudio.',
            ], 403);
        }

        $sheetIdOrUrl = trim((string) ($request->input('sheet_id') ?? $request->input('sheet_url_or_id', '')));

        if (! empty($sheetIdOrUrl)) {
            // Extraer ID si es URL completa
            if (preg_match('/spreadsheets\/d\/([a-zA-Z0-9-_]+)/', $sheetIdOrUrl, $matches)) {
                $sheetId = $matches[1];
            } else {
                $sheetId = $sheetIdOrUrl;
            }
        } else {
            // Generar automáticamente
            try {
                $year = date('Y');
                $month = date('m');
                $clientName = str_replace(' ', '_', $study->client_name ?: 'Client');
                $fileName = "{$year}_{$month}_WMSCSLATAM_{$study->market}_{$clientName}_{$study->campaign_name}_brandlift_b";

                $marketFolderId = $googleService->findOrCreateMarketFolder($study->market);
                $sheetId = $googleService->duplicateTemplate($fileName, $marketFolderId);
            } catch (\Exception $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al crear la hoja de cálculo en Google Drive: '.$e->getMessage(),
                ], 500);
            }
        }

        $newStatus = $study->cm360_pushed ? 'pushed' : ($study->status === 'error' ? 'created' : $study->status);

        $study->update([
            'sheet_id' => $sheetId,
            'status' => $newStatus,
            'error_message' => null,
        ]);

        BrandliftEditLog::create([
            'brandlift_study_id' => $study->id,
            'user_id' => auth()->id(),
            'changes_made' => [
                'action' => 'link_sheet',
                'description' => "Hoja de cálculo vinculada: {$sheetId}",
                'user_name' => auth()->user()?->name ?? 'Usuario',
            ],
        ]);

        return response()->json([
            'success' => true,
            'message' => '¡Hoja de Google Sheets vinculada exitosamente!',
            'sheet_id' => $sheetId,
            'sheet_url' => "https://docs.google.com/spreadsheets/d/{$sheetId}",
            'study' => $study->fresh(),
        ]);
    }
}
