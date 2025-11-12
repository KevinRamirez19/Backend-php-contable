<?php

namespace App\Http\Controllers;

use App\Models\AsientoContable;
use App\Models\PartidaContable;
use App\Models\Movimiento;
use App\Models\Cuenta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;


class AsientoContableController extends Controller
{
    public function index()
    {
        $asientos = AsientoContable::with(['partidas.cuenta'])->get();
        return response()->json(['success' => true, 'data' => $asientos], 200);
    }
public function store(Request $request)
{
    try {
        $validatedData = $request->validate([
            'codigo' => 'required|string',
            'fecha' => 'required|date',
            'descripcion' => 'required|string',
            'partidas' => 'required|array|min:2',
            'partidas.*.cuenta_id' => 'required|exists:cuentas,id',
            'partidas.*.debe' => 'nullable|numeric|min:0',
            'partidas.*.haber' => 'nullable|numeric|min:0',
            'partidas.*.descripcion' => 'nullable|string',
        ]);

        // Crear el asiento contable
        $asiento = new AsientoContable();
        $asiento->codigo = $validatedData['codigo'];
        $asiento->fecha = $validatedData['fecha'];
        $asiento->descripcion = $validatedData['descripcion'];
        $asiento->created_by = auth()->id() ?? 1;
        $asiento->save();

        $totalDebe = 0;
        $totalHaber = 0;

        // Recorrer las partidas
        foreach ($validatedData['partidas'] as $partidaData) {
            $partida = new PartidaContable();
            $partida->asiento_id = $asiento->id;
            $partida->cuenta_id = $partidaData['cuenta_id'];
            $partida->descripcion = $partidaData['descripcion'] ?? null;
            $partida->debe = $partidaData['debe'] ?? 0;
            $partida->haber = $partidaData['haber'] ?? 0;
            $partida->save();

            $totalDebe += $partida->debe;
            $totalHaber += $partida->haber;
        }

        // Guardar totales balanceados en el asiento
        $asiento->total_debe = $totalDebe;
        $asiento->total_haber = $totalHaber;
        $asiento->save();

        // Validar que esté balanceado
        if (round($totalDebe, 2) !== round($totalHaber, 2)) {
            throw new \Exception('El total del debe y el haber deben estar balanceados.');
        }

        return response()->json([
            'success' => true,
            'message' => 'Asiento contable creado correctamente.',
            'data' => $asiento->load('partidas.cuenta')
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Error al crear el asiento: ' . $e->getMessage(),
        ], 400);
    }
}

    public function show($id)
    {
        $asiento = AsientoContable::with(['partidas.cuenta'])->findOrFail($id);
        return response()->json(['success' => true, 'data' => $asiento]);
    }

    public function destroy($id)
    {
        $asiento = AsientoContable::findOrFail($id);
        $asiento->partidas()->delete();
        Movimiento::where('asiento_id', $asiento->id)->delete();
        $asiento->delete();
        return response()->json(['success' => true, 'message' => 'Asiento eliminado correctamente']);
    }
}
