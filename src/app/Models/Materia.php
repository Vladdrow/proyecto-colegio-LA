<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Materia extends Model
{
    protected $table = 'materia';
    protected $primaryKey = 'id_materia';
    public $timestamps = false;

    protected $fillable = ['nombre', 'distintivo', 'carga_horaria', 'id_campo'];

    public function campo()
    {
        return $this->belongsTo(CampoSaberesConocimientos::class, 'id_campo');
    }

    public function asignaciones()
    {
        return $this->hasMany(Asignacion::class, 'id_materia');
    }

    public function notas()
    {
        return $this->hasMany(Nota::class, 'id_materia');
    }

    public function asistencias()
    {
        return $this->hasMany(Asistencia::class, 'id_materia');
    }
}