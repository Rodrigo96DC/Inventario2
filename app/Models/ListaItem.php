<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ListaItem extends Model
{
    protected $table = 'lista_items';
    protected $guarded = [];

    public function lista()
    {
        return $this->belongsTo(Lista::class);
    }

    public function itemable()
    {
        return $this->morphTo();
    }
}