<?php

namespace App\Services;

use App\Models\Venta;
use App\Exceptions\DianException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DianService
{
    private string $baseUrl;
    private array $credentials;

    public function __construct()
    {
        $mode = config('dian.mode', 'homologacion');
        $config = config("dian.{$mode}");
        
        $this->baseUrl = $config['url'];
        $this->credentials = [
            'username' => $config['username'],
            'password' => $config['password'],
        ];
    }

    public function enviarFactura(Venta $venta): array
    {
        try {
            // Preparar payload para DIAN
            $payload = $this->prepararPayloadFactura($venta);

            // Enviar a DIAN
            $response = Http::timeout(30)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Basic ' . base64_encode($this->credentials['username'] . ':' . $this->credentials['password']),
                ])
                ->post($this->baseUrl . '/facturas', $payload);

            if ($response->successful()) {
                $data = $response->json();
                
                // Actualizar venta con respuesta DIAN
                $venta->update([
                    'estado_dian' => Venta::ESTADO_DIAN_ACEPTADA,
                    'cufe' => $data['cufe'] ?? null,
                    'qr_code' => $data['qr_code'] ?? null,
                ]);

                return $data;
            } else {
                $error = $response->json();
                throw new DianException(
                    $error['message'] ?? 'Error en la comunicación con DIAN',
                    $venta->id,
                    $error['code'] ?? 'UNKNOWN_ERROR',
                    $response->status()
                );
            }

        } catch (\Exception $e) {
            Log::error('Error enviando factura a DIAN', [
                'venta_id' => $venta->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            $venta->update([
                'estado_dian' => Venta::ESTADO_DIAN_RECHAZADA,
            ]);

            throw new DianException(
                $e->getMessage(),
                $venta->id,
                'DIAN_ERROR',
                $e->getCode() ?: 500
            );
        }
    }

    private function prepararPayloadFactura(Venta $venta): array
    {
        // Este es un ejemplo básico. En producción, se debe seguir 
        // el estándar UBL 2.1 de la DIAN para Colombia
        return [
            'tipo_documento' => '01', // Factura electrónica de venta
            'numero_factura' => $venta->numero_factura,
            'fecha_emision' => $venta->fecha_venta->format('Y-m-d'),
            'hora_emision' => $venta->created_at->format('H:i:s'),
            'cliente' => [
                'tipo_documento' => $venta->cliente->tipo_documento,
                'numero_documento' => $venta->cliente->numero_documento,
                'nombre' => $venta->cliente->nombre,
                'direccion' => $venta->cliente->direccion,
                'email' => $venta->cliente->email,
            ],
            'items' => $venta->detalles->map(function ($detalle) {
                return [
                    'codigo' => $detalle->vehiculo_id,
                    'descripcion' => $detalle->vehiculo->descripcion_completa,
                    'cantidad' => $detalle->cantidad,
                    'precio_unitario' => $detalle->precio_unitario,
                    'subtotal' => $detalle->subtotal,
                ];
            })->toArray(),
            'impuestos' => [
                'iva' => $venta->iva,
            ],
            'totales' => [
                'subtotal' => $venta->subtotal,
                'total' => $venta->total,
            ]
        ];
    }

    public function consultarEstadoFactura(string $cufe): array
    {
        $response = Http::timeout(30)
            ->withHeaders([
                'Authorization' => 'Basic ' . base64_encode($this->credentials['username'] . ':' . $this->credentials['password']),
            ])
            ->get($this->baseUrl . '/facturas/' . $cufe . '/estado');

        if ($response->successful()) {
            return $response->json();
        }

        throw new DianException(
            'Error consultando estado de factura',
            null,
            'CONSULTA_ERROR',
            $response->status()
        );
    }
}