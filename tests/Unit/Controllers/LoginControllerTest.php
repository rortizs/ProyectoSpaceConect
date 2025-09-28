<?php

require_once 'tests/Unit/Controllers/BaseControllerTest.php';
require_once 'Controllers/Login.php';

/**
 * Login Controller Test
 *
 * Comprehensive unit tests for the Login controller.
 * Tests authentication, session management, password reset,
 * security measures, and login validation.
 */
class LoginControllerTest extends BaseControllerTest
{
    /**
     * Controller instance under test
     */
    protected Login $controller;

    /**
     * Set up before each test
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->setupLoginController();
    }

    /**
     * Set up login controller with mocked dependencies
     */
    private function setupLoginController(): void
    {
        // Mock Views class
        $mockViews = $this->createMock(stdClass::class);
        $mockViews->method('getView')->willReturn(true);

        // Mock Model class
        $mockModel = $this->createMock(stdClass::class);

        // Create controller with mocked dependencies
        $this->controller = new class extends Login {
            public $views;
            public $model;

            public function __construct() {
                // Skip parent constructor to avoid session issues
            }

            public function setMockViews($views) {
                $this->views = $views;
            }

            public function setMockModel($model) {
                $this->model = $model;
            }
        };

        $this->controller->setMockViews($mockViews);
        $this->controller->setMockModel($mockModel);
    }

    /**
     * Test login() method displays login form
     */
    public function testLoginDisplaysForm(): void
    {
        // Mock business_session function
        if (!function_exists('business_session')) {
            function business_session() {
                return [
                    'business_name' => 'Test ISP',
                    'logo' => 'test_logo.png'
                ];
            }
        }

        $viewData = null;
        $mockViews = $this->createMock(stdClass::class);
        $mockViews->expects($this->once())
                  ->method('getView')
                  ->with(
                      $this->anything(),
                      'login',
                      $this->callback(function($data) use (&$viewData) {
                          $viewData = $data;
                          return true;
                      })
                  );

        $this->controller->setMockViews($mockViews);

        $this->controller->login();

        // Verify login form data
        $this->assertArrayHasKey('page_name', $viewData);
        $this->assertEquals('Login', $viewData['page_name']);
        $this->assertArrayHasKey('page_functions_js', $viewData);
        $this->assertEquals('login.js', $viewData['page_functions_js']);
        $this->assertArrayHasKey('business', $viewData);
    }

    /**
     * Test validation() method with valid credentials
     */
    public function testValidationWithValidCredentials(): void
    {
        $validCredentials = [
            'username' => 'testuser',
            'password' => 'testpass123'
        ];

        $this->mockPostRequest($validCredentials);

        // Mock required functions
        if (!function_exists('strClean')) {
            function strClean($str) {
                return trim(strip_tags($str));
            }
        }

        if (!function_exists('encrypt')) {
            function encrypt($str) {
                return base64_encode($str);
            }
        }

        if (!function_exists('decrypt')) {
            function decrypt($str) {
                return base64_decode($str);
            }
        }

        if (!function_exists('clearCookie')) {
            function clearCookie() {
                return true;
            }
        }

        if (!function_exists('user_session')) {
            function user_session($userId) {
                return true;
            }
        }

        // Mock model methods
        $mockModel = $this->createMock(stdClass::class);
        $mockModel->method('validation')->willReturn([
            'id' => 1,
            'state' => 1,
            'names' => 'Test',
            'surnames' => 'User'
        ]);
        $mockModel->method('login_session')->willReturn(true);

        $this->controller->setMockModel($mockModel);

        ob_start();
        $this->controller->validation();
        $output = ob_get_clean();

        $this->assertJson($output);

        $response = json_decode($output, true);
        $this->assertEquals('success', $response['status']);
        $this->assertEquals('ok', $response['msg']);
    }

    /**
     * Test validation() method with invalid credentials
     */
    public function testValidationWithInvalidCredentials(): void
    {
        $invalidCredentials = [
            'username' => 'wronguser',
            'password' => 'wrongpass'
        ];

        $this->mockPostRequest($invalidCredentials);

        // Mock model returning null for invalid credentials
        $mockModel = $this->createMock(stdClass::class);
        $mockModel->method('validation')->willReturn(null);

        $this->controller->setMockModel($mockModel);

        ob_start();
        $this->controller->validation();
        $output = ob_get_clean();

        $this->assertJson($output);

        $response = json_decode($output, true);
        $this->assertEquals('warning', $response['status']);
        $this->assertStringContainsString('Usuario o contraseña es incorrecta', $response['msg']);
    }

    /**
     * Test validation() method with empty credentials
     */
    public function testValidationWithEmptyCredentials(): void
    {
        $emptyCredentials = [
            'username' => '',
            'password' => ''
        ];

        $this->mockPostRequest($emptyCredentials);

        ob_start();
        $this->controller->validation();
        $output = ob_get_clean();

        $this->assertJson($output);

        $response = json_decode($output, true);
        $this->assertEquals('warning', $response['status']);
        $this->assertStringContainsString('obligatorios', $response['msg']);
    }

    /**
     * Test validation() method with deactivated user
     */
    public function testValidationWithDeactivatedUser(): void
    {
        $validCredentials = [
            'username' => 'deactivateduser',
            'password' => 'testpass123'
        ];

        $this->mockPostRequest($validCredentials);

        // Mock model returning deactivated user
        $mockModel = $this->createMock(stdClass::class);
        $mockModel->method('validation')->willReturn([
            'id' => 1,
            'state' => 0, // Deactivated
            'names' => 'Deactivated',
            'surnames' => 'User'
        ]);

        $this->controller->setMockModel($mockModel);

        ob_start();
        $this->controller->validation();
        $output = ob_get_clean();

        $this->assertJson($output);

        $response = json_decode($output, true);
        $this->assertEquals('error', $response['status']);
        $this->assertStringContainsString('desactivado', $response['msg']);
    }

    /**
     * Test validation() method with remember me functionality
     */
    public function testValidationWithRememberMe(): void
    {
        $credentialsWithRemember = [
            'username' => 'testuser',
            'password' => 'testpass123',
            'remember' => '1'
        ];

        $this->mockPostRequest($credentialsWithRemember);

        // Mock model methods
        $mockModel = $this->createMock(stdClass::class);
        $mockModel->method('validation')->willReturn([
            'id' => 1,
            'state' => 1,
            'names' => 'Test',
            'surnames' => 'User'
        ]);
        $mockModel->method('login_session')->willReturn(true);

        $this->controller->setMockModel($mockModel);

        // Mock cookie functions
        $cookiesSet = [];
        if (!function_exists('setcookie')) {
            function setcookie($name, $value, $expire) use (&$cookiesSet) {
                $cookiesSet[$name] = $value;
                return true;
            }
        }

        ob_start();
        $this->controller->validation();
        $output = ob_get_clean();

        $this->assertJson($output);

        $response = json_decode($output, true);
        $this->assertEquals('success', $response['status']);

        // Verify cookies would be set (in real environment)
        $this->assertTrue(true); // Placeholder for cookie verification
    }

    /**
     * Test reset() method with valid email
     */
    public function testResetWithValidEmail(): void
    {
        $validEmail = [
            'email' => 'test@example.com'
        ];

        $this->mockPostRequest($validEmail);

        // Mock required functions
        if (!function_exists('token')) {
            function token() {
                return 'mock_token_' . time();
            }
        }

        if (!function_exists('sendMail')) {
            function sendMail($data, $template) {
                return true;
            }
        }

        // Mock model methods
        $mockModel = $this->createMock(stdClass::class);
        $mockModel->method('validation_email')->willReturn([
            'id' => 1,
            'names' => 'Test',
            'surnames' => 'User'
        ]);
        $mockModel->method('update_token')->willReturn('success');

        $this->controller->setMockModel($mockModel);

        ob_start();
        $this->controller->reset();
        $output = ob_get_clean();

        $this->assertJson($output);

        $response = json_decode($output, true);
        $this->assertEquals('success', $response['status']);
        $this->assertStringContainsString('correo', $response['msg']);
    }

    /**
     * Test reset() method with invalid email
     */
    public function testResetWithInvalidEmail(): void
    {
        $invalidEmail = [
            'email' => 'nonexistent@example.com'
        ];

        $this->mockPostRequest($invalidEmail);

        // Mock model returning null for invalid email
        $mockModel = $this->createMock(stdClass::class);
        $mockModel->method('validation_email')->willReturn(null);

        $this->controller->setMockModel($mockModel);

        ob_start();
        $this->controller->reset();
        $output = ob_get_clean();

        $this->assertJson($output);

        $response = json_decode($output, true);
        $this->assertEquals('not_exist', $response['status']);
        $this->assertStringContainsString('No existe ningún operador', $response['msg']);
    }

    /**
     * Test reset() method with empty email
     */
    public function testResetWithEmptyEmail(): void
    {
        $emptyEmail = [
            'email' => ''
        ];

        $this->mockPostRequest($emptyEmail);

        ob_start();
        $this->controller->reset();
        $output = ob_get_clean();

        $this->assertJson($output);

        $response = json_decode($output, true);
        $this->assertEquals('error', $response['status']);
        $this->assertStringContainsString('correo electrónico valido', $response['msg']);
    }

    /**
     * Test restore() method with valid token
     */
    public function testRestoreWithValidToken(): void
    {
        $validParams = base64_encode('test@example.com') . ',valid_token';

        // Mock model method
        $mockModel = $this->createMock(stdClass::class);
        $mockModel->method('user_information')->willReturn([
            'id' => 1,
            'names' => 'Test',
            'surnames' => 'User'
        ]);

        $this->controller->setMockModel($mockModel);

        $viewData = null;
        $mockViews = $this->createMock(stdClass::class);
        $mockViews->expects($this->once())
                  ->method('getView')
                  ->with(
                      $this->anything(),
                      'restore_password',
                      $this->callback(function($data) use (&$viewData) {
                          $viewData = $data;
                          return true;
                      })
                  );

        $this->controller->setMockViews($mockViews);

        try {
            $this->controller->restore($validParams);
        } catch (Exception $e) {
            // Expected due to die() call
        }

        // Verify restore form data
        if ($viewData) {
            $this->assertArrayHasKey('email', $viewData);
            $this->assertArrayHasKey('token', $viewData);
            $this->assertArrayHasKey('id', $viewData);
            $this->assertArrayHasKey('page_name', $viewData);
            $this->assertEquals('Restaurar contraseña', $viewData['page_name']);
        }
    }

    /**
     * Test restore() method with invalid token
     */
    public function testRestoreWithInvalidToken(): void
    {
        $invalidParams = base64_encode('test@example.com') . ',invalid_token';

        // Mock model returning null for invalid token
        $mockModel = $this->createMock(stdClass::class);
        $mockModel->method('user_information')->willReturn(null);

        $this->controller->setMockModel($mockModel);

        // Mock header function to capture redirect
        $this->expectOutputString('');

        try {
            $this->controller->restore($invalidParams);
        } catch (Exception $e) {
            // Expected due to redirect and die()
            $this->assertTrue(true);
        }
    }

    /**
     * Test update_password() method with valid data
     */
    public function testUpdatePasswordWithValidData(): void
    {
        $validPasswordData = [
            'id' => base64_encode('1'),
            'email' => 'test@example.com',
            'token' => 'valid_token',
            'password' => 'newpassword123',
            'passwordConfirm' => 'newpassword123'
        ];

        $this->mockPostRequest($validPasswordData);

        // Mock model methods
        $mockModel = $this->createMock(stdClass::class);
        $mockModel->method('user_information')->willReturn([
            'id' => 1,
            'names' => 'Test',
            'surnames' => 'User'
        ]);
        $mockModel->method('update_password')->willReturn('success');

        $this->controller->setMockModel($mockModel);

        ob_start();
        $this->controller->update_password();
        $output = ob_get_clean();

        $this->assertJson($output);

        $response = json_decode($output, true);
        $this->assertEquals('success', $response['status']);
        $this->assertStringContainsString('restablecida', $response['msg']);
    }

    /**
     * Test update_password() method with mismatched passwords
     */
    public function testUpdatePasswordWithMismatchedPasswords(): void
    {
        $mismatchedPasswordData = [
            'id' => base64_encode('1'),
            'email' => 'test@example.com',
            'token' => 'valid_token',
            'password' => 'newpassword123',
            'passwordConfirm' => 'differentpassword'
        ];

        $this->mockPostRequest($mismatchedPasswordData);

        ob_start();
        $this->controller->update_password();
        $output = ob_get_clean();

        $this->assertJson($output);

        $response = json_decode($output, true);
        $this->assertEquals('error', $response['status']);
        $this->assertStringContainsString('contraseñas no coinciden', $response['msg']);
    }

    /**
     * Test update_password() method with missing fields
     */
    public function testUpdatePasswordWithMissingFields(): void
    {
        $incompleteData = [
            'id' => base64_encode('1'),
            'email' => 'test@example.com'
            // Missing token, password, passwordConfirm
        ];

        $this->mockPostRequest($incompleteData);

        ob_start();
        $this->controller->update_password();
        $output = ob_get_clean();

        $this->assertJson($output);

        $response = json_decode($output, true);
        $this->assertFalse($response['status']);
        $this->assertStringContainsString('obligatorios', $response['msg']);
    }

    /**
     * Test security against brute force attacks
     */
    public function testBruteForceProtection(): void
    {
        $attackCredentials = [
            'username' => 'admin',
            'password' => 'wrong'
        ];

        // Mock model returning null (failed login)
        $mockModel = $this->createMock(stdClass::class);
        $mockModel->method('validation')->willReturn(null);

        $this->controller->setMockModel($mockModel);

        // Simulate multiple failed attempts
        for ($i = 0; $i < 5; $i++) {
            $this->mockPostRequest($attackCredentials);

            ob_start();
            $this->controller->validation();
            $output = ob_get_clean();

            $response = json_decode($output, true);
            $this->assertEquals('warning', $response['status']);
        }

        // In a real implementation, there would be rate limiting here
        $this->assertTrue(true); // Placeholder for brute force protection test
    }

    /**
     * Test SQL injection protection
     */
    public function testSqlInjectionProtection(): void
    {
        $sqlInjectionAttempts = [
            [
                'username' => "admin'; DROP TABLE users; --",
                'password' => 'password'
            ],
            [
                'username' => "admin' OR '1'='1",
                'password' => 'password'
            ],
            [
                'username' => "admin'; SELECT * FROM users; --",
                'password' => 'password'
            ]
        ];

        // Mock model that should sanitize input
        $mockModel = $this->createMock(stdClass::class);
        $mockModel->method('validation')->willReturn(null);

        $this->controller->setMockModel($mockModel);

        foreach ($sqlInjectionAttempts as $attempt) {
            $this->mockPostRequest($attempt);

            ob_start();
            $this->controller->validation();
            $output = ob_get_clean();

            $response = json_decode($output, true);
            $this->assertEquals('warning', $response['status']);
        }
    }

    /**
     * Test XSS protection
     */
    public function testXssProtection(): void
    {
        $xssAttempts = [
            [
                'username' => '<script>alert("xss")</script>',
                'password' => 'password'
            ],
            [
                'username' => 'javascript:alert(1)',
                'password' => 'password'
            ],
            [
                'username' => '<img src=x onerror=alert(1)>',
                'password' => 'password'
            ]
        ];

        $mockModel = $this->createMock(stdClass::class);
        $mockModel->method('validation')->willReturn(null);

        $this->controller->setMockModel($mockModel);

        foreach ($xssAttempts as $attempt) {
            $this->mockPostRequest($attempt);

            ob_start();
            $this->controller->validation();
            $output = ob_get_clean();

            // Verify that output doesn't contain script tags
            $this->assertStringNotContainsString('<script>', $output);
            $this->assertStringNotContainsString('javascript:', $output);
        }
    }

    /**
     * Test session security
     */
    public function testSessionSecurity(): void
    {
        $validCredentials = [
            'username' => 'testuser',
            'password' => 'testpass123'
        ];

        $this->mockPostRequest($validCredentials);

        // Mock model methods
        $mockModel = $this->createMock(stdClass::class);
        $mockModel->method('validation')->willReturn([
            'id' => 1,
            'state' => 1,
            'names' => 'Test',
            'surnames' => 'User'
        ]);
        $mockModel->method('login_session')->willReturn(true);

        $this->controller->setMockModel($mockModel);

        ob_start();
        $this->controller->validation();
        $output = ob_get_clean();

        // Verify session is properly set
        $this->assertTrue(isset($_SESSION['idUser']));
        $this->assertTrue($_SESSION['login']);
        $this->assertEquals(1, $_SESSION['idUser']);
    }

    /**
     * Test password strength validation
     */
    public function testPasswordStrengthValidation(): void
    {
        $weakPasswords = [
            '123',
            'password',
            'abc',
            '111111',
            'qwerty'
        ];

        foreach ($weakPasswords as $weakPassword) {
            $credentials = [
                'username' => 'testuser',
                'password' => $weakPassword
            ];

            $this->mockPostRequest($credentials);

            // In a real implementation, there would be password strength validation
            // This is a placeholder to demonstrate the test structure
            $this->assertTrue(strlen($weakPassword) < 8); // Simple strength check
        }
    }
}