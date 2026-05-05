<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Persona extends Model
{
    protected $table = 'persona';
    protected $primaryKey = 'id_persona';
    public $timestamps = true;

    protected $fillable = [
        'ci', 'nombres', 'apellidos', 'direccion', 
        'telefono', 'fecha_nac', 'estado', 'genero'
    ];

    public function usuario()
    {
        return $this->hasOne(Usuario::class, 'id_persona');
    }

    public function docente()
    {
        return $this->hasOne(Docente::class, 'id_persona');
    }

    public function estudiante()
    {
        return $this->hasOne(Estudiante::class, 'id_persona');
    }

    public function tutor()
    {
        return $this->hasOne(Tutor::class, 'id_persona');
    }
}