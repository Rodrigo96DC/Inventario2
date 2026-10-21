<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class RutChileno implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $rut = strtoupper(str_replace(['.', ' ', '-'], '', $value));

        if (! preg_match('/^(\d{7,8})([0-9K])$/', $rut, $coincidencias)) {
            $fail('El RUT no tiene un formato válido.');
            return;
        }

        [, $numero, $dv] = $coincidencias;

        $suma = 0;
        $multiplicador = 2;

        foreach (str_split(strrev($numero)) as $digito) {
            $suma += $digito * $multiplicador;
            $multiplicador = $multiplicador === 7 ? 2 : $multiplicador + 1;
        }

        $resto = 11 - ($suma % 11);
        $dvEsperado = match (true) {
            $resto === 11 => '0',
            $resto === 10 => 'K',
            default => (string) $resto,
        };

        if ($dv !== $dvEsperado) {
            $fail('El RUT ingresado no es válido (dígito verificador incorrecto).');
        }
    }
}