<?php

namespace App\Http\Requests\Funcionario;

use App\Rules\RutChileno;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class FuncionarioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nombre' => 'required|string|max:255',
            'rut' => [
                'nullable',
                'string',
                new RutChileno(),
                Rule::unique('funcionarios', 'rut')->ignore($this->route('funcionario')),
            ],
               'region_id'          => 'nullable|exists:regiones,id',
        'division_id'        => 'nullable|exists:divisiones,id',
        'departamento_id'    => 'nullable|exists:departamentos,id',
        'unidad_id'          => 'nullable|exists:unidades,id',
        'cargo'              => 'nullable|string|max:255',
        'correo'             => 'nullable|email|max:255',
        'fecha_ingreso'      => 'nullable|date',
        'fecha_fin_contrato' => 'nullable|date',
        'activo'             => 'boolean',
        'avatar'             => 'nullable|image|max:2048',
        ];
    }

    public function messages(): array
    {
        return [
            'nombre.required' => 'El nombre del funcionario es obligatorio.',
            'region_id.required' => 'Debes seleccionar una región.',
            'region_id.exists' => 'La región seleccionada no es válida.',
            'rut.unique' => 'Ya existe un funcionario registrado con ese RUT.',
            'correo.email' => 'El correo electrónico no tiene un formato válido.',
            'avatar.image' => 'El archivo debe ser una imagen.',
            'avatar.max' => 'La imagen no puede superar los 2MB.',
        ];
    }
}