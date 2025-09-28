<?php

/**
 * Mocks External Services Trait
 *
 * Provides utilities for mocking external service dependencies.
 */
trait MocksExternalServices
{
    /**
     * Collection of service mocks
     */
    protected array $serviceMocks = [];

    /**
     * Mock WhatsApp service
     */
    protected function mockWhatsAppService(): void
    {
        if (!class_exists('Mockery')) {
            return;
        }

        $mock = \Mockery::mock('SendWhatsapp');

        // Mock successful message sending
        $mock->shouldReceive('sendMessage')
             ->andReturn([
                 'success' => true,
                 'message' => 'Message sent successfully',
                 'message_id' => 'wamid_test_' . uniqid()
             ])
             ->byDefault();

        // Mock message status check
        $mock->shouldReceive('getMessageStatus')
             ->andReturn([
                 'status' => 'delivered',
                 'timestamp' => time()
             ])
             ->byDefault();

        $this->serviceMocks['whatsapp'] = $mock;
    }

    /**
     * Mock email service
     */
    protected function mockEmailService(): void
    {
        if (!class_exists('Mockery')) {
            return;
        }

        $mock = \Mockery::mock('SendMail');

        // Mock successful email sending
        $mock->shouldReceive('sendEmail')
             ->andReturn([
                 'success' => true,
                 'message' => 'Email sent successfully',
                 'message_id' => 'email_' . uniqid()
             ])
             ->byDefault();

        // Mock email with attachment
        $mock->shouldReceive('sendEmailWithAttachment')
             ->andReturn([
                 'success' => true,
                 'message' => 'Email with attachment sent successfully',
                 'message_id' => 'email_att_' . uniqid()
             ])
             ->byDefault();

        $this->serviceMocks['email'] = $mock;
    }

    /**
     * Mock payment gateway
     */
    protected function mockPaymentGateway(): void
    {
        if (!class_exists('Mockery')) {
            return;
        }

        $mock = \Mockery::mock('PaymentGateway');

        // Mock successful payment processing
        $mock->shouldReceive('processPayment')
             ->andReturn([
                 'success' => true,
                 'transaction_id' => 'txn_' . uniqid(),
                 'amount' => 100.00,
                 'currency' => 'PEN',
                 'status' => 'completed'
             ])
             ->byDefault();

        // Mock payment verification
        $mock->shouldReceive('verifyPayment')
             ->andReturn([
                 'verified' => true,
                 'status' => 'completed',
                 'amount' => 100.00
             ])
             ->byDefault();

        // Mock refund processing
        $mock->shouldReceive('processRefund')
             ->andReturn([
                 'success' => true,
                 'refund_id' => 'ref_' . uniqid(),
                 'amount' => 100.00,
                 'status' => 'refunded'
             ])
             ->byDefault();

        $this->serviceMocks['payment'] = $mock;
    }

    /**
     * Mock file storage service
     */
    protected function mockFileStorageService(): void
    {
        if (!class_exists('Mockery')) {
            return;
        }

        $mock = \Mockery::mock('FileStorage');

        // Mock file upload
        $mock->shouldReceive('uploadFile')
             ->andReturn([
                 'success' => true,
                 'file_path' => '/uploads/test/test_file_' . uniqid() . '.pdf',
                 'file_size' => 1024,
                 'mime_type' => 'application/pdf'
             ])
             ->byDefault();

        // Mock file deletion
        $mock->shouldReceive('deleteFile')
             ->andReturn(['success' => true])
             ->byDefault();

        // Mock file existence check
        $mock->shouldReceive('fileExists')
             ->andReturn(true)
             ->byDefault();

        $this->serviceMocks['storage'] = $mock;
    }

    /**
     * Mock PDF generation service
     */
    protected function mockPdfGenerationService(): void
    {
        if (!class_exists('Mockery')) {
            return;
        }

        $mock = \Mockery::mock('PdfGenerator');

        // Mock PDF generation
        $mock->shouldReceive('generatePdf')
             ->andReturn([
                 'success' => true,
                 'file_path' => '/uploads/pdf/invoice_' . uniqid() . '.pdf',
                 'file_size' => 2048
             ])
             ->byDefault();

        // Mock PDF content
        $mock->shouldReceive('getPdfContent')
             ->andReturn('%PDF-1.4 mock content')
             ->byDefault();

        $this->serviceMocks['pdf'] = $mock;
    }

    /**
     * Mock API client for external services
     */
    protected function mockApiClient(string $serviceName): void
    {
        if (!class_exists('Mockery')) {
            return;
        }

        $mock = \Mockery::mock("ApiClient_{$serviceName}");

        // Mock GET request
        $mock->shouldReceive('get')
             ->andReturn([
                 'status' => 200,
                 'data' => ['result' => 'success'],
                 'headers' => ['Content-Type' => 'application/json']
             ])
             ->byDefault();

        // Mock POST request
        $mock->shouldReceive('post')
             ->andReturn([
                 'status' => 201,
                 'data' => ['created' => true, 'id' => uniqid()],
                 'headers' => ['Content-Type' => 'application/json']
             ])
             ->byDefault();

        // Mock PUT request
        $mock->shouldReceive('put')
             ->andReturn([
                 'status' => 200,
                 'data' => ['updated' => true],
                 'headers' => ['Content-Type' => 'application/json']
             ])
             ->byDefault();

        // Mock DELETE request
        $mock->shouldReceive('delete')
             ->andReturn([
                 'status' => 204,
                 'data' => null,
                 'headers' => []
             ])
             ->byDefault();

        $this->serviceMocks['api_' . $serviceName] = $mock;
    }

    /**
     * Configure mock to return error responses
     */
    protected function configureMockError(string $serviceName, string $method, string $error, int $code = 500): void
    {
        if (!isset($this->serviceMocks[$serviceName])) {
            return;
        }

        $this->serviceMocks[$serviceName]
             ->shouldReceive($method)
             ->andReturn([
                 'success' => false,
                 'error' => $error,
                 'error_code' => $code
             ])
             ->once();
    }

    /**
     * Configure mock to throw exception
     */
    protected function configureMockException(string $serviceName, string $method, string $exceptionClass = 'Exception', string $message = 'Service unavailable'): void
    {
        if (!isset($this->serviceMocks[$serviceName])) {
            return;
        }

        $this->serviceMocks[$serviceName]
             ->shouldReceive($method)
             ->andThrow(new $exceptionClass($message))
             ->once();
    }

    /**
     * Get mock for a specific service
     */
    protected function getMock(string $serviceName)
    {
        return $this->serviceMocks[$serviceName] ?? null;
    }

    /**
     * Assert that a service method was called
     */
    protected function assertServiceMethodCalled(string $serviceName, string $method, array $parameters = []): void
    {
        if (!isset($this->serviceMocks[$serviceName])) {
            $this->fail("Mock for service '{$serviceName}' not found");
        }

        $expectation = $this->serviceMocks[$serviceName]
                          ->shouldHaveReceived($method)
                          ->atLeast()
                          ->once();

        if (!empty($parameters)) {
            $expectation->with(\Mockery::subset($parameters));
        }
    }

    /**
     * Assert that a service method was not called
     */
    protected function assertServiceMethodNotCalled(string $serviceName, string $method): void
    {
        if (!isset($this->serviceMocks[$serviceName])) {
            return; // If mock doesn't exist, method wasn't called
        }

        $this->serviceMocks[$serviceName]
             ->shouldNotHaveReceived($method);
    }

    /**
     * Reset all service mocks
     */
    protected function resetServiceMocks(): void
    {
        if (class_exists('Mockery')) {
            \Mockery::close();
        }
        $this->serviceMocks = [];
    }

    /**
     * Setup all common service mocks
     */
    protected function setupAllServiceMocks(): void
    {
        $this->mockWhatsAppService();
        $this->mockEmailService();
        $this->mockPaymentGateway();
        $this->mockFileStorageService();
        $this->mockPdfGenerationService();
    }

    /**
     * Mock HTTP response for cURL requests
     */
    protected function mockHttpResponse(int $statusCode = 200, array $body = [], array $headers = []): array
    {
        return [
            'status_code' => $statusCode,
            'body' => json_encode($body),
            'headers' => array_merge([
                'Content-Type' => 'application/json',
                'Date' => date('r')
            ], $headers),
            'response_time' => rand(100, 500)
        ];
    }

    /**
     * Mock successful HTTP response
     */
    protected function mockSuccessfulHttpResponse(array $data = []): array
    {
        return $this->mockHttpResponse(200, array_merge(['success' => true], $data));
    }

    /**
     * Mock error HTTP response
     */
    protected function mockErrorHttpResponse(string $message = 'Internal server error', int $code = 500): array
    {
        return $this->mockHttpResponse($code, [
            'success' => false,
            'error' => $message,
            'error_code' => $code
        ]);
    }

    /**
     * Mock timeout HTTP response
     */
    protected function mockTimeoutHttpResponse(): array
    {
        return [
            'status_code' => 0,
            'body' => '',
            'headers' => [],
            'error' => 'Operation timed out after 30000 milliseconds',
            'response_time' => 30000
        ];
    }
}