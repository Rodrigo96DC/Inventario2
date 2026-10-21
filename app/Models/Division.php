<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\Auditable;

class Division extends Model
{
    use SoftDeletes, Auditable;
// app/Models/Division.php
protected $table = 'divisiones';
    protected $guarded = [];

    public function departamentos()
    {
        return $this->hasMany(Departamento::class);
    }
}