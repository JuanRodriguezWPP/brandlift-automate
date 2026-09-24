<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Mail\MagicLinkEmail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function sendMagicLink(Request $request)
    {
        $request->validate([
            'email' => 'required|email'
        ]);

        $user = User::where('email', $request->email)->first();

        if ($user) {
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

        return back()->withErrors(['email' => 'No estas registrado en la plataforma, contacta al equipo de Creative Services']);
    }

    public function loginWithMagicLink(Request $request, User $user)
    {
        if (! $request->hasValidSignature()) {
            abort(401, 'El enlace ha expirado o es inválido.');
        }

        Auth::login($user);

        return redirect()->intended('/dashboard');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}
