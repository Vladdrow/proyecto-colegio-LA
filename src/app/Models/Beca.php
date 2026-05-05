<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Beca extends Model
{
    protected $table = 'beca';
    protected $primaryKey = 'id_beca';
    public $timestamps = false;

    protected $fillable = ['nombre', 'porcentaje'];

    public function matriculas()
    {
        return $this->belongsToMany(Matricula::class, 'est_beca', 'id_beca', 'id_matricula');
    }
}