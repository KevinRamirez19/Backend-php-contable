<?php

namespace App\Http\Controllers;

use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Services\AuthService;
use App\Traits\ApiResponser;
use Illuminate\Http\JsonResponse;

class AuthController extends Controller
{
    use ApiResponser;

    public function __construct(private AuthService $authService) {}

    public function register(RegisterRequest $request): JsonResponse
    {
        try {
            $user = $this->authService->register($request->validated());
            
            return $this->createdResponse($user, 'Usuario registrado exitosamente');
            
        } catch (\Exception $e) {
            return $this->errorResponse('Error en el registro: ' . $e->getMessage(), 500);
        }
    }

    public function login(LoginRequest $request): JsonResponse
    {
        try {
            $token = $this->authService->login($request->validated());
            
            return $this->successResponse($token, 'Login exitoso');
            
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 401);
        }
    }

    public function logout(): JsonResponse
    {
        try {
            $this->authService->logout();
            
            return $this->successResponse(null, 'Sesión cerrada exitosamente');
            
        } catch (\Exception $e) {
            return $this->errorResponse('Error al cerrar sesión: ' . $e->getMessage(), 500);
        }
    }

    public function refresh(): JsonResponse
    {
        try {
            $token = $this->authService->refresh();
            
            return $this->successResponse($token, 'Token refrescado exitosamente');
            
        } catch (\Exception $e) {
            return $this->errorResponse('Error al refrescar token: ' . $e->getMessage(), 401);
        }
    }

    public function me(): JsonResponse
    {
        try {
            $user = $this->authService->getAuthenticatedUser();
            
            return $this->successResponse($user, 'Datos del usuario');
            
        } catch (\Exception $e) {
            return $this->errorResponse('Error al obtener datos del usuario: ' . $e->getMessage(), 401);
        }
    }
}