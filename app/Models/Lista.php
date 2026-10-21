<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\Auditable;

class Lista extends Model
{
    use SoftDeletes, Auditable;

    protected $table = 'listas';
    protected $guarded = [];

    protected $casts = [
        'publica'   => 'boolean',
        'auto_baja' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(ListaItem::class);
    }

    public function getDescripcionAuditoria(): string
    {
        return "Lista: {$this->nombre}";
    }
}