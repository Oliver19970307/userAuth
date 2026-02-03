<?php

use PHPUnit\Framework\TestCase;

class RegisterTest extends TestCase
{
    protected function setUp(): void
    {
        $_SESSION = [];
        $_SESSION['csrf_token'] = 'test_csrf_token';
    }

    public function testRegisterSuccess()
    {
        $_POST = [
            'username'   => 'testuser_' . uniqid(),
            'password'   => 'password123',
            'csrf_token' => 'test_csrf_token'
        ];

        $_SERVER['REQUEST_METHOD'] = 'POST';

        ob_start();
        require __DIR__ . '/../register.php';
        $response = ob_get_clean();

        $data = json_decode($response, true);

        $this->assertEquals('success', $data['status']);
    }

    public function testRegisterDuplicateUsername()
    {
        $_POST = [
            'username'   => 'existing_user',
            'password'   => 'password123',
            'csrf_token' => 'test_csrf_token'
        ];

        $_SERVER['REQUEST_METHOD'] = 'POST';

        ob_start();
        require __DIR__ . '/../register.php';
        $response = ob_get_clean();

        $data = json_decode($response, true);

        $this->assertEquals('error', $data['status']);
    }
}