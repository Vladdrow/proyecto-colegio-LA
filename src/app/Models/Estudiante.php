<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Estudiante extends Model
{
    protected $table = 'estudiante';
    protected $primaryKey = 'id_estudiante';
    public $timestamps = false;

    protected $fillable = ['id_persona', 'rude'];

    public function persona()
    {
        return $this->belongsTo(Persona::class, 'id_persona');
    }

    public function matricula()
    {
        return $this->hasMany(Matricula::class, 'id_estudiante');
    }
}