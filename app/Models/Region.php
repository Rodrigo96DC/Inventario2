<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Region extends Model
{
    protected $table = 'regiones';
    protected $guarded = [];

    public function funcionarios()
    {
        return $this->hasMany(Funcionario::class);
    }
}