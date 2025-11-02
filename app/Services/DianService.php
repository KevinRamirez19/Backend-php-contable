<?php

namespace App\Services;

use App\Models\Venta;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DianService
{
    public function enviarFactura(Venta $venta): void
    {
        // 1️⃣ Generar el XML con formato DIAN
        $xml = $this->generarXmlFactura($venta);

        // 2️⃣ Guardar localmente (para auditoría o envío)
        $fileName = 'facturas/FE-' . $venta->id . '.xml';
        Storage::disk('local')->put($fileName, $xml);

        // 3️⃣ (Opcional) Enviar a la DIAN o simular la validación
        // Aquí podrías integrar el envío real si usas el ambiente de pruebas DIAN
        $venta->update([
            'estado_dian' => 'ENVIADA',
            'cufe' => strtoupper(Str::random(32)), // Simula el CUFE real
            'qr_code' => 'https://www.dian.gov.co/VerificarFactura/' . $venta->id,
        ]);
    }

    private function generarXmlFactura(Venta $venta): string
    {
        $fecha = $venta->fecha_venta->format('Y-m-d');
        $hora = $venta->fecha_venta->format('H:i:s');

        $xml = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<Invoice xmlns="urn:oasis:names:specification:ubl:schema:xsd:Invoice-2"
         xmlns:cac="urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2"
         xmlns:cbc="urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2">

  <cbc:ProfileID>DIAN 2.1</cbc:ProfileID>
  <cbc:ID>FE{$venta->id}</cbc:ID>
  <cbc:UUID schemeID="2" schemeName="CUFE-SHA384">{$venta->id}-SIMULADO</cbc:UUID>
  <cbc:IssueDate>{$fecha}</cbc:IssueDate>
  <cbc:IssueTime>{$hora}-05:00</cbc:IssueTime>
  <cbc:InvoiceTypeCode>01</cbc:InvoiceTypeCode>
  <cbc:DocumentCurrencyCode>COP</cbc:DocumentCurrencyCode>

  <cac:AccountingSupplierParty>
    <cac:Party>
      <cac:PartyName>
        <cbc:Name>TECNOLOGÍAS AVANZADAS S.A.S.</cbc:Name>
      </cac:PartyName>
    </cac:Party>
  </cac:AccountingSupplierParty>

  <cac:AccountingCustomerParty>
    <cac:Party>
      <cac:PartyName>
        <cbc:Name>{$venta->cliente->nombre}</cbc:Name>
      </cac:PartyName>
    </cac:Party>
  </cac:AccountingCustomerParty>

XML;

        // 🔁 Agregar líneas de detalle
        foreach ($venta->detalles as $i => $detalle) {
            $id = $i + 1;
            $cantidad = $detalle->cantidad;
            $precio = number_format($detalle->precio_unitario, 2, '.', '');
            $subtotal = number_format($detalle->subtotal, 2, '.', '');
            $descripcion = htmlspecialchars($detalle->vehiculo->descripcion_completa ?? 'Vehículo');

            $xml .= <<<XML
  <cac:InvoiceLine>
    <cbc:ID>{$id}</cbc:ID>
    <cbc:InvoicedQuantity unitCode="EA">{$cantidad}</cbc:InvoicedQuantity>
    <cbc:LineExtensionAmount currencyID="COP">{$subtotal}</cbc:LineExtensionAmount>
    <cac:Item>
      <cbc:Description>{$descripcion}</cbc:Description>
    </cac:Item>
    <cac:Price>
      <cbc:PriceAmount currencyID="COP">{$precio}</cbc:PriceAmount>
    </cac:Price>
  </cac:InvoiceLine>

XML;
        }

        // 🧮 Agregar totales
        $subtotal = number_format($venta->subtotal, 2, '.', '');
        $iva = number_format($venta->iva, 2, '.', '');
        $total = number_format($venta->total, 2, '.', '');

        $xml .= <<<XML
  <cac:TaxTotal>
    <cbc:TaxAmount currencyID="COP">{$iva}</cbc:TaxAmount>
  </cac:TaxTotal>

  <cac:LegalMonetaryTotal>
    <cbc:LineExtensionAmount currencyID="COP">{$subtotal}</cbc:LineExtensionAmount>
    <cbc:TaxInclusiveAmount currencyID="COP">{$total}</cbc:TaxInclusiveAmount>
    <cbc:PayableAmount currencyID="COP">{$total}</cbc:PayableAmount>
  </cac:LegalMonetaryTotal>

</Invoice>
XML;

        return $xml;
    }
}
