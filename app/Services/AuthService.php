<?php

namespace App\Services;

use App\Models\User;
use App\Models\Rol;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthService
{
    public function register(array $data): User
    {
        return DB::transaction(function () use ($data) {
            // Crear usuario
            $user = User::create([
                'nombre' => $data['nombre'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'activo' => true,
            ]);

            // Asignar rol (por defecto vendedor si no se especifica)
            $rolNombre = $data['rol'] ?? 'vendedor';
            $rol = Rol::where('nombre', $rolNombre)->firstOrFail();
            
            $user->roles()->attach($rol);

            return $user->load('roles');
        });
    }

    public function login(array $credentials): array
    {
        $token = JWTAuth::attempt([
            'email' => $credentials['email'],
            'password' => $credentials['password'],
            'activo' => true
        ]);

        if (!$token) {
            throw new \Exception('Credenciales inválidas o usuario inactivo');
        }

        $user = JWTAuth::user();

        return [
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => JWTAuth::factory()->getTTL() * 60,
            'user' => $user->load('roles')
        ];
    }

    public function logout(): void
    {
        JWTAuth::logout();
    }

    public function refresh(): array
    {
        $token = JWTAuth::refresh();
        
        return [
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => JWTAuth::factory()->getTTL() * 60
        ];
    }

    public function getAuthenticatedUser(): User
    {
        return JWTAuth::user()->load('roles');
    }
}