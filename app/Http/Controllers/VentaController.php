<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreVentaRequest;
use App\Http\Resources\VentaResource;
use App\Models\Venta;
use App\Services\VentaService;
use App\Traits\ApiResponser;
use Illuminate\Http\JsonResponse;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class VentaController extends Controller
{
    use ApiResponser;

    private VentaService $ventaService;

    public function __construct(VentaService $ventaService)
    {
        $this->ventaService = $ventaService;
        $this->middleware('auth:api')->except([
            'descargarFacturaPDF',
            'descargarFacturaXML'
        ]);
    }

    // Listar ventas
    public function index(): JsonResponse
    {
        try {
            $ventas = Venta::with(['cliente', 'detalles.vehiculo'])->get();
            return $this->successResponse(
                VentaResource::collection($ventas),
                'Listado de ventas obtenido exitosamente'
            );
        } catch (\Exception $e) {
            return $this->errorResponse('Error al obtener ventas: ' . $e->getMessage(), 500);
        }
    }

    public function store(StoreVentaRequest $request): JsonResponse
{
    Log::info('🟢 Iniciando registro de venta', ['data' => $request->all()]);

    try {
        // Crear la venta usando el servicio
        $venta = $this->ventaService->crearVenta($request->validated());
        Log::info('✅ Venta creada correctamente', ['venta_id' => $venta->id ?? null]);

        // Calcular CUFE
        $datosCufe = $venta->numero_factura
            . $venta->fecha_venta
            . '900123456'
            . $venta->cliente->documento
            . $venta->total
            . $venta->iva;

        $venta->cufe = hash('sha384', $datosCufe);
        $venta->save();
        Log::info('🔢 CUFE generado y guardado', ['cufe' => $venta->cufe]);

        // 🚫 Eliminado: creación automática de asiento contable
        Log::warning('⚠️ Asiento contable NO generado automáticamente. Deberá crearse manualmente desde el módulo contable.', [
            'venta_id' => $venta->id,
            'numero_factura' => $venta->numero_factura
        ]);

        return $this->createdResponse(
            new VentaResource($venta),
            'Venta registrada exitosamente (sin asiento contable automático)'
        );
    } catch (\Exception $e) {
        Log::error('❌ Error al registrar venta', [
            'mensaje' => $e->getMessage(),
            'archivo' => $e->getFile(),
            'linea' => $e->getLine(),
            'trace' => $e->getTraceAsString()
        ]);

        return response()->json([
            'error' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
        ], 500);
    }
}


    // Mostrar detalle de venta
    public function show(int $id): JsonResponse
    {
        try {
            $venta = Venta::with(['cliente', 'detalles.vehiculo'])->find($id);
            if (!$venta) return $this->errorResponse('Venta no encontrada', 404);

            return $this->successResponse(new VentaResource($venta), 'Detalle de la venta obtenido exitosamente');
        } catch (\Exception $e) {
            return $this->errorResponse('Error al obtener la venta: ' . $e->getMessage(), 500);
        }
    }

    // Descargar factura PDF
    public function descargarFacturaPDF(int $id)
    {
        $venta = Venta::with(['cliente', 'detalles.vehiculo'])->find($id);
        if (!$venta) return $this->errorResponse('Venta no encontrada', 404);

        if (request()->has('token') && !Auth::guard('api')->user()) {
            return $this->errorResponse('Token inválido', 401);
        }

        $pdf = Pdf::loadView('facturas.factura', ['venta' => $venta])
            ->setPaper('a4', 'portrait');

        return $pdf->stream('factura_' . $venta->numero_factura . '.pdf');
    }

    // Descargar factura XML UBL 2.1 DIAN
    public function descargarFacturaXML(int $id)
    {
        $venta = Venta::with(['cliente', 'detalles.vehiculo'])->find($id);
        if (!$venta) return $this->errorResponse('Venta no encontrada', 404);

        if (request()->has('token') && !Auth::guard('api')->user()) {
            return $this->errorResponse('Token inválido', 401);
        }

        $xml = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><Invoice></Invoice>');
        $xml->addAttribute('xmlns', 'urn:oasis:names:specification:ubl:schema:xsd:Invoice-2');
        $xml->addChild('cbc:ID', $venta->numero_factura);
        $xml->addChild('cbc:IssueDate', $venta->fecha_venta);
        $xml->addChild('cbc:InvoiceTypeCode', '01');
        $xml->addChild('cbc:DocumentCurrencyCode', 'COP');

        // Emisor
        $supplier = $xml->addChild('cac:AccountingSupplierParty');
        $party = $supplier->addChild('cac:Party');
        $party->addChild('cbc:RegistrationName', 'AUTOMORATA S.A.S.');
        $party->addChild('cbc:CompanyID', '900123456');

        // Cliente
        $customer = $xml->addChild('cac:AccountingCustomerParty');
        $cparty = $customer->addChild('cac:Party');
        $cparty->addChild('cbc:Name', $venta->cliente->nombre);
        $cparty->addChild('cbc:CompanyID', $venta->cliente->documento);

        // Detalles
        foreach ($venta->detalles as $detalle) {
            $line = $xml->addChild('cac:InvoiceLine');
            $line->addChild('cbc:ID', $detalle->id);
            $line->addChild('cbc:InvoicedQuantity', $detalle->cantidad);
            $line->addChild('cbc:LineExtensionAmount', $detalle->subtotal);
            $item = $line->addChild('cac:Item');
            $item->addChild('cbc:Description', $detalle->vehiculo->marca . ' ' . $detalle->vehiculo->modelo);
            $price = $line->addChild('cac:Price');
            $price->addChild('cbc:PriceAmount', $detalle->precio_unitario);
        }

        $total = $xml->addChild('cac:LegalMonetaryTotal');
        $total->addChild('cbc:LineExtensionAmount', $venta->subtotal);
        $total->addChild('cbc:TaxInclusiveAmount', $venta->total);
        $total->addChild('cbc:PayableAmount', $venta->total);

        $xml->addChild('cbc:UUID', $venta->cufe);

        $filename = 'factura_' . $venta->numero_factura . '.xml';
        return Response::make($xml->asXML(), 200, [
            'Content-Type' => 'application/xml',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"'
        ]);
    }
}
