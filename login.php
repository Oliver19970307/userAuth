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

ini_set('session.cookie_httponly', '1');      // Prevent JavaScript access to cookies
ini_set('session.cookie_samesite', 'Strict'); // Mitigate CSRF attacks
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

if ($username === '' || $password === '') {
    echo json_encode([
        'status'  => 'error',
        'message' => 'Username and password are required',
    ]);

    exit;
}

/*
|--------------------------------------------------------------------------
| Brute Force Protection
|--------------------------------------------------------------------------
*/

$maxAttempts = 5;
$lockTime    = 600;

$_SESSION['login_attempts'] = $_SESSION['login_attempts'] ?? 0;
$_SESSION['lock_until']     = $_SESSION['lock_until'] ?? 0;

if (time() < $_SESSION['lock_until']) {
    echo json_encode([
        'status'  => 'error',
        'message' => 'Too many attempts. Please try again later.',
    ]);

    exit;
}

/*
|--------------------------------------------------------------------------
| Authentication
|--------------------------------------------------------------------------
*/

try {
    $sql  = 'SELECT id, password FROM users WHERE username = :username';
    $stmt = $pdo->prepare($sql);

    $stmt->execute([
        ':username' => $username,
    ]);

    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        /*
         * Successful login
         * - Reset brute force counters
         * - Regenerate session ID
         */
        $_SESSION['login_attempts'] = 0;
        $_SESSION['lock_until']     = 0;

        session_regenerate_id(true);

        $_SESSION['user_id']  = $user['id'];
        $_SESSION['username'] = $username;

        echo json_encode([
            'status'  => 'success',
            'message' => 'Login successful',
        ]);

        exit;
    }

    /*
     * Failed login attempt
     */
    $_SESSION['login_attempts']++;

    if ($_SESSION['login_attempts'] >= $maxAttempts) {
        $_SESSION['lock_until'] = time() + $lockTime;
    }

    echo json_encode([
        'status'  => 'error',
        'message' => 'Invalid username or password',
    ]);
} catch (PDOException $exception) {
    /*
     * Database or unexpected server error
     */
    echo json_encode([
        'status'  => 'error',
        'message' => 'Login failed. Please try again later.',
    ]);
}