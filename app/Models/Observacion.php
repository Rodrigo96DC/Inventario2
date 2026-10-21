<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Observacion extends Model
{
    protected $table = 'observaciones';
    protected $guarded = [];

    public function detalleEquipo()
    {
        return $this->belongsTo(DetalleEquipo::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}