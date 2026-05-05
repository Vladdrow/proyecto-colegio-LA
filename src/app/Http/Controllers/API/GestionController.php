<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Gestion;
use Illuminate\Http\Request;

class GestionController extends Controller
{
    /**
     * GET /api/gestiones - Listar años lectivos para combobox
     */
    public function index(Request $request)
    {
        $query = Gestion::query();

        // Solo gestiones activas? (opcional)
        if ($request->has('activa')) {
            $query->where('estado', 1);
        }

        $gestiones = $query->orderBy('anio', 'desc')->get();

        return response()->json([
            'success' => true,
            'data' => $gestiones
        ]);
    }
}
