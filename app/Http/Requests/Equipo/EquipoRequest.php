<?php

namespace App\Http\Requests\Equipo;

use App\Models\TipoEquipo;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EquipoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $tipoEquipo = TipoEquipo::find($this->input('tipo_equipo_id'))?->nombre;

        $imagenRules = ['image', 'mimes:jpeg,png,jpg', 'max:2048'];
        $imagenRules[] = $this->route('equipo') ? 'nullable' : 'required';

        $rules = [
            'tipo_equipo_id' => 'required|exists:tipo_equipos,id',
            'marca' => 'required|string|max:255',
            'modelo' => [
                'required',
                'string',
                'max:255',
                Rule::unique('equipos')
                    ->where('marca', $this->input('marca'))
                    ->ignore($this->route('equipo')),
            ],
            'entradas' => 'nullable|string|max:255',
            'procesador' => 'nullable|string|max:255',
            'observacion' => 'nullable|string',
            'imagen' => $imagenRules,
        ];

        if (in_array($tipoEquipo, ['Computador', 'Notebook'])) {
            $rules['procesador'] = 'required|string|max:255';
        } elseif (in_array($tipoEquipo, ['Monitor', 'Proyector', 'Tablet'])) {
            $rules['entradas'] = 'required|string|max:255';
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'tipo_equipo_id.required' => 'Debes seleccionar un tipo de equipo.',
            'tipo_equipo_id.exists' => 'El tipo de equipo seleccionado no es válido.',
            'marca.required' => 'La marca es obligatoria.',
            'modelo.required' => 'El modelo es obligatorio.',
            'modelo.unique' => 'Ya existe un equipo con esa marca y modelo.',
            'imagen.required' => 'La imagen del equipo es obligatoria.',
            'imagen.image' => 'El archivo debe ser una imagen.',
            'imagen.max' => 'La imagen no puede superar los 2MB.',
            'procesador.required' => 'El procesador es obligatorio para este tipo de equipo.',
            'entradas.required' => 'Las entradas/conexiones son obligatorias para este tipo de equipo.',
        ];
    }
}