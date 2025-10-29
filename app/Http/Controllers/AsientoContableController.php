<?php

namespace App\Http\Controllers;

use App\Models\AsientoContable;
use App\Models\PartidaContable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AsientoContableController extends Controller
{
    // 🔹 Obtener todos los asientos con sus partidas y cuentas
    public function index()
    {
        try {
            $asientos = AsientoContable::with(['partidas.cuenta'])->get(); // 👈 importante: carga la cuenta

            return response()->json([
                'success' => true,
                'data' => $asientos
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener los asientos contables: ' . $e->getMessage()
            ], 500);
        }
    }

    // 🔹 Guardar nuevo asiento contable
    public function store(Request $request)
    {
        $request->validate([
            'codigo' => 'required|string|max:20|unique:asientos_contables,codigo',
            'fecha' => 'required|date',
            'descripcion' => 'required|string|max:255',
            'partidas' => 'required|array|min:2',
            'partidas.*.cuenta_id' => 'required|integer|exists:cuentas,id',
            'partidas.*.debe' => 'required|numeric|min:0',
            'partidas.*.haber' => 'required|numeric|min:0',
            'partidas.*.descripcion' => 'nullable|string|max:255',
        ]);

        DB::beginTransaction();
        try {
            // Crear asiento
            $asiento = AsientoContable::create([
                'codigo' => $request->codigo,
                'fecha' => $request->fecha,
                'descripcion' => $request->descripcion,
                'compra_id' => $request->compra_id ?? null,
                'venta_id' => $request->venta_id ?? null,
                'created_by' => auth()->id() ?? null,
            ]);

            // Crear partidas
            foreach ($request->partidas as $partida) {
                PartidaContable::create([
                    'asiento_id' => $asiento->id,
                    'cuenta_id' => $partida['cuenta_id'],
                    'debe' => $partida['debe'],
                    'haber' => $partida['haber'],
                    'descripcion' => $partida['descripcion'] ?? '',
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Asiento contable creado correctamente',
                'data' => $asiento->load('partidas.cuenta') // 👈 carga cuenta también aquí
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error creando asiento: ' . $e->getMessage(), $request->all());
            return response()->json([
                'success' => false,
                'message' => 'Error al crear el asiento contable: ' . $e->getMessage()
            ], 500);
        }
    }

    // 🔹 Mostrar un asiento con partidas y cuentas
    public function show($id)
    {
        try {
            $asiento = AsientoContable::with(['partidas.cuenta'])->findOrFail($id); // 👈 incluye cuenta

            return response()->json([
                'success' => true,
                'data' => $asiento
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Asiento no encontrado: ' . $e->getMessage()
            ], 404);
        }
    }

    // 🔹 Eliminar asiento
    public function destroy($id)
    {
        try {
            $asiento = AsientoContable::findOrFail($id);
            $asiento->delete();

            return response()->json([
                'success' => true,
                'message' => 'Asiento contable eliminado correctamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar el asiento: ' . $e->getMessage()
            ], 500);
        }
    }
}
