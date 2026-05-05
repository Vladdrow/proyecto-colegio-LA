<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Docente extends Model
{
    protected $table = 'docente';
    protected $primaryKey = 'id_docente';
    public $timestamps = false;

    protected $fillable = ['id_persona', 'rda'];

    public function persona()
    {
        return $this->belongsTo(Persona::class, 'id_persona');
    }

    public function especialidades()
    {
        //return $this->belongsToMany(Especialidad::class, 'docente_especialidad', 'id_docente', 'id_especialidad');
    }
}