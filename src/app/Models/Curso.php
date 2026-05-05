<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Curso extends Model
{
    protected $table = 'curso';
    protected $primaryKey = 'id_curso';
    public $timestamps = false;

    protected $fillable = ['grado', 'nivel', 'paralelo', 'turno'];

    public function matriculas()
    {
        return $this->hasMany(Matricula::class, 'id_curso');
    }

    public function asignaciones()
    {
        return $this->hasMany(Asignacion::class, 'id_curso');
    }
}