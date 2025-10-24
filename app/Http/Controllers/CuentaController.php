<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cuenta;

class CuentaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $cuentas = Cuenta::all();

        return response()->json([
            'success' => true,
            'data' => $cuentas
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validar los datos según las reglas del modelo
        $data = $request->validate(Cuenta::rules());

        // Crear la cuenta
        $cuenta = Cuenta::create($data);

        return response()->json([
            'success' => true,
            'data' => $cuenta,
            'message' => 'Cuenta creada correctamente'
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $cuenta = Cuenta::find($id);

        if (!$cuenta) {
            return response()->json([
                'success' => false,
                'message' => 'Cuenta no encontrada'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $cuenta
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $cuenta = Cuenta::findOrFail($id);

        if (!$cuenta) {
            return response()->json([
                'success' => false,
                'message' => 'Cuenta no encontrada'
            ], 404);
        }

        // Validar datos, ignorando la cuenta actual en la regla unique
        $data = $request->validate(Cuenta::rules($id));

        $cuenta->update($data);

        return response()->json([
            'success' => true,
            'data' => $cuenta,
            'message' => 'Cuenta actualizada correctamente'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $cuenta = Cuenta::find($id);

        if (!$cuenta) {
            return response()->json([
                'success' => false,
                'message' => 'Cuenta no encontrada'
            ], 404);
        }

        $cuenta->delete();

        return response()->json([
            'success' => true,
            'message' => 'Cuenta eliminada correctamente'
        ]);
    }
}
