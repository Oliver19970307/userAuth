<?php

use PHPUnit\Framework\TestCase;

class LoginTest extends TestCase
{
    protected function setUp(): void
    {
        $_SESSION = [];
        $_SESSION['csrf_token'] = 'test_csrf_token';
    }

    public function testLoginFailWithWrongPassword()
    {
        $_POST = [
            'username'   => 'existing_user',
            'password'   => 'wrong_password',
            'csrf_token' => 'test_csrf_token'
        ];

        $_SERVER['REQUEST_METHOD'] = 'POST';

        ob_start();
        require __DIR__ . '/../login.php';
        $response = ob_get_clean();

        $data = json_decode($response, true);

        $this->assertEquals('error', $data['status']);
    }

    public function testLoginSuccess()
    {
        $_POST = [
            'username'   => 'existing_user',
            'password'   => 'correct_password',
            'csrf_token' => 'test_csrf_token'
        ];

        $_SERVER['REQUEST_METHOD'] = 'POST';

        ob_start();
        require __DIR__ . '/../login.php';
        $response = ob_get_clean();

        $data = json_decode($response, true);

        $this->assertEquals('success', $data['status']);
    }
}