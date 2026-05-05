<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Bitacora;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{

    public function login(Request $request)
    {
        $request->validate([
            "identifier" => "required|string",
            "password" => "required",
        ]);

        $ip = $request->ip();
        $userAgent = $request->userAgent();

        // Buscar usuario
        $user = Usuario::where("username", $request->identifier)->first();
        if (!$user) {
            $user = Usuario::where("email", $request->identifier)->first();
        }

        // Caso 1: Usuario no existe
        if (!$user) {
            $this->registrarBitacora(
                'Sistema',
                'Inicio de sesion fallido',
                null,
                json_encode([
                    'username_intentado' => $request->identifier,
                    'motivo' => 'usuario_no_existe',
                    'ip' => $ip,
                    'user_agent' => $userAgent
                ]),
                null,
                $ip
            );

            return response()->json([
                "success" => false,
                "message" => "Credenciales incorrectas"
            ], 401);
        }

        // Caso 2: Contraseña incorrecta
        if (!Hash::check($request->password, $user->contrasena)) {
            $this->registrarBitacora(
                'Sistema',
                'Inicio de sesion fallido',
                null,
                json_encode([
                    'username_intentado' => $request->identifier,
                    'motivo' => 'password_incorrecto',
                    'ip' => $ip,
                    'user_agent' => $userAgent
                ]),
                $user->id_usuario,
                $ip
            );

            return response()->json([
                "success" => false,
                "message" => "Credenciales incorrectas"
            ], 401);
        }

        // Caso 3: Usuario inactivo
        if ($user->estado != 1) {
            $this->registrarBitacora(
                'Sistema',
                'Inicio de sesion fallido',
                null,
                json_encode([
                    'username_intentado' => $request->identifier,
                    'motivo' => 'usuario_inactivo',
                    'ip' => $ip,
                    'user_agent' => $userAgent
                ]),
                $user->id_usuario,
                $ip
            );

            return response()->json([
                "success" => false,
                "message" => "Usuario inactivo. Contacte al administrador."
            ], 403);
        }

        // Caso 4: Login exitoso
        $token = $user->createToken("auth_token")->plainTextToken;

        // Cargar relaciones necesarias
        $user->load(['rol', 'persona']);
        $permisosFinales = $user->getPermisosFinalesAttribute();

        $this->registrarBitacora(
            'Sistema',
            'Inicio de sesion',
            $user->id_usuario,
            json_encode([
                'username' => $user->username,
                'ip' => $ip,
                'user_agent' => $userAgent
            ]),
            $user->id_usuario,
            $ip
        );

        return response()->json([
            "success" => true,
            "token" => $token,
            "usuario" => [
                "id_usuario" => $user->id_usuario,
                "username" => $user->username,
                "email" => $user->email,
                "id_rol" => $user->id_rol,
                "rol_nombre" => $user->rol ? $user->rol->nombre : null,
                "id_persona" => $user->id_persona,
                "persona" => $user->persona ? [
                    "id_persona" => $user->persona->id_persona,
                    "ci" => $user->persona->ci,
                    "nombres" => $user->persona->nombres,
                    "apellidos" => $user->persona->apellidos,
                    "direccion" => $user->persona->direccion,
                    "telefono" => $user->persona->telefono,
                    "fecha_nac" => $user->persona->fecha_nac,
                ] : null,
                "permisos" => $permisosFinales->pluck('codigo')->toArray(),
                "estado" => $user->estado,
            ]
        ]);
    }

    public function logout(Request $request)
    {
        $user = $request->user();
        $ip = $request->ip();

        $this->registrarBitacora(
            'Sistema',
            'Cierre de sesion',
            $user->id_usuario,
            json_encode([
                'username' => $user->username,
                'ip' => $ip
            ]),
            $user->id_usuario,
            $ip
        );

        $request->user()->currentAccessToken()->delete();
        return response()->json(["message" => "Sesión cerrada correctamente"]);
    }

    public function user(Request $request)
    {
        $user = $request->user();
        return response()->json([
            "id_usuario" => $user->id_usuario,
            "username" => $user->username,
            "email" => $user->email,
            "id_rol" => $user->id_rol,
            "id_persona" => $user->id_persona,
            "estado" => $user->estado,
        ]);
    }

    // Método privado para registrar en bitácora
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