<?php

/* TODO Exportacion a xlsx y pdf */
namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Bitacora;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class BitacoraController extends Controller
{
    /**
     * GET /api/bitacora - Listar registros con filtros
     */
    public function index(Request $request)
    {
        $query = Bitacora::with('usuario'); // Cargar relación con usuario

        // Filtro por tabla afectada
        if ($request->has('tabla')) {
            $query->where('tabla_afectada', 'ilike', $request->tabla);
        }

        // Filtro por operación
        if ($request->has('operacion')) {
            $query->where('operacion', 'ilike', $request->operacion);
        }

        // Filtro por ID de usuario
        if ($request->has('usuario')) {
            $query->where('id_usuario', $request->usuario);
        }

        // Filtro por fecha desde
        if ($request->has('fecha_desde')) {
            $query->whereDate('fecha', '>=', $request->fecha_desde);
        }

        // Filtro por fecha hasta
        if ($request->has('fecha_hasta')) {
            $query->whereDate('fecha', '<=', $request->fecha_hasta);
        }

        // Búsqueda en resumen_cambio
        if ($request->has('search')) {
            $search = $request->search;
            $query->where('resumen_cambio', 'ilike', "%{$search}%");
        }

        // Ordenamiento
        $sortBy = $request->get('sort_by', 'fecha');
        $sortOrder = $request->get('sort_order', 'asc');
        $query->orderBy($sortBy, $sortOrder);

        // Paginación
        $perPage = $request->get('per_page', 15);
        $bitacora = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $bitacora,
            'filters' => $request->only([
                'tabla',
                'operacion',
                'usuario',
                'fecha_desde',
                'fecha_hasta',
                'search',
                'sort_by',
                'sort_order',
                'per_page',
                'page'
            ])
        ]);
    }

    /**
     * GET /api/bitacora/{id} - Ver detalle de un registro específico
     */
    public function show($id)
    {
        $registro = Bitacora::with('usuario')->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $registro
        ]);
    }

    /**
     * GET /api/bitacora/exportar?formato=csv
     */
    public function exportar(Request $request)
    {
        $query = Bitacora::with('usuario');

        // Aplicar los mismos filtros que en index()
        if ($request->has('tabla')) {
            $query->where('tabla_afectada', 'ilike', $request->tabla);
        }

        if ($request->has('operacion')) {
            $query->where('operacion', 'ilike', $request->operacion);
        }

        if ($request->has('usuario')) {
            $query->where('id_usuario', $request->usuario);
        }

        if ($request->has('fecha_desde')) {
            $query->whereDate('fecha', '>=', $request->fecha_desde);
        }

        if ($request->has('fecha_hasta')) {
            $query->whereDate('fecha', '<=', $request->fecha_hasta);
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where('resumen_cambio', 'ilike', "%{$search}%");
        }

        $registros = $query->orderBy('fecha', 'desc')->get();

        // Crear contenido CSV
        $handle = fopen('php://temp', 'w+');

        // Cabeceras del CSV
        fputcsv($handle, [
            'ID',
            'Tabla Afectada',
            'Operación',
            'ID Registro Afectado',
            'Resumen del Cambio',
            'ID Usuario',
            'Usuario',
            'IP Origen',
            'Fecha'
        ]);

        // Datos
        foreach ($registros as $registro) {
            fputcsv($handle, [
                $registro->id_bitacora,
                $registro->tabla_afectada,
                $registro->operacion,
                $registro->id_registro_afectado,
                $registro->resumen_cambio,
                $registro->id_usuario,
                $registro->usuario ? $registro->usuario->username : 'Sistema',
                $registro->ip_origen,
                $registro->fecha
            ]);
        }

        // Obtener contenido y cerrar handle
        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);

        // Registrar en bitácora (acción importante)
        $this->registrarBitacora(
            'Bitacora',
            'Exportar CSV',
            null,
            json_encode([
                'filtros' => $request->only(['tabla', 'operacion', 'usuario', 'fecha_desde', 'fecha_hasta', 'search']),
                'total_registros' => $registros->count()
            ]),
            auth()->id(),
            $request->ip()
        );

        // Devolver como descarga
        return Response::make($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="bitacora_' . date('Y-m-d_His') . '.csv"',
        ]);
    }

    private function registrarBitacora($tabla, $operacion, $idRegistro, $resumen, $idUsuario, $ip)
    {
        \App\Models\Bitacora::create([
            'tabla_afectada' => $tabla,
            'operacion' => $operacion,
            'id_registro_afectado' => $idRegistro,
            'resumen_cambio' => $resumen,
            'id_usuario' => $idUsuario,
            'ip_origen' => $ip,
            'fecha' => now()
        ]);
    }

}