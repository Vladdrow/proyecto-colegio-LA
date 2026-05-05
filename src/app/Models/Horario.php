<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Horario extends Model
{
    protected $table = 'horario';
    protected $primaryKey = 'id_horario';
    public $timestamps = false;

    protected $fillable = ['dia', 'hora_inicio', 'hora_fin', 'id_asignacion', 'id_aula'];

    public function asignacion()
    {
        return $this->belongsTo(Asignacion::class, 'id_asignacion');
    }

    public function aula()
    {
        return $this->belongsTo(Aula::class, 'id_aula');
    }
}