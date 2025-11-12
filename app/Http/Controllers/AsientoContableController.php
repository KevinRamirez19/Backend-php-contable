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
        // 🟡 Log inicial: datos recibidos desde el frontend
        Log::info('📩 Datos recibidos en store():', $request->all());

        // 🔍 Validación de campos esperados
        $validatedData = $request->validate([
            'fecha' => 'required|date',
            'descripcion' => 'required|string',
            'partidas' => 'required|array|min:2',
            'partidas.*.cuenta_id' => 'required|exists:cuentas,id',
            'partidas.*.debe' => 'nullable|numeric|min:0',
            'partidas.*.haber' => 'nullable|numeric|min:0',
            'partidas.*.descripcion' => 'nullable|string',
        ]);

        Log::info('✅ Datos validados correctamente:', $validatedData);

        // 🔹 Generar código automáticamente
        $ultimoAsiento = \App\Models\AsientoContable::latest('id')->first();
        $nuevoCodigo = 'AS-' . str_pad(($ultimoAsiento ? $ultimoAsiento->id + 1 : 1), 3, '0', STR_PAD_LEFT);

        // 🧾 Crear el asiento
        $asiento = new \App\Models\AsientoContable();
        $asiento->codigo = $nuevoCodigo;
        $asiento->fecha = $validatedData['fecha'];
        $asiento->descripcion = $validatedData['descripcion'];
        $asiento->created_by = auth()->id() ?? 1;
        $asiento->save();

        Log::info('🆕 Asiento creado:', $asiento->toArray());

        $totalDebe = 0;
        $totalHaber = 0;

        // 💾 Guardar partidas
        foreach ($validatedData['partidas'] as $index => $partidaData) {
            Log::info("➡️ Guardando partida {$index}:", $partidaData);

            $partida = new \App\Models\PartidaContable();
            $partida->asiento_id = $asiento->id;
            $partida->cuenta_id = $partidaData['cuenta_id'];
            $partida->descripcion = $partidaData['descripcion'] ?? null;
            $partida->debe = $partidaData['debe'] ?? 0;
            $partida->haber = $partidaData['haber'] ?? 0;
            $partida->save();

            $totalDebe += $partida->debe;
            $totalHaber += $partida->haber;
        }

        // ⚖️ Actualizar totales y verificar balance
       /* $asiento->total_debe = $totalDebe;
        $asiento->total_haber = $totalHaber;
        $asiento->save();*/

        Log::info('📊 Totales calculados:', [
            'total_debe' => $totalDebe,
            'total_haber' => $totalHaber,
        ]);

        if (round($totalDebe, 2) !== round($totalHaber, 2)) {
            throw new \Exception('El total del debe y el haber deben estar balanceados.');
        }

        Log::info('✅ Asiento contable creado correctamente.');

        return response()->json([
            'success' => true,
            'message' => 'Asiento contable creado correctamente.',
            'data' => $asiento->load('partidas.cuenta')
        ]);
    } catch (\Throwable $e) {
        // 🚨 Log completo del error
        Log::error('❌ Error al crear asiento:', [
            'mensaje' => $e->getMessage(),
            'linea' => $e->getLine(),
            'archivo' => $e->getFile(),
            'traza' => $e->getTraceAsString(),
        ]);

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
