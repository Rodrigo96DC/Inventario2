<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\Auditable;

class Unidad extends Model
{
    use SoftDeletes, Auditable;
// app/Models/Unidad.php
protected $table = 'unidades';
    protected $guarded = [];

    public function departamento()
    {
        return $this->belongsTo(Departamento::class);
    }

    public function funcionarios()
    {
        return $this->hasMany(Funcionario::class);
    }
}