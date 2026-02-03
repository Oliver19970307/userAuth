<?php

declare(strict_types=1);

header('Content-Type: application/json');

/*
|--------------------------------------------------------------------------
| Session / Cookie Security Settings
|--------------------------------------------------------------------------
|
| These settings help mitigate XSS, CSRF, and session hijacking attacks.
|
*/

ini_set('session.cookie_httponly', '1');      // Prevent JavaScript access
ini_set('session.cookie_samesite', 'Strict'); // CSRF mitigation
// ini_set('session.cookie_secure', '1');     // Enable only under HTTPS

session_start();

$pdo = require 'db.php';

/*
|--------------------------------------------------------------------------
| Allow POST Requests Only
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);

    echo json_encode([
        'status'  => 'error',
        'message' => 'Method not allowed',
    ]);

    exit;
}

/*
|--------------------------------------------------------------------------
| CSRF Token Validation
|--------------------------------------------------------------------------
*/

$csrfToken = $_POST['csrf_token'] ?? '';

if (
    empty($_SESSION['csrf_token']) ||
    !hash_equals($_SESSION['csrf_token'], $csrfToken)
) {
    http_response_code(403);

    echo json_encode([
        'status'  => 'error',
        'message' => 'Invalid request',
    ]);

    exit;
}

/*
|--------------------------------------------------------------------------
| Input Validation
|--------------------------------------------------------------------------
*/

$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';

if (strlen($username) < 3 || strlen($password) < 6) {
    echo json_encode([
        'status'  => 'error',
        'message' => 'Invalid username or password length',
    ]);

    exit;
}

/*
|--------------------------------------------------------------------------
| Registration Rate Limiting (Anti-bot / Anti-spam)
|--------------------------------------------------------------------------
|
| Limit the number of registration attempts per session.
|
*/

$maxAttempts = 3;
$lockTime    = 900; // 15 minutes

$_SESSION['register_attempts'] = $_SESSION['register_attempts'] ?? 0;
$_SESSION['register_lock']     = $_SESSION['register_lock'] ?? 0;

if (time() < $_SESSION['register_lock']) {
    echo json_encode([
        'status'  => 'error',
        'message' => 'Too many attempts. Please try again later.',
    ]);

    exit;
}

/*
|--------------------------------------------------------------------------
| Password Hashing
|--------------------------------------------------------------------------
*/

$passwordHash = password_hash($password, PASSWORD_DEFAULT);

/*
|--------------------------------------------------------------------------
| Database Insert
|--------------------------------------------------------------------------
*/

try {
    $sql  = 'INSERT INTO users (username, password) VALUES (:username, :password)';
    $stmt = $pdo->prepare($sql);

    $stmt->execute([
        ':username' => $username,
        ':password' => $passwordHash,
    ]);

    // Reset rate limit counters on success
    $_SESSION['register_attempts'] = 0;
    $_SESSION['register_lock']     = 0;

    echo json_encode([
        'status'  => 'success',
        'message' => 'Registration successful',
    ]);

    exit;
} catch (PDOException $exception) {
    $_SESSION['register_attempts']++;

    if ($_SESSION['register_attempts'] >= $maxAttempts) {
        $_SESSION['register_lock'] = time() + $lockTime;
    }

    // Duplicate username (MySQL error code 1062)
    if (isset($exception->errorInfo[1]) && $exception->errorInfo[1] === 1062) {
        echo json_encode([
            'status'  => 'error',
            'message' => 'Registration failed',
        ]);

        exit;
    }

    echo json_encode([
        'status'  => 'error',
        'message' => 'Registration failed. Please try again later.',
    ]);
}