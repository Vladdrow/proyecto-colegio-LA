<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CampoSaberesConocimientos extends Model
{
    protected $table = 'campo_saberes_conocimientos';
    protected $primaryKey = 'id_campo';
    public $timestamps = false;

    protected $fillable = ['descripcion'];

    public function materias()
    {
        return $this->hasMany(Materia::class, 'id_campo');
    }
}