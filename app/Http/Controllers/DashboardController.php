<?php

namespace App\Http\Controllers;

use App\Models\BrandliftEditLog;
use App\Models\BrandliftStudy;
use App\Models\User;
use App\Services\CampaignManagerService;
use App\Services\GoogleWorkspaceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * DashboardController
 *
 * Serves the Brandlift history dashboard and provides API endpoints
 * for listing, filtering, viewing, and deleting brandlift records.
 */
class DashboardController extends Controller
{
    /**
     * Show the dashboard view.
     */
    public function index()
    {
        return view('dashboard');
    }

    /**
     * API: List brandlift studies with filters and pagination.
     *
     * Query params:
     * - search: search by campaign name
     * - market: filter by market code
     * - status: filter by status (created, pushed, error)
     * - date_from: filter from date (Y-m-d)
     * - date_to: filter to date (Y-m-d)
     * - per_page: items per page (default 15)
     * - page: page number
     */
    public function apiList(Request $request): JsonResponse
    {
        $user = auth()->user();
        $query = BrandliftStudy::with('questions', 'creatives', 'tags', 'editLogs.user', 'creator');

        $activeMarket = session('active_market');

        // Restrict visibility by role, market, creator, and assigned_users (OOO follow-up)
        $query->visibleTo($user, $activeMarket);

        // Search by campaign name
        if ($search = $request->input('search')) {
            $query->where('campaign_name', 'LIKE', "%{$search}%");
        }

        // Filter by market (only if they have global access or it matches their assigned markets)
        if ($market = $request->input('market')) {
            if (! $user || $user->hasMarket($market) || $user->hasGlobalAccess()) {
                $query->where('market', $market);
            }
        }

        // Filter by status
        if ($status = $request->input('status')) {
            $query->status($status);
        }

        // Filter by date range
        if ($dateFrom = $request->input('date_from')) {
            $query->whereDate('created_at', '>=', $dateFrom);
        }
        if ($dateTo = $request->input('date_to')) {
            $query->whereDate('created_at', '<=', $dateTo);
        }

        // Stats for KPIs (before pagination)
        $allStudies = BrandliftStudy::query()->visibleTo($user, $activeMarket);

        $stats = [
            'total' => $allStudies->count(),
            'pushed' => (clone $allStudies)->where('cm360_pushed', true)->count(),
            'active' => (clone $allStudies)->whereDate('end_date', '>=', now())->count(),
            'this_month' => (clone $allStudies)->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count(),
            'markets' => (clone $allStudies)->selectRaw('market, COUNT(*) as count')
                ->groupBy('market')
                ->orderByDesc('count')
                ->limit(5)
                ->pluck('count', 'market')
                ->toArray(),
        ];

        // Paginate
        $perPage = min((int) $request->input('per_page', 15), 50);
        $studies = $query->orderByDesc('created_at')->paginate($perPage);

        return response()->json([
            'stats' => $stats,
            'studies' => $studies,
        ]);
    }

    /**
     * API: Get a single brandlift study with its questions.
     */
    public function show(int $id): JsonResponse
    {
        $study = BrandliftStudy::with(['questions', 'creatives', 'tags', 'editLogs.user', 'creator'])->findOrFail($id);
        $user = auth()->user();

        if ($user && ! $study->canBeAccessedBy($user)) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permisos para ver este Brandlift.',
            ], 403);
        }

        return response()->json([
            'study' => $study,
        ]);
    }

    /**
     * API: List users eligible to be assigned in copy (OOO / follow-up) for a study.
     * - Local users only see active profiles belonging to the study's country/market.
     * - Admin and Designer roles see all active profiles across all markets.
     */
    public function assignableUsers(int $id): JsonResponse
    {
        $study = BrandliftStudy::findOrFail($id);
        $user = auth()->user();

        if ($user && ! $study->canBeAccessedBy($user)) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permisos para gestionar el seguimiento de este Brandlift.',
            ], 403);
        }

        $studyMarket = $study->market;
        $hasGlobal = $user && $user->hasGlobalAccess();

        $allActiveUsers = User::where(function ($q) {
            $q->where('status', 'active')->orWhereNull('status');
        })->orderBy('name')->get();

        $eligible = $allActiveUsers->filter(function (User $candidate) use ($study, $studyMarket, $hasGlobal) {
            // Exclude the original creator since they already own the study
            if ($study->created_by && (int) $candidate->id === (int) $study->created_by) {
                return false;
            }

            // Admin and Designer see all users
            if ($hasGlobal) {
                return true;
            }

            // Country separation for local profiles: only show non-global users assigned to the study's country
            if ($candidate->hasGlobalAccess()) {
                return false;
            }

            return in_array($studyMarket, $candidate->assigned_markets, true);
        })->sortBy(function (User $candidate) use ($studyMarket) {
            // Sort users matching the study's market first, then alphabetically
            $matchesMarket = in_array($studyMarket, $candidate->assigned_markets, true) ? 0 : 1;

            return $matchesMarket.'_'.strtolower($candidate->name);
        })->values()->map(function (User $candidate) use ($studyMarket) {
            return [
                'id' => $candidate->id,
                'name' => $candidate->name,
                'email' => $candidate->email,
                'role' => $candidate->role,
                'markets' => $candidate->assigned_markets,
                'matches_study_market' => in_array($studyMarket, $candidate->assigned_markets, true),
            ];
        });

        return response()->json([
            'success' => true,
            'study_id' => $study->id,
            'campaign_name' => $study->campaign_name,
            'market' => $study->market,
            'market_name' => $study->market_name,
            'created_by' => $study->created_by,
            'creator_name' => $study->creator_name,
            'assigned_users' => array_values(array_map('intval', $study->assigned_users ?? [])),
            'users' => $eligible,
        ]);
    }

    /**
     * API: Save assigned users in copy (OOO / follow-up) for a study.
     */
    public function assignUsers(Request $request, int $id): JsonResponse
    {
        $study = BrandliftStudy::findOrFail($id);
        $user = auth()->user();

        if ($user && ! $study->canBeAccessedBy($user)) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permisos para asignar usuarios a este Brandlift.',
            ], 403);
        }

        $validated = $request->validate([
            'user_ids' => 'nullable|array',
            'user_ids.*' => 'integer|exists:users,id',
        ]);

        $requestedIds = array_values(array_unique(array_map('intval', $validated['user_ids'] ?? [])));
        $hasGlobal = $user && $user->hasGlobalAccess();

        if (! empty($requestedIds)) {
            $targetUsers = User::whereIn('id', $requestedIds)->get();

            if (! $hasGlobal) {
                foreach ($targetUsers as $targetUser) {
                    if (! in_array($study->market, $targetUser->assigned_markets, true)) {
                        return response()->json([
                            'success' => false,
                            'message' => "El usuario {$targetUser->name} no pertenece al mercado de este estudio ({$study->market}).",
                        ], 422);
                    }
                }
            }

            // Exclude creator if accidentally passed
            if ($study->created_by) {
                $requestedIds = array_values(array_filter(
                    $requestedIds,
                    fn (int $uid) => $uid !== (int) $study->created_by
                ));
            }
        }

        $study->update([
            'assigned_users' => $requestedIds,
        ]);

        $assignedNames = User::whereIn('id', $requestedIds)->pluck('name')->all();

        if ($user) {
            BrandliftEditLog::create([
                'brandlift_study_id' => $study->id,
                'user_id' => $user->id,
                'changes_made' => [
                    'action' => 'assign_users',
                    'assigned_user_ids' => $requestedIds,
                    'assigned_names' => $assignedNames,
                    'description' => empty($assignedNames)
                        ? 'Se removieron los usuarios asignados para seguimiento.'
                        : 'Seguimiento asignado a: '.implode(', ', $assignedNames),
                ],
            ]);
        }

        $study->load(['questions', 'creatives', 'tags', 'editLogs.user', 'creator']);

        return response()->json([
            'success' => true,
            'message' => empty($assignedNames)
                ? 'Lista de seguimiento actualizada (sin usuarios asignados).'
                : 'Seguimiento compartido con: '.implode(', ', $assignedNames),
            'study' => $study,
        ]);
    }

    /**
     * API: Delete a brandlift study and its associated Google Sheet.
     */
    public function destroy(int $id): JsonResponse
    {
        $user = auth()->user();
        $study = BrandliftStudy::findOrFail($id);

        if ($user && ! $study->canBeAccessedBy($user)) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permisos para eliminar este Brandlift.',
            ], 403);
        }

        // Delete Google Sheet if present
        if (! empty($study->sheet_id)) {
            try {
                $workspaceService = app(GoogleWorkspaceService::class);
                $workspaceService->deleteFile($study->sheet_id);
            } catch (\Throwable $e) {
                Log::warning("Could not delete Google Sheet {$study->sheet_id} during study deletion: ".$e->getMessage());
            }
        }

        $study->questions()->delete();
        $study->creatives()->delete();
        $study->editLogs()->delete();
        $study->tags()->delete();
        $study->delete();

        return response()->json([
            'success' => true,
            'message' => 'Brandlift y su hoja de Google Sheets eliminados correctamente.',
        ]);
    }

    /**
     * API: Remove click redirect event from a study's HTML and update CM360 if pushed.
     */
    public function removeClickEvent(int $id, CampaignManagerService $cmService): JsonResponse
    {
        $study = BrandliftStudy::with(['questions', 'creatives'])->findOrFail($id);
        $user = auth()->user();

        if ($user && ! $study->canBeAccessedBy($user)) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permisos para gestionar los hotspots de este Brandlift.',
            ], 403);
        }

        $hasCm360Data = $study->cm360_profile_id && $study->cm360_advertiser_id;
        $cm360Errors = [];
        $cm360SuccessCount = 0;

        // 1. Update Base Questions HTML (Local DB)
        foreach ($study->questions as $question) {
            if ($question->creative_html) {
                $html = $question->creative_html;
                $html = preg_replace('/var clickTag = ".*?";\s*/is', '', $html);
                $html = str_ireplace(' onclick="window.open(window.clickTag || clickTag, \'_blank\');"', '', $html);
                $question->update(['creative_html' => $html]);
            }
        }

        // 2. Update Creatives HTML (Local DB & CM360)
        foreach ($study->creatives as $creative) {
            if ($creative->creative_html) {
                $html = $creative->creative_html;
                $html = preg_replace('/var clickTag = ".*?";\s*/is', '', $html);
                $html = str_ireplace(' onclick="window.open(window.clickTag || clickTag, \'_blank\');"', '', $html);

                // Update local DB
                $creative->update(['creative_html' => $html]);

                // Update CM360 if applicable
                if ($study->cm360_pushed && $hasCm360Data && $creative->cm360_creative_id) {
                    try {
                        $creativeName = "{$study->campaign_name}_{$creative->variant_key}_{$study->creative_width}x{$study->creative_height}";
                        $cmService->updateCreativeHtml(
                            $study->cm360_profile_id,
                            $study->cm360_advertiser_id,
                            $creative->cm360_creative_id,
                            $html,
                            $creativeName
                        );
                        $cm360SuccessCount++;
                    } catch (\Exception $e) {
                        \Log::error("Failed to update CM360 creative {$creative->cm360_creative_id}", [
                            'error' => $e->getMessage(),
                        ]);
                        $cm360Errors[] = "Error en variante {$creative->variant_key}: {$e->getMessage()}";
                    }
                }
            }
        }

        $message = 'Redirección eliminada de la base de datos local.';
        if ($study->cm360_pushed) {
            if ($hasCm360Data) {
                $message .= " Se actualizaron {$cm360SuccessCount} creativos en CM360.";
                if (count($cm360Errors) > 0) {
                    $message .= ' Hubo errores en algunos: '.implode(', ', $cm360Errors);
                }
            } else {
                $message .= ' (No se pudo actualizar en CM360 porque este estudio es antiguo y no tiene datos de perfil).';
            }
        }

        return response()->json([
            'success' => empty($cm360Errors),
            'message' => $message,
            'cm360_errors' => $cm360Errors,
        ]);
    }

    /**
     * API: Toggle active / inactive status of a brandlift study and sync with CM360.
     */
    public function toggleStatus(int $id, CampaignManagerService $cmService, Request $request): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => 'No está permitido pausar o reactivar campañas desde la plataforma.',
        ], 403);
    }
}
