<?php

use App\Http\Controllers\BrandliftController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/dashboard');
});

// Autenticación por Magic Link
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'sendMagicLink'])->name('login.send');
Route::get('/magic-login/{user}', [AuthController::class, 'loginWithMagicLink'])->name('magic.login');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Rutas Públicas de API
// API: Receive answers from creatives (Bypasses CSRF because it's called from an external iframe)
Route::post('/api/brandlift/submit', [BrandliftController::class, 'submitResponse'])
    ->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class]);

// Rutas Protegidas por Autenticación
Route::middleware('auth')->group(function () {
    
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Brandlift Creator
    Route::get('/brandlift', [BrandliftController::class, 'index'])->name('brandlift');

    // Gestión de Usuarios (Solo Admin)
    Route::middleware(\App\Http\Middleware\AdminMiddleware::class)->group(function () {
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
        Route::post('/users', [UserController::class, 'store'])->name('users.store');
        Route::get('/users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
        Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
        Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
    });

    // API Privadas de Brandlift
    Route::post('/api/brandlift/store', [BrandliftController::class, 'store']);
    Route::post('/api/brandlift/push-to-cm360', [BrandliftController::class, 'pushToCM360']);
    Route::post('/api/brandlift/automate-sheet', [BrandliftController::class, 'automateSheet']);
    
    Route::get('/api/brandlift/history', [DashboardController::class, 'apiList']);
    Route::get('/api/brandlift/history/{id}', [DashboardController::class, 'show']);
    Route::delete('/api/brandlift/history/{id}', [DashboardController::class, 'destroy']);
    Route::post('/api/brandlift/history/{id}/remove-click', [DashboardController::class, 'removeClickEvent']);

    // API Privadas de CM360
    Route::get('/api/cm360/profiles', function (App\Services\CampaignManagerService $cmService) {
        try {
            $profilesResponse = $cmService->getUserProfiles();
            $profiles = $profilesResponse['items'] ?? [];
            return response()->json(collect($profiles)->map(fn($p) => ['id' => $p['profileId'], 'name' => $p['userName']])->toArray());
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    });

    Route::get('/api/cm360/advertisers/{profileId}', function ($profileId, App\Services\CampaignManagerService $cmService) {
        try {
            $advertisersResponse = $cmService->getAdvertisers($profileId);
            $advertisers = $advertisersResponse['advertisers'] ?? [];
            return response()->json(collect($advertisers)->map(fn($a) => ['id' => $a['id'], 'name' => $a['name']])->toArray());
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    });

    Route::get('/api/cm360/sites/{profileId}', function ($profileId, App\Services\CampaignManagerService $cmService) {
        try {
            $sitesResponse = $cmService->getSites($profileId);
            $sites = $sitesResponse['sites'] ?? [];
            return response()->json(collect($sites)->map(fn($s) => ['id' => $s['id'], 'name' => $s['name']])->toArray());
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    });
});
