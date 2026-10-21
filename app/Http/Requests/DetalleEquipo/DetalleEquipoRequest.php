<?php

namespace App\Http\Requests\DetalleEquipo;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DetalleEquipoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'equipo_id'           => 'required|exists:equipos,id',
            'estado_id'           => 'required|exists:estados_equipo,id',
            'tipo_disco_id'       => 'nullable|exists:tipo_discos,id',
            'inventario'          => ['required', 'string', 'max:50', Rule::unique('detalle_equipos')->ignore($this->route('detalle_equipo'))],
            'serial'              => ['required', 'string', 'max:100', Rule::unique('detalle_equipos')->ignore($this->route('detalle_equipo'))],
            'ram_gb'              => 'nullable|integer|min:1|max:1024',
            'capacidad_disco_gb'  => 'nullable|integer|min:1',
            'ip'                  => 'nullable|ip',
            'fecha_adquisicion'   => 'nullable|date',
            'puerto_switch'       => 'nullable|integer|min:1|max:9999',
            'teletrabajo'         => 'boolean',
            'es_prestamo'         => 'boolean',
            'mac_ethernet'        => 'nullable|string|regex:/^([0-9A-Fa-f]{2}[:-]){5}([0-9A-Fa-f]{2})$/',
            'mac_wifi'            => 'nullable|string|regex:/^([0-9A-Fa-f]{2}[:-]){5}([0-9A-Fa-f]{2})$/',
        ];
    }

    public function messages(): array
    {
        return [
            'equipo_id.required'    => 'Debes seleccionar un equipo.',
            'estado_id.required'    => 'Debes seleccionar un estado.',
            'inventario.required'   => 'El número de inventario es obligatorio.',
            'inventario.unique'     => 'Ya existe un equipo con ese número de inventario.',
            'serial.required'       => 'El serial es obligatorio.',
            'serial.unique'         => 'Ya existe un equipo con ese serial.',
            'ip.ip'                 => 'La IP no tiene un formato válido.',
            'mac_ethernet.regex'    => 'La MAC Ethernet no tiene un formato válido (ej: AA:BB:CC:DD:EE:FF).',
            'mac_wifi.regex'        => 'La MAC WiFi no tiene un formato válido (ej: AA:BB:CC:DD:EE:FF).',
        ];
    }

    public function prepareForValidation(): void
    {
        $this->merge([
            'teletrabajo' => $this->boolean('teletrabajo'),
            'es_prestamo' => $this->boolean('es_prestamo'),
        ]);
    }
}