<?php

header('Content-Type: application/json');

function json_success(array $data = [], string $message = 'OK', int $statusCode = 200): void
{
    http_response_code($statusCode);
    echo json_encode([
        'success' => true,
        'message' => $message,
        'data' => $data,
    ]);
    exit;
}

function json_error(string $message, int $statusCode = 400, array $data = []): void
{
    http_response_code($statusCode);
    echo json_encode([
        'success' => false,
        'message' => $message,
        'data' => $data,
    ]);
    exit;
}

function require_method(string $method): void
{
    if ($_SERVER['REQUEST_METHOD'] !== $method) {
        json_error('Method not allowed', 405);
    }
}

function read_json_input(): array
{
    $raw = file_get_contents('php://input');
    if ($raw === false || trim($raw) === '') {
        return [];
    }

    $decoded = json_decode($raw, true);
    if (!is_array($decoded)) {
        json_error('Invalid JSON body', 400);
    }

    return $decoded;
}

function get_param(array $input, string $key, ?string $default = null): ?string
{
    if (!array_key_exists($key, $input)) {
        return $default;
    }

    $value = trim((string) $input[$key]);
    return $value === '' ? $default : $value;
}

function to_bool_flag($value, int $default = 1): int
{
    if ($value === null || $value === '') {
        return $default;
    }

    if ($value === true || $value === 1 || $value === '1' || strtolower((string) $value) === 'true') {
        return 1;
    }

    return 0;
}

function to_non_negative_int($value, string $fieldName): int
{
    if (!is_numeric($value) || (int) $value < 0) {
        json_error($fieldName . ' must be a non-negative integer');
    }

    return (int) $value;
}

function to_non_negative_float($value, string $fieldName): float
{
    if (!is_numeric($value) || (float) $value < 0) {
        json_error($fieldName . ' must be a non-negative number');
    }

    return (float) $value;
}
