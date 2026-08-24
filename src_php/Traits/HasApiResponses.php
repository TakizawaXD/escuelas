<?php

namespace App\Traits;

trait HasApiResponses
{
    protected function jsonResponse(array $data, int $statusCode = 200, array $headers = []): void
    {
        foreach ($headers as $name => $value) {
            header("$name: $value");
        }
        header('Content-Type: application/json; charset=utf-8');
        http_response_code($statusCode);
        echo json_encode($data);
        exit;
    }

    protected function successResponse(string $message, array $data = [], int $statusCode = 200): void
    {
        $this->jsonResponse([
            'success' => true,
            'message' => $message,
            'data' => $data
        ], $statusCode);
    }

    protected function errorResponse(string $message, int $statusCode = 400, array $errors = []): void
    {
        $response = [
            'success' => false,
            'message' => $message
        ];
        if (!empty($errors)) {
            $response['errors'] = $errors;
        }
        $this->jsonResponse($response, $statusCode);
    }
}
