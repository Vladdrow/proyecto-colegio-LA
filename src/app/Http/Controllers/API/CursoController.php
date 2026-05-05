<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Curso;
use Illuminate\Http\Request;

class CursoController extends Controller
{
    /**
     * GET /api/cursos - Listar cursos para combobox
     */
    public function index(Request $request)
    {
        $query = Curso::query();

        // Filtro por nivel (inicial, primaria, secundaria)
        if ($request->has('nivel')) {
            $query->where('nivel', $request->nivel);
        }

        // Filtro por turno
        if ($request->has('turno')) {
            $query->where('turno', $request->turno);
        }

        //$cursos = $query->orderBy('grado')->orderBy('paralelo')->get();
        $cursos = $query->get();

        return response()->json([
            'success' => true,
            'data' => $cursos
        ]);
    }
}
