<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\Auditable;

class Asignacion extends Model
{
    use Auditable;

    protected $table = 'asignaciones';
    protected $guarded = [];
    protected $casts = [
        'fecha_asignacion' => 'date',
    ];

    public function detalleEquipo()
    {
        return $this->belongsTo(DetalleEquipo::class);
    }

    public function funcionario()
    {
        return $this->belongsTo(Funcionario::class);
    }

    public function getDescripcionAuditoria(): string
{
    $equipo      = $this->detalleEquipo?->inventario ?? "equipo #{$this->detalle_equipo_id}";
    $funcionario = $this->funcionario?->nombre ?? "funcionario #{$this->funcionario_id}";
    return "Equipo {$equipo} asignado a {$funcionario}";
}

public function getDescripcionTransferencia(int $desdeFuncionarioId, int $hastaFuncionarioId): string
{
    $equipo   = $this->detalleEquipo?->inventario ?? "equipo #{$this->detalle_equipo_id}";
    $desde    = \App\Models\Funcionario::find($desdeFuncionarioId)?->nombre ?? "funcionario #{$desdeFuncionarioId}";
    $hasta    = \App\Models\Funcionario::find($hastaFuncionarioId)?->nombre ?? "funcionario #{$hastaFuncionarioId}";
    return "Equipo {$equipo} transferido de {$desde} a {$hasta}";
}
}