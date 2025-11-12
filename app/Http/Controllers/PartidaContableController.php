<?php

namespace App\Http\Controllers;

use App\Models\PartidaContable;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class PartidaContableController extends Controller
{
    // 🔹 Listar todas las partidas
    public function index()
    {
        $partidas = PartidaContable::all();
        return response()->json([
            'success' => true,
            'data' => $partidas
        ]);
    }

    // 🔹 Mostrar una partida por id
    public function show($id)
    {
        $partida = PartidaContable::find($id);
        if (!$partida) {
            return response()->json([
                'success' => false,
                'message' => 'Partida no encontrada'
            ], Response::HTTP_NOT_FOUND);
        }

        return response()->json([
            'success' => true,
            'data' => $partida
        ]);
    }

    // 🔹 Crear una nueva partida
    public function store(Request $request)
    {
        $request->validate([
            'asiento_id' => 'required|integer',
            'cuenta_id' => 'required|integer',
            'tipo' => 'required|in:debe,haber',
            'descripcion' => 'nullable|string',
        ]);


        $partida = PartidaContable::create($request->all());

        return response()->json([
            'success' => true,
            'data' => $partida
        ], Response::HTTP_CREATED);
    }

    // 🔹 Actualizar una partida
    public function update(Request $request, $id)
    {
        $partida = PartidaContable::find($id);
        if (!$partida) {
            return response()->json([
                'success' => false,
                'message' => 'Partida no encontrada'
            ], Response::HTTP_NOT_FOUND);
        }

        $request->validate([
            'asiento_id' => 'sometimes|integer',
            'cuenta_id' => 'sometimes|integer',
            'debe' => 'sometimes|numeric',
            'haber' => 'sometimes|numeric',
            'descripcion' => 'nullable|string',
        ]);

        $partida->update($request->all());

        return response()->json([
            'success' => true,
            'data' => $partida
        ]);
    }

    // 🔹 Eliminar una partida
    public function destroy($id)
    {
        $partida = PartidaContable::find($id);
        if (!$partida) {
            return response()->json([
                'success' => false,
                'message' => 'Partida no encontrada'
            ], Response::HTTP_NOT_FOUND);
        }

        $partida->delete();

        return response()->json([
            'success' => true,
            'message' => 'Partida eliminada correctamente'
        ]);
    }
}
