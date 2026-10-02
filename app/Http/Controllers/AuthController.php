<?php

namespace App\Http\Controllers;

use App\Mail\MagicLinkEmail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function sendMagicLink(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $user = User::where('email', $request->email)->first();

        if ($user) {
            if ($user->status === 'inactive') {
                return back()->withErrors(['email' => 'Tu cuenta está inactiva. Contacta al equipo de Creative Services.']);
            }

            // Generate a signed URL valid for 15 minutes
            $url = URL::temporarySignedRoute(
                'magic.login',
                now()->addMinutes(15),
                ['user' => $user->id]
            );

            // Send email
            Mail::to($user->email)->send(new MagicLinkEmail($url));

            return back()->with('success', 'Te hemos enviado un link de acceso a la plataforma');
        }

        return back()->withErrors(['email' => 'No estás registrado en la plataforma, contacta al equipo de Creative Services']);
    }

    public function loginWithMagicLink(Request $request, User $user)
    {
        if (! $request->hasValidSignature()) {
            abort(401, 'El enlace ha expirado o es inválido.');
        }

        if ($user->status === 'inactive') {
            abort(403, 'Tu cuenta se encuentra inactiva. Contacta al administrador.');
        }

        $user->update(['last_login_at' => now()]);

        Auth::login($user);

        return redirect()->intended('/dashboard');
    }

    public function loginWithToken(string $token)
    {
        $user = User::where('login_token', $token)->first();

        if (! $user) {
            return redirect()->route('login')->withErrors(['email' => 'El enlace o token de acceso directo es inválido.']);
        }

        if ($user->status === 'inactive') {
            return redirect()->route('login')->withErrors(['email' => 'Tu cuenta se encuentra inactiva. Contacta al administrador.']);
        }

        $user->update(['last_login_at' => now()]);

        Auth::login($user);

        return redirect()->intended('/dashboard');
    }

    public function switchMarket(Request $request)
    {
        $market = $request->input('market');
        $user = auth()->user();

        if ($market && $user && ! $user->hasMarket($market) && ! $user->isAdmin()) {
            return response()->json(['success' => false, 'message' => 'Mercado no autorizado.'], 403);
        }

        session(['active_market' => $market ?: null]);

        return response()->json([
            'success' => true,
            'active_market' => session('active_market'),
        ]);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}
