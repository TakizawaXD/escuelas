<?php

namespace App\Validators;

class Validator
{
    protected array $errors = [];

    /**
     * Valida un conjunto de datos contra una lista de reglas.
     */
    public function validate(array $data, array $rules): bool
    {
        $this->errors = [];

        foreach ($rules as $field => $fieldRules) {
            $value = $data[$field] ?? null;

            foreach ($fieldRules as $rule) {
                // Rule: required
                if ($rule === 'required') {
                    if ($value === null || $value === '' || (is_array($value) && empty($value))) {
                        $this->errors[$field][] = "El campo $field es obligatorio.";
                    }
                } 
                // Rule: email
                elseif ($rule === 'email') {
                    if ($value && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                        $this->errors[$field][] = "El formato de correo no es válido.";
                    }
                } 
                // Rule: numeric
                elseif ($rule === 'numeric') {
                    if ($value && !is_numeric($value)) {
                        $this->errors[$field][] = "El campo $field debe ser numérico.";
                    }
                } 
                // Rule: min:X
                elseif (strpos($rule, 'min:') === 0) {
                    $min = (int)substr($rule, 4);
                    if ($value !== null && strlen((string)$value) < $min) {
                        $this->errors[$field][] = "El campo $field debe tener al menos $min caracteres.";
                    }
                } 
                // Rule: max:X
                elseif (strpos($rule, 'max:') === 0) {
                    $max = (int)substr($rule, 4);
                    if ($value !== null && strlen((string)$value) > $max) {
                        $this->errors[$field][] = "El campo $field no debe superar los $max caracteres.";
                    }
                }
            }
        }

        return empty($this->errors);
    }

    /**
     * Retorna los errores de la validación.
     */
    public function getErrors(): array
    {
        return $this->errors;
    }
}
