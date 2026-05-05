<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Nota extends Model
{
    protected $table = 'nota';
    protected $primaryKey = 'id_nota';
    public $timestamps = true;

    protected $fillable = ['trimestre', 'calificacion', 'id_matricula', 'id_materia'];

    public function matricula()
    {
        return $this->belongsTo(Matricula::class, 'id_matricula');
    }

    public function materia()
    {
        return $this->belongsTo(Materia::class, 'id_materia');
    }
}