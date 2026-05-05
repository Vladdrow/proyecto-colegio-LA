<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Estudiante;
use App\Models\Persona;
use App\Models\Bitacora;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EstudianteController extends Controller
{
    /**
     * GET /api/estudiantes - Listar estudiantes con filtros
     */
    public function index(Request $request)
    {
        \Log::info('Parámetros recibidos en estudiantes:', $request->all());
        $usuarioAutenticado = $request->user();
        $idUsuarioAutenticado = $usuarioAutenticado ? $usuarioAutenticado->id_usuario : null;

        $query = Estudiante::with('persona');

        // Filtro por gestión (año lectivo) - a través de matrícula
        if ($request->has('id_gestion')) {
            $query->whereHas('matricula', function ($q) use ($request) {
                $q->where('id_gestion', $request->id_gestion);
            });
        }

        // Filtro por curso - a través de matrícula
        if ($request->has('id_curso')) {
            $query->whereHas('matricula', function ($q) use ($request) {
                $q->where('id_curso', $request->id_curso);
            });
        }

        // Filtro por estado de matrícula (Activo, Retirado, etc.)
        if ($request->has('estado_matricula')) {
            $query->whereHas('matricula', function ($q) use ($request) {
                $q->where('estado', $request->estado_matricula);
            });
        }

        // Filtro por estado (si agregas estado en Persona)
        if ($request->has('estado')) {
            $query->whereHas('persona', function ($q) use ($request) {
                $q->where('estado', $request->estado);
            });
        }

        if ($request->has('genero')) {
            $query->whereHas('persona', function ($q) use ($request) {
                $q->where('genero', $request->genero);
            });
        }


        // Búsqueda por texto (nombre, apellido, ci, rude)
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('rude', 'ilike', "%{$search}%")
                    ->orWhereHas('persona', function ($q2) use ($search) {
                        $q2->where('nombres', 'ilike', "%{$search}%")
                            ->orWhere('apellidos', 'ilike', "%{$search}%")
                            ->orWhere('ci', 'ilike', "%{$search}%")
                            ->orWhere('genero', 'ilike', "%{$search}%")
                            ->orWhere('direccion', 'ilike', "%{$search}%")      // 👈 NUEVO
                            ->orWhere('telefono', 'ilike', "%{$search}%");       // 👈 NUEVO
                    });
            });
        }

        // Ordenamiento
        $sortBy = $request->get('sort_by', 'id_estudiante');
        $sortOrder = $request->get('sort_order', 'asc');
        $query->orderBy($sortBy, $sortOrder);

        // Paginación
        $perPage = $request->get('per_page', 15);
        $estudiantes = $query->paginate($perPage);

        $this->registrarBitacora(
            'Estudiante',
            'Listar estudiantes',
            null,
            json_encode([
                'filtros' => $request->only(['estado', 'genero', 'search', 'sort_by', 'sort_order']),
                'resultados' => $estudiantes->total()
            ]),
            $idUsuarioAutenticado,
            $request->ip()
        );

        return response()->json([
            'success' => true,
            'data' => $estudiantes,
            'filters' => $request->only(['estado', 'genero', 'search', 'sort_by', 'sort_order'])
        ]);
    }

    /**
     * GET /api/estudiantes/{id} - Ver estudiante específico
     */
    public function show(Request $request, $id)
    {
        $usuarioAutenticado = $request->user();
        $idUsuarioAutenticado = $usuarioAutenticado ? $usuarioAutenticado->id_usuario : null;

        $estudiante = Estudiante::with('persona')->findOrFail($id);

        $this->registrarBitacora(
            'Estudiante',
            'Ver estudiante',
            $estudiante->id_estudiante,
            json_encode([
                'rude' => $estudiante->rude,
                'persona' => $estudiante->persona->nombres . ' ' . $estudiante->persona->apellidos
            ]),
            $idUsuarioAutenticado,
            $request->ip()
        );

        return response()->json([
            'success' => true,
            'data' => $estudiante
        ]);
    }

    /**
     * POST /api/estudiantes - Crear estudiante
     */
    public function store(Request $request)
    {
        $usuarioAutenticado = $request->user();
        $idUsuarioAutenticado = $usuarioAutenticado ? $usuarioAutenticado->id_usuario : null;

        $request->validate([
            // Datos de Persona
            'ci' => 'required|string|max:15|unique:persona,ci',
            'nombres' => 'required|string|max:100',
            'apellidos' => 'required|string|max:100',
            'direccion' => 'nullable|string|max:200',
            'telefono' => 'nullable|string|max:15',
            'fecha_nac' => 'required|date',
            'genero' => 'required|in:F,M',
            // Datos de Estudiante
            'rude' => 'required|string|max:50|unique:estudiante,rude',
        ]);

        try {
            DB::beginTransaction();

            // 1. Crear Persona
            $persona = Persona::create([
                'ci' => $request->ci,
                'nombres' => $request->nombres,
                'apellidos' => $request->apellidos,
                'direccion' => $request->direccion,
                'telefono' => $request->telefono,
                'fecha_nac' => $request->fecha_nac,
                'genero' => $request->genero,
                'estado' => 1
            ]);

            // 2. Crear Estudiante
            $estudiante = Estudiante::create([
                'id_persona' => $persona->id_persona,
                'rude' => $request->rude
            ]);

            // 3. Registrar en bitácora
            $this->registrarBitacora(
                'Estudiante',
                'Crear estudiante',
                $estudiante->id_estudiante,
                json_encode([
                    'persona' => [
                        'id_persona' => $persona->id_persona,
                        'ci' => $persona->ci,
                        'nombres' => $persona->nombres,
                        'apellidos' => $persona->apellidos,
                    ],
                    'estudiante' => [
                        'id_estudiante' => $estudiante->id_estudiante,
                        'rude' => $estudiante->rude
                    ]
                ]),
                $idUsuarioAutenticado,
                $request->ip()
            );

            DB::commit();

            // Cargar relación persona
            $estudiante->load('persona');

            return response()->json([
                'success' => true,
                'message' => 'Estudiante creado exitosamente',
                'data' => $estudiante
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al crear estudiante: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * PUT /api/estudiantes/{id} - Actualizar estudiante
     */
    public function update(Request $request, $id)
    {
        $usuarioAutenticado = $request->user();
        $idUsuarioAutenticado = $usuarioAutenticado ? $usuarioAutenticado->id_usuario : null;

        $estudiante = Estudiante::with('persona')->findOrFail($id);

        $request->validate([
            // Datos de Persona
            'ci' => "required|string|max:15|unique:persona,ci,{$estudiante->id_persona},id_persona",
            'nombres' => 'required|string|max:100',
            'apellidos' => 'required|string|max:100',
            'direccion' => 'nullable|string|max:200',
            'telefono' => 'nullable|string|max:15',
            'fecha_nac' => 'required|date',
            'genero' => 'required|in:F,M',
            // Datos de Estudiante
            'rude' => "required|string|max:50|unique:estudiante,rude,{$id},id_estudiante",
        ]);

        try {
            DB::beginTransaction();

            // 1. Actualizar Persona
            $estudiante->persona->update([
                'ci' => $request->ci,
                'nombres' => $request->nombres,
                'apellidos' => $request->apellidos,
                'direccion' => $request->direccion,
                'telefono' => $request->telefono,
                'fecha_nac' => $request->fecha_nac,
                'genero' => $request->genero,
            ]);

            // 2. Actualizar Estudiante
            $estudiante->update([
                'rude' => $request->rude
            ]);

            // 3. Registrar en bitácora
            $this->registrarBitacora(
                'Estudiante',
                'Actualizar estudiante',
                $estudiante->id_estudiante,
                json_encode([
                    'persona' => [
                        'id_persona' => $estudiante->persona->id_persona,
                        'cambios' => [
                            'ci' => $estudiante->persona->ci,
                            'nombres' => $estudiante->persona->nombres,
                            'apellidos' => $estudiante->persona->apellidos,
                            'direccion' => $estudiante->persona->direccion,
                            'telefono' => $estudiante->persona->telefono,
                            'fecha_nac' => $estudiante->persona->fecha_nac,
                        ]
                    ],
                    'estudiante' => [
                        'id_estudiante' => $estudiante->id_estudiante,
                        'rude' => $estudiante->rude
                    ]
                ]),
                $idUsuarioAutenticado,
                $request->ip()
            );

            DB::commit();

            $estudiante->load('persona');

            return response()->json([
                'success' => true,
                'message' => 'Estudiante actualizado exitosamente',
                'data' => $estudiante
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar estudiante: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * PUT /api/estudiantes/{id}/estado - Cambiar estado de matrícula
     */
    public function cambiarEstadoMatricula(Request $request, $id)
    {
        $usuarioAutenticado = $request->user();
        $idUsuarioAutenticado = $usuarioAutenticado ? $usuarioAutenticado->id_usuario : null;

        $request->validate([
            'estado' => 'required|string|in:Activo,Retirado,Pendiente,Aprobado,Reprobado'
        ]);

        $estudiante = Estudiante::with('persona', 'matricula')->findOrFail($id);

        if (!$estudiante->matricula) {
            return response()->json([
                'success' => false,
                'message' => 'El estudiante no tiene una matrícula activa'
            ], 404);
        }

        try {
            DB::beginTransaction();

            $estadoAnterior = $estudiante->matricula->estado;
            $estudiante->matricula->update(['estado' => $request->estado]);

            $this->registrarBitacora(
                'Matricula',
                'Cambiar estado de matrícula',
                $estudiante->matricula->id_matricula,
                json_encode([
                    'estudiante' => [
                        'id_estudiante' => $estudiante->id_estudiante,
                        'rude' => $estudiante->rude,
                        'nombre' => $estudiante->persona->nombres . ' ' . $estudiante->persona->apellidos
                    ],
                    'estado_anterior' => $estadoAnterior,
                    'estado_nuevo' => $request->estado
                ]),
                $idUsuarioAutenticado,
                $request->ip()
            );

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Estado de matrícula actualizado exitosamente',
                'data' => [
                    'estado_anterior' => $estadoAnterior,
                    'estado_nuevo' => $request->estado
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al cambiar estado: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * DELETE /api/estudiantes/{id} - Desactivar estudiante
     */
    public function destroy(Request $request, $id)
    {
        $usuarioAutenticado = $request->user();
        $idUsuarioAutenticado = $usuarioAutenticado ? $usuarioAutenticado->id_usuario : null;


        $estudiante = Estudiante::with('persona')->findOrFail($id);

        try {
            DB::beginTransaction();

            // Desactivar persona (en lugar de eliminar físicamente)
            $estudiante->persona->update(['estado' => 0]);

            $this->registrarBitacora(
                'Estudiante',
                'Desactivar estudiante',
                $estudiante->persona->id_persona,
                json_encode([
                    'motivo' => 'Desactivación de estudiante',
                    'estudiante' => [
                        'id_estudiante' => $estudiante->id_estudiante,
                        'rude' => $estudiante->rude
                    ],
                    'persona' => [
                        'id_persona' => $estudiante->persona->id_persona,
                        'nombres' => $estudiante->persona->nombres,
                        'apellidos' => $estudiante->persona->apellidos,
                    ],
                    'nuevo_estado' => 0
                ]),
                $idUsuarioAutenticado,
                $request->ip()
            );

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Estudiante desactivado exitosamente'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al desactivar estudiante: ' . $e->getMessage()
            ], 500);
        }
    }

    // ============================================
    // MÉTODO PRIVADO PARA BITÁCORA
    // ============================================

    private function registrarBitacora($tabla, $operacion, $idRegistro, $resumen, $idUsuario, $ip)
    {
        Bitacora::create([
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