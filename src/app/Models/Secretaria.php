<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Secretaria extends Model
{
    protected $table = 'secretaria';
    protected $primaryKey = 'id_secretaria';
    public $timestamps = false;

    protected $fillable = ['id_persona'];

    public function persona()
    {
        return $this->belongsTo(Persona::class, 'id_persona');
    }
}