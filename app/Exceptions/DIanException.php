<?php

namespace App\Exceptions;

use Exception;

class DianException extends Exception
{
    protected $ventaId;
    protected $codigoError;
    protected $dianResponse;

    public function __construct(
        string $message = "", 
        ?int $ventaId = null, 
        ?string $codigoError = null, 
        int $code = 0, 
        ?array $dianResponse = null, 
        Exception $previous = null
    ) {
        parent::__construct($message, $code, $previous);
        
        $this->ventaId = $ventaId;
        $this->codigoError = $codigoError;
        $this->dianResponse = $dianResponse;
    }

    public function getVentaId(): ?int
    {
        return $this->ventaId;
    }

    public function getCodigoError(): ?string
    {
        return $this->codigoError;
    }

    public function getDianResponse(): ?array
    {
        return $this->dianResponse;
    }

    public function context(): array
    {
        return [
            'venta_id' => $this->ventaId,
            'codigo_error' => $this->codigoError,
            'dian_response' => $this->dianResponse,
            'timestamp' => now()->toISOString(),
        ];
    }

    public function render($request)
    {
        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => $this->getMessage(),
                'error_code' => $this->codigoError,
                'venta_id' => $this->ventaId,
            ], $this->getCode() ?: 500);
        }

        return parent::render($request);
    }
}