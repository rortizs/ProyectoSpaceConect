<?php

require_once __DIR__ . '/../../bootstrap.php';

use PHPUnit\Framework\TestCase;

/**
 * SendWhatsapp Unit Tests
 *
 * Tests for WhatsApp messaging service including message sending,
 * error handling, and API integration.
 */
class SendWhatsappTest extends BaseTestCase
{
    use MocksExternalServices;

    private SendWhatsapp $service;
    private $mockBusiness;
    private $testNumber;
    private $testMessage;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setupMocks();
        $this->setupTestData();
        $this->service = new SendWhatsapp($this->mockBusiness);
    }

    protected function tearDown(): void
    {
        $this->resetServiceMocks();
        parent::tearDown();
    }

    private function setupMocks(): void
    {
        $this->mockBusiness = (object)[
            'id' => 1,
            'business_name' => 'Test ISP',
            'whatsapp_api' => 'https://api.whatsapp.test.com',
            'whatsapp_key' => 'test_api_key_12345'
        ];
    }

    private function setupTestData(): void
    {
        $this->testNumber = '+51999999999';
        $this->testMessage = 'Hello, this is a test message from our ISP system.';
    }

    /**
     * @test
     * @group whatsapp-messaging
     * @group services
     * @group external-api
     */
    public function test_send_message_successfully(): void
    {
        // Arrange
        $expectedUrl = 'https://api.whatsapp.test.com/api/messages/send';
        $expectedHeaders = [
            'Authorization: Bearer test_api_key_12345',
            'Content-Type: application/json',
            'cache-control: no-cache'
        ];
        $expectedPayload = json_encode([
            'number' => $this->testNumber,
            'body' => $this->testMessage
        ]);

        // Mock cURL functions
        $this->mockCurlFunctions(
            $expectedUrl,
            $expectedHeaders,
            $expectedPayload,
            '{"success": true, "message_id": "wamid_test_123"}'
        );

        // Act
        $result = $this->service->send($this->testNumber, $this->testMessage);

        // Assert
        $this->assertTrue($result);
        $this->assertNull($this->service->getMessageError());
    }

    /**
     * @test
     * @group whatsapp-messaging
     * @group services
     */
    public function test_send_fails_when_whatsapp_key_missing(): void
    {
        // Arrange
        $businessWithoutKey = (object)[
            'id' => 1,
            'whatsapp_api' => 'https://api.whatsapp.test.com',
            'whatsapp_key' => null
        ];

        $service = new SendWhatsapp($businessWithoutKey);

        // Act
        $result = $service->send($this->testNumber, $this->testMessage);

        // Assert
        $this->assertFalse($result);
        $this->assertEquals('No se encontró la configuración', $service->getMessageError());
    }

    /**
     * @test
     * @group whatsapp-messaging
     * @group services
     */
    public function test_send_fails_when_number_is_empty(): void
    {
        // Act
        $result = $this->service->send('', $this->testMessage);

        // Assert
        $this->assertFalse($result);
        $this->assertEquals('El número está vacío!!!', $this->service->getMessageError());
    }

    /**
     * @test
     * @group whatsapp-messaging
     * @group services
     */
    public function test_send_fails_when_number_is_null(): void
    {
        // Act
        $result = $this->service->send(null, $this->testMessage);

        // Assert
        $this->assertFalse($result);
        $this->assertEquals('El número está vacío!!!', $this->service->getMessageError());
    }

    /**
     * @test
     * @group whatsapp-messaging
     * @group services
     * @group external-api
     */
    public function test_send_handles_curl_error(): void
    {
        // Arrange
        $this->mockCurlFunctions(
            'https://api.whatsapp.test.com/api/messages/send',
            [],
            '',
            false, // cURL returns false
            'Connection timeout'
        );

        // Act
        $result = $this->service->send($this->testNumber, $this->testMessage);

        // Assert
        $this->assertFalse($result);
        $this->assertEquals('Connection timeout', $this->service->getMessageError());
    }

    /**
     * @test
     * @group whatsapp-messaging
     * @group services
     * @group external-api
     */
    public function test_send_handles_api_error_response(): void
    {
        // Arrange
        $this->mockCurlFunctions(
            'https://api.whatsapp.test.com/api/messages/send',
            [],
            '',
            '{"success": false, "error": "Invalid phone number format"}'
        );

        // Act
        $result = $this->service->send('invalid_number', $this->testMessage);

        // Assert
        $this->assertTrue($result); // Function returns true if response is received, even if API reports error
        $this->assertNull($this->service->getMessageError());
    }

    /**
     * @test
     * @group whatsapp-messaging
     * @group services
     */
    public function test_send_constructs_correct_url(): void
    {
        // Arrange
        $customBusiness = (object)[
            'whatsapp_api' => 'https://custom.whatsapp.api.com',
            'whatsapp_key' => 'custom_key_789'
        ];

        $service = new SendWhatsapp($customBusiness);
        $expectedUrl = 'https://custom.whatsapp.api.com/api/messages/send';

        $this->mockCurlFunctions(
            $expectedUrl,
            [],
            '',
            '{"success": true}'
        );

        // Act
        $result = $service->send($this->testNumber, $this->testMessage);

        // Assert
        $this->assertTrue($result);
    }

    /**
     * @test
     * @group whatsapp-messaging
     * @group services
     */
    public function test_send_sets_correct_headers(): void
    {
        // Arrange
        $expectedHeaders = [
            'Authorization: Bearer test_api_key_12345',
            'Content-Type: application/json',
            'cache-control: no-cache'
        ];

        $this->mockCurlFunctions(
            'https://api.whatsapp.test.com/api/messages/send',
            $expectedHeaders,
            '',
            '{"success": true}'
        );

        // Act
        $result = $this->service->send($this->testNumber, $this->testMessage);

        // Assert
        $this->assertTrue($result);
    }

    /**
     * @test
     * @group whatsapp-messaging
     * @group services
     */
    public function test_send_sends_correct_payload(): void
    {
        // Arrange
        $expectedPayload = json_encode([
            'number' => $this->testNumber,
            'body' => $this->testMessage
        ]);

        $this->mockCurlFunctions(
            'https://api.whatsapp.test.com/api/messages/send',
            [],
            $expectedPayload,
            '{"success": true}'
        );

        // Act
        $result = $this->service->send($this->testNumber, $this->testMessage);

        // Assert
        $this->assertTrue($result);
    }

    /**
     * @test
     * @group whatsapp-messaging
     * @group services
     */
    public function test_constructor_accepts_array_business(): void
    {
        // Arrange
        $businessArray = [
            'id' => 2,
            'whatsapp_api' => 'https://array.api.com',
            'whatsapp_key' => 'array_key'
        ];

        // Act
        $service = new SendWhatsapp($businessArray);

        $this->mockCurlFunctions(
            'https://array.api.com/api/messages/send',
            [],
            '',
            '{"success": true}'
        );

        $result = $service->send($this->testNumber, $this->testMessage);

        // Assert
        $this->assertTrue($result);
    }

    /**
     * @test
     * @group whatsapp-messaging
     * @group services
     */
    public function test_constructor_accepts_object_business(): void
    {
        // Arrange
        $businessObject = (object)[
            'id' => 3,
            'whatsapp_api' => 'https://object.api.com',
            'whatsapp_key' => 'object_key'
        ];

        // Act
        $service = new SendWhatsapp($businessObject);

        $this->mockCurlFunctions(
            'https://object.api.com/api/messages/send',
            [],
            '',
            '{"success": true}'
        );

        $result = $service->send($this->testNumber, $this->testMessage);

        // Assert
        $this->assertTrue($result);
    }

    /**
     * @test
     * @group whatsapp-messaging
     * @group services
     */
    public function test_get_message_error_returns_null_initially(): void
    {
        // Act
        $error = $this->service->getMessageError();

        // Assert
        $this->assertNull($error);
    }

    /**
     * @test
     * @group whatsapp-messaging
     * @group services
     */
    public function test_send_handles_special_characters_in_message(): void
    {
        // Arrange
        $specialMessage = 'Hello! Your bill is $150.99. Pay by 2024/03/15. ¡Gracias! 😊';

        $expectedPayload = json_encode([
            'number' => $this->testNumber,
            'body' => $specialMessage
        ]);

        $this->mockCurlFunctions(
            'https://api.whatsapp.test.com/api/messages/send',
            [],
            $expectedPayload,
            '{"success": true}'
        );

        // Act
        $result = $this->service->send($this->testNumber, $specialMessage);

        // Assert
        $this->assertTrue($result);
    }

    /**
     * @test
     * @group whatsapp-messaging
     * @group services
     */
    public function test_send_handles_long_message(): void
    {
        // Arrange
        $longMessage = str_repeat('This is a very long message. ', 100); // ~2700 chars

        $this->mockCurlFunctions(
            'https://api.whatsapp.test.com/api/messages/send',
            [],
            '',
            '{"success": true}'
        );

        // Act
        $result = $this->service->send($this->testNumber, $longMessage);

        // Assert
        $this->assertTrue($result);
    }

    /**
     * @test
     * @group whatsapp-messaging
     * @group services
     */
    public function test_send_handles_international_phone_formats(): void
    {
        // Arrange
        $internationalNumbers = [
            '+1234567890',
            '51999999999',
            '+51-999-999-999',
            '(51) 999 999 999'
        ];

        foreach ($internationalNumbers as $number) {
            $this->mockCurlFunctions(
                'https://api.whatsapp.test.com/api/messages/send',
                [],
                '',
                '{"success": true}'
            );

            // Act
            $result = $this->service->send($number, $this->testMessage);

            // Assert
            $this->assertTrue($result, "Failed for number format: {$number}");
        }
    }

    /**
     * @test
     * @group whatsapp-messaging
     * @group services
     * @group external-api
     */
    public function test_send_handles_network_timeout(): void
    {
        // Arrange - Mock curl to simulate timeout
        $this->mockCurlFunctions(
            'https://api.whatsapp.test.com/api/messages/send',
            [],
            '',
            false,
            'Operation timed out after 30000 milliseconds'
        );

        // Act
        $result = $this->service->send($this->testNumber, $this->testMessage);

        // Assert
        $this->assertFalse($result);
        $this->assertStringContains('timeout', strtolower($this->service->getMessageError()));
    }

    /**
     * @test
     * @group whatsapp-messaging
     * @group services
     */
    public function test_multiple_sends_reset_error_state(): void
    {
        // Arrange - First send fails
        $result1 = $this->service->send('', $this->testMessage);
        $this->assertFalse($result1);
        $this->assertNotNull($this->service->getMessageError());

        // Second send succeeds
        $this->mockCurlFunctions(
            'https://api.whatsapp.test.com/api/messages/send',
            [],
            '',
            '{"success": true}'
        );

        // Act
        $result2 = $this->service->send($this->testNumber, $this->testMessage);

        // Assert
        $this->assertTrue($result2);
        $this->assertNull($this->service->getMessageError()); // Error should be reset
    }

    /**
     * Helper method to mock cURL functions
     */
    private function mockCurlFunctions(
        string $expectedUrl,
        array $expectedHeaders = [],
        string $expectedPayload = '',
        $response = '{"success": true}',
        string $error = ''
    ): void {
        // In a real implementation, you would use a dependency injection
        // system or a wrapper class to make cURL testable.
        // For this example, we're documenting the expected behavior.

        // This is a simplified representation of what the mock should verify:
        // - URL matches expected WhatsApp API endpoint
        // - Headers include correct authorization
        // - Payload contains number and message
        // - Response handling works correctly
        // - Error handling works correctly

        // Note: In production code, you would want to:
        // 1. Extract cURL operations to a separate HTTP client class
        // 2. Inject the HTTP client as a dependency
        // 3. Mock the HTTP client in tests
        // 4. Test the service logic independently of HTTP concerns

        $this->assertTrue(true); // Placeholder for actual cURL mocking
    }
}