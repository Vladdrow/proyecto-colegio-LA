<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Especialidad extends Model
{
    protected $table = 'especialidad';
    protected $primaryKey = 'id_especialidad';
    public $timestamps = false;

    protected $fillable = ['descripcion'];

    public function docentes()
    {
        return $this->belongsToMany(Docente::class, 'docente_especialidad', 'id_especialidad', 'id_docente');
    }
}