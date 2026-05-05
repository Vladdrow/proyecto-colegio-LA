<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Matricula extends Model
{
    protected $table = 'matricula';
    protected $primaryKey = 'id_matricula';
    public $timestamps = true;

    protected $fillable = [
        'fecha_inscripcion', 'estado', 'id_estudiante', 
        'id_curso', 'id_gestion', 'id_tutor', 'id_secretaria'
    ];

    public function estudiante()
    {
        return $this->belongsTo(Estudiante::class, 'id_estudiante');
    }

    public function curso()
    {
        return $this->belongsTo(Curso::class, 'id_curso');
    }

    public function gestion()
    {
        return $this->belongsTo(Gestion::class, 'id_gestion');
    }

    public function tutor()
    {
        return $this->belongsTo(Tutor::class, 'id_tutor');
    }

    public function secretaria()
    {
        return $this->belongsTo(Secretaria::class, 'id_secretaria');
    }

    public function notas()
    {
        return $this->hasMany(Nota::class, 'id_matricula');
    }

    public function asistencias()
    {
        return $this->hasMany(Asistencia::class, 'id_matricula');
    }

    public function pagos()
    {
        return $this->hasMany(Pago::class, 'id_matricula');
    }

    public function becas()
    {
        return $this->belongsToMany(Beca::class, 'est_beca', 'id_matricula', 'id_beca');
    }
}