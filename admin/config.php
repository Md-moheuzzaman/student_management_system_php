<?php
/**
 * Shared setup: session, database connection and small helpers.
 * Default XAMPP credentials are used below. Change them if yours differ.
 */
const DB_HOST = 'localhost';
const DB_NAME = 'student_management_system';
const DB_USER = 'root';
const DB_PASS = '';

session_set_cookie_params([
    'lifetime' => 0,
    'path'     => '/',
    'httponly' => true,
    'samesite' => 'Lax',
]);
session_start();

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    $db->set_charset('utf8mb4');
} catch (mysqli_sql_exception $ex) {
    http_response_code(500);
    exit('Cannot connect to the database. Start MySQL in XAMPP and check that student_management_system.sql has been imported.');
}

/** Run a prepared query with values bound according to their PHP types. */
function db_execute(mysqli $db, $sql, array $params = [])
{
    $stmt = $db->prepare($sql);
    if ($params) {
        $types = '';
        $boundParams = [];
        foreach ($params as $index => $value) {
            $types .= is_int($value) ? 'i' : (is_float($value) ? 'd' : 's');
            $boundParams[$index] = &$params[$index];
        }
        $stmt->bind_param($types, ...$boundParams);
    }
    $stmt->execute();
    return $stmt;
}

/** Escape text for safe output in HTML. */
function e($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

/** Return a CSRF token for the current session. */
function csrf_token()
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

/** True when the submitted token matches the session token. */
function csrf_valid($token)
{
    return is_string($token) && !empty($_SESSION['csrf']) && hash_equals($_SESSION['csrf'], $token);
}

/** Send the visitor to the login page unless they are signed in. */
function require_login()
{
    if (empty($_SESSION['user_id'])) {
        header('Location: index.php');
        exit;
    }
}
