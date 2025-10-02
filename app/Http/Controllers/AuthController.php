<?php

namespace App\Http\Controllers;

use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;

class AuthController extends Controller
{
    public function __construct(private AuthService $authService) {}

    public function register(RegisterRequest $request): JsonResponse
    {
        try {
            $user = $this->authService->register($request->validated());
            
            return response()->json([
                'success' => true,
                'message' => 'Usuario registrado exitosamente',
                'data' => $user
            ], 201);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error en el registro: ' . $e->getMessage()
            ], 500);
        }
    }

    public function login(LoginRequest $request): JsonResponse
    {
        try {
            $token = $this->authService->login($request->validated());
            
            return response()->json([
                'success' => true,
                'data' => $token
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 401);
        }
    }

    public function logout(): JsonResponse
    {
        $this->authService->logout();
        
        return response()->json([
            'success' => true,
            'message' => 'Sesión cerrada exitosamente'
        ]);
    }

    public function refresh(): JsonResponse
    {
        $token = $this->authService->refresh();
        
        return response()->json([
            'success' => true,
            'data' => $token
        ]);
    }

    public function me(): JsonResponse
    {
        $user = $this->authService->getAuthenticatedUser();
        
        return response()->json([
            'success' => true,
            'data' => $user
        ]);
    }
}