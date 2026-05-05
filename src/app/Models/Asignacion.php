<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Asignacion extends Model
{
    protected $table = 'asignacion';
    protected $primaryKey = 'id_asignacion';
    public $timestamps = false;

    protected $fillable = ['id_docente', 'id_materia', 'id_curso', 'id_gestion'];

    public function docente()
    {
        return $this->belongsTo(Docente::class, 'id_docente');
    }

    public function materia()
    {
        return $this->belongsTo(Materia::class, 'id_materia');
    }

    public function curso()
    {
        return $this->belongsTo(Curso::class, 'id_curso');
    }

    public function gestion()
    {
        return $this->belongsTo(Gestion::class, 'id_gestion');
    }

    public function horarios()
    {
        return $this->hasMany(Horario::class, 'id_asignacion');
    }
}