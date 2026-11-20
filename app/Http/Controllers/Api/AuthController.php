<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ApiToken;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'password' => 'required|string|min:6',
            'phone' => 'nullable|string|max:60',
            'company' => 'nullable|string|max:255',
            'locale' => 'nullable|string|in:es,en,de,it,fr,pt,nl',
        ]);

        if (Customer::where('email', $data['email'])->exists()
            || User::where('email', $data['email'])->exists()) {
            return response()->json(['message' => 'Este correo ya está registrado.'], 422);
        }

        $customer = Customer::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'phone' => $data['phone'] ?? null,
            'company' => $data['company'] ?? null,
            'locale' => $data['locale'] ?? 'es',
        ]);

        $plain = $this->issueToken($customer, 'mobile');

        return response()->json([
            'token' => $plain,
            'user' => $this->userData($customer, 'customer'),
        ], 201);
    }

    public function login(Request $request)
    {
        $data = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $user = Customer::where('email', $data['email'])->first();

        if ($user && Hash::check($data['password'], $user->password)) {
            $type = 'customer';
        } else {
            $admin = User::where('email', $data['email'])->first();
            if ($admin && Hash::check($data['password'], $admin->password)) {
                $user = $admin;
                $type = 'admin';
            }
        }

        if (! isset($user)) {
            return response()->json(['message' => 'Credenciales incorrectas.'], 401);
        }

        $plain = $this->issueToken($user, 'mobile');

        return response()->json([
            'token' => $plain,
            'user' => $this->userData($user, $type),
        ]);
    }

    public function logout(Request $request)
    {
        $token = $request->attributes->get('api_token');
        if ($token) {
            $token->delete();
        }

        return response()->json(['message' => 'Sesión cerrada.']);
    }

    public function me(Request $request)
    {
        $user = $request->user();
        $type = $user instanceof Customer ? 'customer' : 'admin';

        return response()->json(['user' => $this->userData($user, $type)]);
    }

    protected function issueToken($user, string $name): string
    {
        $plain = Str::random(60);

        ApiToken::create([
            'tokenable_type' => get_class($user),
            'tokenable_id' => $user->id,
            'name' => $name,
            'token' => hash('sha256', $plain),
            'abilities' => '*',
            'expires_at' => now()->addDays(60),
        ]);

        return $plain;
    }

    protected function userData($user, string $type): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone ?? null,
            'company' => $user->company ?? null,
            'type' => $type,
        ];
    }
}
