<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tutor extends Model
{
    protected $table = 'tutor';
    protected $primaryKey = 'id_tutor';
    public $timestamps = false;

    protected $fillable = ['id_persona'];

    public function persona()
    {
        return $this->belongsTo(Persona::class, 'id_persona');
    }
}