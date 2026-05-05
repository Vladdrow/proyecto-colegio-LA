<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pago extends Model
{
    protected $table = 'pago';
    protected $primaryKey = 'id_pago';
    public $timestamps = true;

    protected $fillable = ['id_matricula', 'mes', 'monto', 'fecha_pago', 'estado'];

    public function matricula()
    {
        return $this->belongsTo(Matricula::class, 'id_matricula');
    }
}