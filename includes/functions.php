<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

const DB_HOST = 'localhost';
const DB_NAME = 'student_management_system';
const DB_USER = 'root';
const DB_PASS = '';

function e($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf'];
}

function csrf_valid($token): bool
{
    return is_string($token) && !empty($_SESSION['csrf']) && hash_equals($_SESSION['csrf'], $token);
}

function db_connection(): mysqli
{
    static $connection;

    if ($connection instanceof mysqli) {
        return $connection;
    }

    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    $connection = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    $connection->set_charset('utf8mb4');

    return $connection;
}

function db_scalar(mysqli $db, string $sql)
{
    $result = $db->query($sql);
    $row = $result->fetch_row();

    return $row[0] ?? 0;
}

function db_rows(mysqli $db, string $sql): array
{
    $result = $db->query($sql);

    return $result->fetch_all(MYSQLI_ASSOC);
}