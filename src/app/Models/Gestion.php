<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Gestion extends Model
{
    protected $table = 'gestion';
    protected $primaryKey = 'id_gestion';
    public $timestamps = false;

    protected $fillable = ['anio', 'estado', 'fecha_inicio', 'fecha_fin'];

    public function matriculas()
    {
        return $this->hasMany(Matricula::class, 'id_gestion');
    }

    public function asignaciones()
    {
        return $this->hasMany(Asignacion::class, 'id_gestion');
    }
}