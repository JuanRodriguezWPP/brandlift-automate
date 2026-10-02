<?php

namespace App\Http\Controllers;

use App\Models\BrandliftStudy;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReportController extends Controller
{
    /**
     * Display reports and analytics dashboard.
     */
    public function index(Request $request): View
    {
        $user = auth()->user();
        $query = BrandliftStudy::with(['questions', 'creatives', 'tags']);

        $activeMarket = session('active_market');

        // Only show campaigns for the markets associated with the user profile
        if ($user && ! $user->isAdmin()) {
            $assigned = $user->assigned_markets;
            if (! empty($assigned)) {
                $query->whereIn('market', $assigned);
            }
        } elseif ($market = $request->input('market')) {
            $query->where('market', $market);
        } elseif ($user && $user->isAdmin() && ! empty($activeMarket)) {
            $query->where('market', $activeMarket);
        }

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('campaign_name', 'LIKE', "%{$search}%")
                    ->orWhere('client_name', 'LIKE', "%{$search}%");
            });
        }

        if ($status = $request->input('status')) {
            if ($status === 'active') {
                $query->where(function ($q) {
                    $q->where('cm360_pushed', true)
                        ->orWhereIn('status', ['pushed', 'cm360_pushed']);
                })->where(function ($q) {
                    $q->whereNull('end_date')
                        ->orWhereDate('end_date', '>=', now()->toDateString());
                });
            } elseif ($status === 'finished') {
                $query->where(function ($q) {
                    $q->where('cm360_pushed', true)
                        ->orWhereIn('status', ['pushed', 'cm360_pushed']);
                })->whereNotNull('end_date')
                    ->whereDate('end_date', '<', now()->toDateString());
            } elseif ($status === 'pushed') {
                $query->where(function ($q) {
                    $q->where('cm360_pushed', true)
                        ->orWhereIn('status', ['pushed', 'cm360_pushed']);
                });
            } else {
                $query->where('status', $status);
            }
        }

        if ($dateFrom = $request->input('date_from')) {
            $query->where(function ($q) use ($dateFrom) {
                $q->whereDate('end_date', '>=', $dateFrom)
                    ->orWhere(function ($sub) use ($dateFrom) {
                        $sub->whereNull('end_date')->whereDate('created_at', '>=', $dateFrom);
                    });
            });
        }

        if ($dateTo = $request->input('date_to')) {
            $query->where(function ($q) use ($dateTo) {
                $q->whereDate('end_date', '<=', $dateTo)
                    ->orWhere(function ($sub) use ($dateTo) {
                        $sub->whereNull('end_date')->whereDate('created_at', '<=', $dateTo);
                    });
            });
        }

        $allStudies = (clone $query)->latest()->get()->unique('campaign_name')->values();

        // Calculate KPIs
        $totalStudies = $allStudies->count();
        $totalPushed = $allStudies->filter(fn ($s) => $s->cm360_pushed || in_array($s->status, ['pushed', 'cm360_pushed']))->count();
        $totalQuestions = $allStudies->sum('question_count');
        $totalCreatives = $allStudies->sum(fn ($s) => $s->creatives->count());

        // Top 5 Clientes con más BrandLift
        $topClients = $allStudies
            ->filter(fn ($s) => ! empty($s->client_name))
            ->groupBy(fn ($s) => trim((string) $s->client_name))
            ->map(function ($group, $client) use ($totalStudies) {
                $count = $group->count();
                $pushed = $group->filter(fn ($s) => $s->cm360_pushed || in_array($s->status, ['pushed', 'cm360_pushed']))->count();

                return [
                    'name' => $client,
                    'count' => $count,
                    'pushed' => $pushed,
                    'percentage' => $totalStudies > 0 ? round(($count / $totalStudies) * 100) : 0,
                ];
            })
            ->sortByDesc('count')
            ->take(5)
            ->values();

        // Market distribution
        $marketDistribution = $allStudies->groupBy('market')->map(function ($group, $market) {
            $first = $group->first();

            return [
                'code' => $market,
                'name' => $first ? $first->market_name : $market,
                'count' => $group->count(),
                'pushed' => $group->where('cm360_pushed', true)->count(),
            ];
        })->sortByDesc('count')->values();

        // DSP Usage breakdown
        $dspCounts = [
            'DV360' => 0,
            'TTD' => 0,
            'Sonata' => 0,
            'Amazon' => 0,
        ];

        foreach ($allStudies as $study) {
            $tags = (array) ($study->dps_tags ?? []);
            foreach ($tags as $tag) {
                $tagUpper = strtoupper((string) $tag);
                if (isset($dspCounts[$tagUpper])) {
                    $dspCounts[$tagUpper]++;
                } elseif (str_contains($tagUpper, 'DV360')) {
                    $dspCounts['DV360']++;
                } elseif (str_contains($tagUpper, 'TTD') || str_contains($tagUpper, 'TRADE')) {
                    $dspCounts['TTD']++;
                } elseif (str_contains($tagUpper, 'SONATA')) {
                    $dspCounts['Sonata']++;
                } elseif (str_contains($tagUpper, 'AMAZON')) {
                    $dspCounts['Amazon']++;
                }
            }
        }

        // Status breakdown
        $statusCounts = [
            'pushed' => $allStudies->where('status', 'pushed')->count(),
            'created' => $allStudies->where('status', 'created')->count(),
            'error' => $allStudies->where('status', 'error')->count(),
        ];

        // Available markets list for filter dropdown
        $availableMarkets = [
            'PE' => 'Perú (PE)',
            'PRI' => 'Puerto Rico (PRI)',
            'ARG' => 'Argentina (ARG)',
            'MIA' => 'Miami (MIA)',
            'MEX' => 'México (MEX)',
            'CHL' => 'Chile (CHL)',
            'COL' => 'Colombia (COL)',
            'ECU' => 'Ecuador (ECU)',
        ];

        if ($user && ! $user->isAdmin()) {
            $assigned = $user->assigned_markets;
            if (! empty($assigned)) {
                $availableMarkets = array_intersect_key($availableMarkets, array_flip($assigned));
            }
        }

        return view('reports', compact(
            'allStudies',
            'totalStudies',
            'totalPushed',
            'totalQuestions',
            'totalCreatives',
            'topClients',
            'marketDistribution',
            'dspCounts',
            'statusCounts',
            'availableMarkets'
        ));
    }
}
