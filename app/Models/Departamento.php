<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\Auditable;

class Departamento extends Model
{
    use SoftDeletes, Auditable;

    protected $guarded = [];
// app/Models/Departamento.php
protected $table = 'departamentos';
    public function division()
    {
        return $this->belongsTo(Division::class);
    }

    public function unidades()
    {
        return $this->hasMany(Unidad::class);
    }
}