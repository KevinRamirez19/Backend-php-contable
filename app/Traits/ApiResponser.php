<?php

namespace App\Traits;

trait ApiResponser
{
    protected function successResponse($data, $message = null, $code = 200)
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data
        ], $code);
    }

    protected function errorResponse($message = null, $code = 400)
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'data' => null
        ], $code);
    }

    protected function createdResponse($data, $message = 'Recurso creado exitosamente')
    {
        return $this->successResponse($data, $message, 201);
    }

    protected function notFoundResponse($message = 'Recurso no encontrado')
    {
        return $this->errorResponse($message, 404);
    }

    protected function unauthorizedResponse($message = 'No autorizado')
    {
        return $this->errorResponse($message, 401);
    }

    protected function validationErrorResponse($errors, $message = 'Error de validación')
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'errors' => $errors
        ], 422);
    }
}