<?php
/* TODO eliminar persona */
namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Usuario;
use App\Models\Persona;
use App\Models\Bitacora;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class UsuarioController extends Controller
{
    /**
     * GET /api/usuarios - Listar usuarios con filtros
     */
    public function index(Request $request)
    {
        $user = $request->user();
        \Log::info('Parámetros recibidos:', $request->all());

        $query = Usuario::with(['rol', 'persona']);

        // Filtro por rol
        if ($request->has('rol')) {
            $rol = $request->rol;
            // Puede venir como nombre o como id
            if (is_numeric($rol)) {
                $query->where('id_rol', $rol);
            } else {
                $query->whereHas('rol', function ($q) use ($rol) {
                    $q->where('nombre', 'ilike', $rol);
                });
            }
        }

        // Filtro por estado
        if ($request->has('estado')) {
            $query->where('estado', $request->estado);
        }

        // Búsqueda por texto
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('username', 'ilike', "%{$search}%")
                    ->orWhere('email', 'ilike', "%{$search}%")
                    ->orWhereHas('persona', function ($q2) use ($search) {
                        $q2->where('nombres', 'ilike', "%{$search}%")
                            ->orWhere('apellidos', 'ilike', "%{$search}%")
                            ->orWhere('ci', 'ilike', "%{$search}%");
                    });
            });
        }

        // Ordenamiento
        $sortBy = $request->get('sort_by', 'id_usuario');
        $sortOrder = $request->get('sort_order', 'asc');
        $query->orderBy($sortBy, $sortOrder);

        // Paginación
        $perPage = $request->get('per_page', 15);
        $usuarios = $query->paginate($perPage);

        // Transformar resultados (agregar datos específicos según rol)
        $usuarios->getCollection()->transform(function ($usuario) {
            $usuario->loadSpecificData();
            return $usuario;
        });


        $this->registrarBitacora(
            'Usuario',
            'Listar usuarios',
            null,
            json_encode([
                'filtros' => $request->only(['rol', 'estado', 'search', 'sort_by', 'sort_order']),
                'resultados' => $usuarios->total(),
                'pagina' => $request->get('page', 1),
                'por_pagina' => $perPage
            ]),
            $user->id_usuario,
            $request->ip()
        );


        return response()->json([
            'success' => true,
            'data' => $usuarios,
            'filters' => $request->only(['rol', 'estado', 'search', 'sort_by', 'sort_order'])
        ]);
    }

    /**
     * GET /api/usuarios/{id} - Mostrar un usuario específico
     */
    public function show(Request $request, $id)
    {
        $user = $request->user();
        $usuario = Usuario::with(['rol', 'persona'])->findOrFail($id);
        $usuario->loadSpecificData();

        $this->registrarBitacora(
            'Usuario',
            'Ver usuario',
            $usuario->id_usuario,
            json_encode([
                'username' => $usuario->username,
                'id_rol' => $usuario->id_rol,
                'rol_nombre' => $usuario->rol?->nombre
            ]),
            $user->id_usuario,
            $request->ip()
        );


        return response()->json([
            'success' => true,
            'data' => $usuario
        ]);
    }

    /**
     * POST /api/usuarios - Crear nuevo usuario
     */
    public function store(Request $request)
    {
        $user = $request->user();
        $request->validate([
            // Datos de Usuario
            'username' => 'required|string|max:100|unique:usuario,username',
            'email' => 'nullable|email|max:100|unique:usuario,email',
            'password' => 'required|string|min:6',
            'id_rol' => 'required|integer|exists:rol,id_rol',

            // Datos de Persona
            'persona' => 'required|array',
            'persona.ci' => 'required|string|max:15|unique:persona,ci',
            'persona.nombres' => 'required|string|max:100',
            'persona.apellidos' => 'required|string|max:100',
            'persona.direccion' => 'nullable|string|max:200',
            'persona.telefono' => 'nullable|string|max:15',
            'persona.fecha_nac' => 'required|date',

            // Datos específicos según rol
            'docente' => 'array',
            'docente.rda' => 'nullable|string|max:10|unique:docente,rda',
            'estudiante' => 'array',
            'estudiante.rude' => 'nullable|string|max:50|unique:estudiante,rude',
        ]);

        try {
            DB::beginTransaction();

            // 1. Crear Persona
            $persona = Persona::create($request->persona);

            // 2. Crear Usuario
            $usuario = Usuario::create([
                'username' => $request->username,
                'email' => $request->email,
                'contrasena' => Hash::make($request->password),
                'id_rol' => $request->id_rol,
                'id_persona' => $persona->id_persona,
                'estado' => 1,
            ]);

            // 3. Crear registro en tabla específica según rol
            $this->crearRegistroEspecifico($usuario, $request);

            // 4. Registrar en bitácora
            $this->registrarBitacora(
                'Usuario',
                'Crear usuario',
                $usuario->id_usuario,
                json_encode([
                    'usuario' => $usuario->username,
                    'datos' => $request->except(['password'])
                ]),
                $user->id_usuario,
                $request->ip()
            );

            DB::commit();

            // Cargar relaciones
            $usuario->load(['rol', 'persona']);
            $usuario->loadSpecificData();

            return response()->json([
                'success' => true,
                'message' => 'Usuario creado exitosamente',
                'data' => $usuario
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al crear usuario: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * PUT /api/usuarios/{id} - Actualizar usuario completo
     */
    public function update(Request $request, $id)
    {
        $user = $request->user();
        $usuario = Usuario::with(['persona'])->findOrFail($id);

        $request->validate([
            'username' => "required|string|max:100|unique:usuario,username,{$id},id_usuario",
            'email' => "nullable|email|max:100|unique:usuario,email,{$id},id_usuario",
            'id_rol' => 'required|integer|exists:rol,id_rol',
            'estado' => 'sometimes|boolean',

            'persona' => 'required|array',
            'persona.ci' => "required|string|max:15|unique:persona,ci,{$usuario->id_persona},id_persona",
            'persona.nombres' => 'required|string|max:100',
            'persona.apellidos' => 'required|string|max:100',
            'persona.direccion' => 'nullable|string|max:200',
            'persona.telefono' => 'nullable|string|max:15',
            'persona.fecha_nac' => 'required|date',

            'docente' => 'array',
            'docente.rda' => "nullable|string|max:10|unique:docente,rda,{$usuario->id_persona},id_persona",
            'estudiante' => 'array',
            'estudiante.rude' => "nullable|string|max:50|unique:estudiante,rude,{$usuario->id_persona},id_persona",
        ]);

        try {
            DB::beginTransaction();

            // 1. Actualizar Persona
            $usuario->persona->update($request->persona);

            // 2. Actualizar Usuario
            $updateData = [
                'username' => $request->username,
                'email' => $request->email,
                'id_rol' => $request->id_rol,
            ];
            if ($request->has('estado')) {
                $updateData['estado'] = $request->estado;
            }
            if ($request->has('password')) {
                $updateData['contrasena'] = Hash::make($request->password);
            }
            $usuario->update($updateData);

            // 3. Actualizar registro específico según rol
            $this->actualizarRegistroEspecifico($usuario, $request);

            // 4. Registrar en bitácora
            $this->registrarBitacora(
                'Usuario',
                'Actualizar usuario',
                $usuario->id_usuario,
                json_encode([
                    'usuario' => $usuario->username,
                    'datos_actualizados' => $request->except(['password'])
                ]),
                $user->id_usuario,
                $request->ip()
            );

            DB::commit();

            // Recargar relaciones
            $usuario->load(['rol', 'persona']);
            $usuario->loadSpecificData();

            return response()->json([
                'success' => true,
                'message' => 'Usuario actualizado exitosamente',
                'data' => $usuario
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar usuario: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * DELETE /api/usuarios/{id} - Eliminar/desactivar usuario
     */
    public function destroy(Request $request, $id)
    {
        $user = $request->user();
        $usuario = Usuario::findOrFail($id);

        try {
            DB::beginTransaction();

            // Desactivar en lugar de eliminar físicamente
            $usuario->update(['estado' => 0]);

            // Registrar en bitácora
            $this->registrarBitacora(
                'Usuario',
                'Desactivar usuario',
                $usuario->id_usuario,
                json_encode([
                    'usuario' => $usuario->username,
                    'estado_nuevo' => 0
                ]),
                $user->id_usuario,
                $request->ip()
            );

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Usuario desactivado exitosamente'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al desactivar usuario: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * GET /api/roles - Listar roles disponibles
     */
    public function roles()
    {
        $roles = \App\Models\Rol::all();
        return response()->json([
            'success' => true,
            'data' => $roles
        ]);
    }

    /**
     * GET /api/permisos - Listar permisos disponibles
     */
    public function permisos()
    {
        $permisos = \App\Models\Permiso::all();
        return response()->json([
            'success' => true,
            'data' => $permisos
        ]);
    }

    /**
     * GET /api/usuarios/{id}/permisos - Obtener permisos finales de un usuario
     */
    public function getPermisosUsuario(Request $request, $id)
    {
        $user = $request->user();
        $usuario = Usuario::findOrFail($id);
        $permisos = $usuario->permisos_finales;

        $this->registrarBitacora(
            'Usuario',
            'Ver permisos de usuario',
            $usuario->id_usuario,
            json_encode([
                'username' => $usuario->username,
                'id_rol' => $usuario->id_rol,
                'rol_nombre' => $usuario->rol?->nombre,
                'cantidad_permisos' => $permisos->count(),
                'permisos' => $permisos->pluck('codigo')->toArray()
            ]),
            $user->id_usuario,
            $request->ip()
        );

        return response()->json([
            'success' => true,
            'data' => $permisos->pluck('codigo')
        ]);
    }

    /**
     * POST /api/usuarios/{id}/permisos - Asignar/revocar permisos a un usuario
     */
    public function asignarPermisos(Request $request, $id)
    {
        $user = $request->user();
        $request->validate([
            'permisos' => 'required|array',
            'permisos.*.codigo' => 'required|string|exists:permiso,codigo',
            'permisos.*.otorgado' => 'required|boolean'
        ]);

        $usuario = Usuario::findOrFail($id);

        try {
            DB::beginTransaction();

            foreach ($request->permisos as $permisoData) {
                $permiso = \App\Models\Permiso::where('codigo', $permisoData['codigo'])->first();
                $usuario->permisosSobrescritos()->syncWithoutDetaching([
                    $permiso->id_permiso => ['otorgado' => $permisoData['otorgado']]
                ]);
            }

            $this->registrarBitacora(
                'Usuario',
                'Asignar permisos',
                $usuario->id_usuario,
                json_encode([
                    'usuario' => $usuario->username,
                    'permisos_asignados' => $request->permisos
                ]),
                $user->id_usuario,
                $request->ip()
            );

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Permisos asignados correctamente'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al asignar permisos: ' . $e->getMessage()
            ], 500);
        }
    }

    // ============================================
    // MÉTODOS PRIVADOS
    // ============================================

    private function crearRegistroEspecifico($usuario, $request)
    {
        switch ($usuario->id_rol) {
            case 6: // Docente
                if ($request->has('docente')) {
                    \App\Models\Docente::create([
                        'id_persona' => $usuario->id_persona,
                        'rda' => $request->docente['rda'] ?? null
                    ]);
                }
                break;
            case 4: // Estudiante
                if ($request->has('estudiante')) {
                    \App\Models\Estudiante::create([
                        'id_persona' => $usuario->id_persona,
                        'rude' => $request->estudiante['rude'] ?? null
                    ]);
                }
                break;
            case 5: // Tutor
                \App\Models\Tutor::create([
                    'id_persona' => $usuario->id_persona
                ]);
                break;
            case 2: // Secretaria
                \App\Models\Secretaria::create([
                    'id_persona' => $usuario->id_persona
                ]);
                break;
        }
    }

    private function actualizarRegistroEspecifico($usuario, $request)
    {
        switch ($usuario->id_rol) {
            case 6: // Docente
                $docente = \App\Models\Docente::where('id_persona', $usuario->id_persona)->first();
                if ($docente && $request->has('docente')) {
                    $docente->update([
                        'rda' => $request->docente['rda'] ?? $docente->rda
                    ]);
                }
                break;
            case 4: // Estudiante
                $estudiante = \App\Models\Estudiante::where('id_persona', $usuario->id_persona)->first();
                if ($estudiante && $request->has('estudiante')) {
                    $estudiante->update([
                        'rude' => $request->estudiante['rude'] ?? $estudiante->rude
                    ]);
                }
                break;
        }
    }

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