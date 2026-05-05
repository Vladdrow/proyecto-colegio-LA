<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bitacora extends Model
{
    protected $table = 'bitacora';
    protected $primaryKey = 'id_bitacora';
    public $timestamps = false; // porque usamos el campo 'fecha' manualmente

    protected $fillable = [
        'tabla_afectada',
        'operacion',
        'id_registro_afectado',
        'resumen_cambio',
        'id_usuario',
        'ip_origen',
        'fecha'
    ];

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'id_usuario', 'id_usuario');
    }
}
