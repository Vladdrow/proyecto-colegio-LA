<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class Usuario extends Authenticatable
{
    use HasApiTokens;

    protected $table = 'usuario';
    protected $primaryKey = 'id_usuario';
    public $timestamps = true;

    protected $fillable = [
        'username',
        'email',
        'contrasena',
        'id_rol',
        'id_persona',
        'estado',
        'intentos_fallidos',
        'remember_token'
    ];

    protected $hidden = [
        'contrasena',
        'remember_token'
    ];

    // Método requerido por Laravel para autenticación
    public function getAuthPassword()
    {
        return $this->contrasena;
    }

    // Método requerido por Laravel para el identificador
    public function getAuthIdentifierName()
    {
        return 'email'; // 👈 Usamos email para login
    }

    // Relación con Rol
    public function rol()
    {
        return $this->belongsTo(Rol::class, 'id_rol');
    }
    // Relación con Persona
    public function persona()
    {
        return $this->belongsTo(Persona::class, 'id_persona');
    }

    public function permisos()
    {
        return $this->belongsToMany(Permiso::class, 'usuario_permiso', 'id_usuario', 'id_permiso')
            ->wherePivot('otorgado', true);
    }

    public function getPermisosFinalesAttribute()
    {
        // 1. Obtener permisos base del rol (Rol_Permiso con otorgado = true)
        $permisosDelRol = $this->rol->permisos()
            ->wherePivot('otorgado', true)
            ->get();

        // 2. Obtener sobrescrituras del usuario (Usuario_Permiso)
        $sobrescrituras = $this->permisosSobrescritos()
            ->get()
            ->keyBy('codigo');

        // 3. Combinar respetando prioridad
        $permisosFinales = collect();

        foreach ($permisosDelRol as $permiso) {
            $codigo = $permiso->codigo;

            // Verificar si hay sobrescritura
            if ($sobrescrituras->has($codigo)) {
                $sobrescritura = $sobrescrituras->get($codigo);

                // Si otorgado = true, mantener; si false, excluir
                if ($sobrescritura->pivot->otorgado) {
                    $permisosFinales->push($permiso);
                }
            } else {
                // No hay sobrescritura, mantener permiso del rol
                $permisosFinales->push($permiso);
            }
        }

        // Agregar permisos EXTRA que el rol no tenía pero el usuario sí (otorgado = true)
        $permisosExtra = $sobrescrituras->filter(function ($permiso) {
            return $permiso->pivot->otorgado;
        })->diffKeys($permisosDelRol->keyBy('codigo'));

        foreach ($permisosExtra as $permiso) {
            $permisosFinales->push($permiso);
        }

        return $permisosFinales;
    }

    /**
     * Relación con sobrescrituras de permisos del usuario
     */
    public function permisosSobrescritos()
    {
        return $this->belongsToMany(Permiso::class, 'usuario_permiso', 'id_usuario', 'id_permiso')
            ->withPivot('otorgado');
    }
    /**
     * Cargar datos específicos según el rol
     */
    public function loadSpecificData()
    {
        switch ($this->id_rol) {
            case 6: // Docente
                $this->load('docente');
                break;
            case 4: // Estudiante
                $this->load('estudiante');
                break;
            case 5: // Tutor
                $this->load('tutor');
                break;
            case 2: // Secretaria
                $this->load('secretaria');
                break;
        }
        return $this;
    }


    public function docente()
    {
        return $this->hasOne(Docente::class, 'id_persona', 'id_persona');
    }

    public function estudiante()
    {
        return $this->hasOne(Estudiante::class, 'id_persona', 'id_persona');
    }

    public function tutor()
    {
        return $this->hasOne(Tutor::class, 'id_persona', 'id_persona');
    }

    public function secretaria()
    {
        return $this->hasOne(Secretaria::class, 'id_persona', 'id_persona');
    }
}