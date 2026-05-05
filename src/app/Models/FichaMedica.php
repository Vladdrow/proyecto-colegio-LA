<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FichaMedica extends Model
{
    protected $table = 'ficha_med';
    protected $primaryKey = 'id_ficha';
    public $timestamps = false;

    protected $fillable = ['id_estudiante', 'tipo_sangre', 'alergias', 'contacto_emerg'];

    public function estudiante()
    {
        return $this->belongsTo(Estudiante::class, 'id_estudiante');
    }
}