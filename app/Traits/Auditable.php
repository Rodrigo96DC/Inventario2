<?php

namespace App\Traits;

use App\Models\Auditoria;

/**
 * @mixin \Illuminate\Database\Eloquent\Model
 */
trait Auditable
{
    public static function bootAuditable(): void
    {
        static::created(fn ($model) => $model->registrarAuditoria('creado'));
        static::updated(fn ($model) => $model->registrarAuditoria('actualizado'));

        static::deleted(function ($model) {
            $forzado = method_exists($model, 'isForceDeleting') && $model->isForceDeleting();
            $model->registrarAuditoria($forzado ? 'eliminado_permanente' : 'eliminado');
        });

        if (method_exists(static::class, 'restored')) {
            static::restored(fn ($model) => $model->registrarAuditoria('restaurado'));
        }
    }

    public function auditorias()
    {
        return $this->morphMany(Auditoria::class, 'auditable')->latest();
    }

protected function registrarAuditoria(string $accion): void
{
    $cambios     = null;
    $descripcion = $this->descripcionAuditoria();

    if ($accion === 'actualizado') {
        $despues = $this->getChanges();
        unset($despues['updated_at']);
        if (empty($despues)) return;

        $antes = array_intersect_key($this->getOriginal(), $despues);

        // Descripción especial para transferencias de asignación
        if (isset($despues['funcionario_id']) && method_exists($this, 'getDescripcionTransferencia')) {
            $descripcion = $this->getDescripcionTransferencia($antes['funcionario_id'], $despues['funcionario_id']);
        }

        $cambios = ['antes' => $antes, 'despues' => $despues];
    }

    $this->auditorias()->create([
        'user_id'     => auth()->id(),
        'accion'      => $accion,
        'descripcion' => $descripcion,
        'cambios'     => $cambios,
    ]);
}

public function descripcionAuditoria(): string
{
    // Descripción específica por modelo
    if (method_exists($this, 'getDescripcionAuditoria')) {
        return $this->getDescripcionAuditoria();
    }

    return $this->nombre
        ?? $this->titulo
        ?? ($this->marca && $this->modelo ? "{$this->marca} {$this->modelo}" : null)
        ?? class_basename($this) . " #{$this->id}";
}
}