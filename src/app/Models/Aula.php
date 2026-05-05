<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Aula extends Model
{
    protected $table = 'aula';
    protected $primaryKey = 'id_aula';
    public $timestamps = false;

    protected $fillable = ['nombre', 'capacidad', 'ubicacion', 'estado', 'tipo'];

    public function horarios()
    {
        return $this->hasMany(Horario::class, 'id_aula');
    }
}