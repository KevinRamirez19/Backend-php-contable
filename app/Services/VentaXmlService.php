<?php

namespace App\Services;

use App\Models\Venta;
use SimpleXMLElement;

class VentaXmlService
{
    /**
     * Generar XML UBL 2.1 de la venta
     */
    public static function generarXml(Venta $venta): string
    {
        $xml = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><Invoice/>');
        $xml->addAttribute('xmlns', 'urn:oasis:names:specification:ubl:schema:xsd:Invoice-2');
        $xml->addAttribute('xmlns:cac', 'urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2');
        $xml->addAttribute('xmlns:cbc', 'urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2');
        $xml->addAttribute('xmlns:sts', 'dian:gov:co:facturaelectronica:Structures-2-1');

        // CABECERA
        $xml->addChild('cbc:ProfileID', 'DIAN 2.1');
        $xml->addChild('cbc:ID', $venta->numero_factura);
        $uuid = $xml->addChild('cbc:UUID', $venta->cufe ?? '');
        $uuid->addAttribute('schemeID', '2');
        $uuid->addAttribute('schemeName', 'CUFE-SHA384');
        $xml->addChild('cbc:IssueDate', $venta->fecha_venta);
        $xml->addChild('cbc:InvoiceTypeCode', '01'); // Factura de venta
        $xml->addChild('cbc:DocumentCurrencyCode', 'COP');

        // EMISOR
        $emisor = $xml->addChild('cac:AccountingSupplierParty')->addChild('cac:Party');
        $emisor->addChild('cac:PartyIdentification')->addChild('cbc:ID', '900123456')->addAttribute('schemeID', '31');
        $emisor->addChild('cac:PartyName')->addChild('cbc:Name', 'AUTOMORATA S.A.S.');

        // CLIENTE
        $cliente = $xml->addChild('cac:AccountingCustomerParty')->addChild('cac:Party');
        $cliente->addChild('cac:PartyIdentification')->addChild('cbc:ID', $venta->cliente->documento ?? '')->addAttribute('schemeID', '13');
        $cliente->addChild('cac:PartyName')->addChild('cbc:Name', $venta->cliente->nombre);

        // DETALLES
        foreach ($venta->detalles as $i => $detalle) {
            $line = $xml->addChild('cac:InvoiceLine');
            $line->addChild('cbc:ID', $i + 1);
            $line->addChild('cbc:InvoicedQuantity', $detalle->cantidad)->addAttribute('unitCode', 'EA');
            $line->addChild('cbc:LineExtensionAmount', $detalle->subtotal)->addAttribute('currencyID', 'COP');
            $item = $line->addChild('cac:Item');
            $item->addChild('cbc:Description', $detalle->vehiculo->marca.' '.$detalle->vehiculo->modelo.' '.$detalle->vehiculo->año);
            $price = $line->addChild('cac:Price');
            $price->addChild('cbc:PriceAmount', $detalle->precio_unitario)->addAttribute('currencyID', 'COP');
        }

        // IMPUESTOS
        $taxTotal = $xml->addChild('cac:TaxTotal');
        $taxTotal->addChild('cbc:TaxAmount', $venta->iva)->addAttribute('currencyID', 'COP');

        // TOTALES
        $legal = $xml->addChild('cac:LegalMonetaryTotal');
        $legal->addChild('cbc:LineExtensionAmount', $venta->subtotal)->addAttribute('currencyID', 'COP');
        $legal->addChild('cbc:TaxExclusiveAmount', $venta->subtotal)->addAttribute('currencyID', 'COP');
        $legal->addChild('cbc:TaxInclusiveAmount', $venta->total)->addAttribute('currencyID', 'COP');
        $legal->addChild('cbc:PayableAmount', $venta->total)->addAttribute('currencyID', 'COP');

        // FIRMA SIMULADA
        $signature = $xml->addChild('cac:Signature');
        $signature->addChild('cbc:ID', 'ID12345');

        return $xml->asXML();
    }
}
