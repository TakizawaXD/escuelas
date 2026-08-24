<?php

namespace App\Controllers;

use App\Traits\HasApiResponses;

abstract class BaseController
{
    use HasApiResponses;

    /**
     * Obtiene y decodifica el cuerpo de una petición JSON.
     */
    protected function getRequestBody(): array
    {
        $input = file_get_contents('php://input');
        $decoded = json_decode($input, true);
        return is_array($decoded) ? $decoded : [];
    }

    /**
     * Sanitiza recursivamente un arreglo de datos de entrada.
     */
    protected function sanitizeInput(array $data): array
    {
        $sanitized = [];
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $sanitized[$key] = $this->sanitizeInput($value);
            } else {
                $sanitized[$key] = htmlspecialchars(trim((string)$value), ENT_QUOTES, 'UTF-8');
            }
        }
        return $sanitized;
    }
}
