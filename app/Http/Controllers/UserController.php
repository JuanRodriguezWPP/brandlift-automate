<?php

namespace App\Http\Controllers;

use App\Mail\WelcomeUserMail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index()
    {
        $users = User::latest()->get();

        return view('users.index', compact('users'));
    }

    public function create()
    {
        return view('users.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'role' => 'required|in:admin,local,diseñador,mercado',
            'markets' => 'required_if:role,local,mercado|nullable|array',
            'markets.*' => 'string|max:10',
            'status' => 'nullable|in:active,inactive',
        ]);

        $markets = in_array($request->role, ['local', 'mercado']) ? array_values(array_filter((array) $request->input('markets', []))) : null;

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role === 'mercado' ? 'local' : $request->role,
            'markets' => $markets,
            'market' => $markets ? ($markets[0] ?? null) : null,
            'status' => $request->input('status', 'active'),
            'login_token' => Str::random(32),
            'password' => null, // Magic link / token doesn't use password
        ]);

        try {
            Mail::to($user->email)->send(new WelcomeUserMail($user));
        } catch (\Exception $e) {
            Log::error('Error enviando correo de bienvenida: '.$e->getMessage());
        }

        return redirect()->route('users.index')->with('success', 'Usuario creado exitosamente.');
    }

    public function edit(User $user)
    {
        return view('users.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users')->ignore($user->id)],
            'role' => 'required|in:admin,local,diseñador,disenador,mercado',
            'markets' => 'required_if:role,local,mercado|nullable|array',
            'markets.*' => 'string|max:10',
            'status' => 'nullable|in:active,inactive',
        ]);

        $markets = in_array($request->role, ['local', 'mercado']) ? array_values(array_filter((array) $request->input('markets', []))) : null;

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
            'role' => in_array($request->role, ['local', 'mercado']) ? 'local' : $request->role,
            'markets' => $markets,
            'market' => $markets ? ($markets[0] ?? null) : null,
            'status' => $request->input('status', 'active'),
            'login_token' => $user->login_token ?: Str::random(32),
        ]);

        return redirect()->route('users.index')->with('success', 'Usuario actualizado exitosamente.');
    }

    public function regenerateToken(User $user)
    {
        $user->update([
            'login_token' => Str::random(32),
        ]);

        return redirect()->route('users.index')->with('success', 'Token de ingreso regenerado exitosamente.');
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->withErrors('No puedes eliminarte a ti mismo.');
        }

        $user->delete();

        return redirect()->route('users.index')->with('success', 'Usuario eliminado exitosamente.');
    }
}
