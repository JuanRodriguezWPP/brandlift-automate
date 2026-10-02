<?php

namespace App\Http\Controllers;

use App\Models\BrandliftStudy;
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
        $query = BrandliftStudy::with('questions', 'creatives', 'tags', 'editLogs.user');

        $activeMarket = session('active_market');

        // Check if user is not admin, restrict by their assigned markets
        if ($user && ! $user->isAdmin()) {
            $assignedMarkets = $user->assigned_markets;
            if (! empty($assignedMarkets)) {
                $query->whereIn('market', $assignedMarkets);
            }
        } elseif ($user && $user->isAdmin() && ! empty($activeMarket)) {
            // Admin is simulating a specific market
            $query->where('market', $activeMarket);
        }

        // Search by campaign name
        if ($search = $request->input('search')) {
            $query->where('campaign_name', 'LIKE', "%{$search}%");
        }

        // Filter by market (only if they are an admin or it matches their assigned markets)
        if ($market = $request->input('market')) {
            if (! $user || $user->hasMarket($market) || $user->isAdmin()) {
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
        $allStudies = BrandliftStudy::query();
        if ($user && ! $user->isAdmin()) {
            $assignedMarkets = $user->assigned_markets;
            if (! empty($assignedMarkets)) {
                $allStudies->whereIn('market', $assignedMarkets);
            }
        } elseif ($user && $user->isAdmin() && ! empty($activeMarket)) {
            $allStudies->where('market', $activeMarket);
        }

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
        $study = BrandliftStudy::with(['questions', 'creatives', 'tags', 'editLogs.user'])->findOrFail($id);
        $user = auth()->user();

        if ($user && ! $user->hasMarket($study->market)) {
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
     * API: Delete a brandlift study and its associated Google Sheet.
     */
    public function destroy(int $id): JsonResponse
    {
        $user = auth()->user();
        $study = BrandliftStudy::findOrFail($id);

        if ($user && ! $user->hasMarket($study->market)) {
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
