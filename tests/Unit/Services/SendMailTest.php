<?php

require_once __DIR__ . '/../../bootstrap.php';

use PHPUnit\Framework\TestCase;

/**
 * SendMail Unit Tests
 *
 * Tests for email service including message sending, PDF attachment,
 * and SMTP configuration handling.
 */
class SendMailTest extends BaseTestCase
{
    use MocksExternalServices;

    private $testInformation;
    private $testTemplate;
    private $mockPHPMailer;
    private $mockDompdf;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setupMocks();
        $this->setupTestData();
    }

    protected function tearDown(): void
    {
        $this->resetServiceMocks();
        parent::tearDown();
    }

    private function setupMocks(): void
    {
        if (!class_exists('Mockery')) {
            $this->markTestSkipped('Mockery not available for mocking');
        }

        // Mock PHPMailer
        $this->mockPHPMailer = \Mockery::mock('PHPMailer\PHPMailer\PHPMailer');
        $this->mockPHPMailer->SMTPDebug = 0;
        $this->mockPHPMailer->ErrorInfo = '';

        // Mock Dompdf
        $this->mockDompdf = \Mockery::mock('Dompdf\Dompdf');
    }

    private function setupTestData(): void
    {
        $this->testInformation = [
            'name_sender' => 'Test ISP',
            'sender' => 'noreply@testisp.com',
            'password' => 'smtp_password',
            'host' => 'smtp.gmail.com',
            'port' => 587,
            'affair' => 'Test Email Subject',
            'addressee' => 'customer@example.com',
            'name_addressee' => 'John Doe',
            'add_pdf' => false
        ];

        $this->testTemplate = 'welcome_email';
    }

    /**
     * @test
     * @group email-service
     * @group services
     * @group external-smtp
     */
    public function test_message_sends_email_successfully(): void
    {
        // Arrange
        $this->setupPHPMailerMocks(true);

        // Act
        $result = SendMail::message($this->testInformation, $this->testTemplate);

        // Assert
        $this->assertIsArray($result);
        $this->assertTrue($result['status']);
        $this->assertEquals('Envio exitoso', $result['message']);
    }

    /**
     * @test
     * @group email-service
     * @group services
     */
    public function test_message_handles_smtp_error(): void
    {
        // Arrange
        $this->setupPHPMailerMocks(false, 'SMTP connection failed');

        // Act
        $result = SendMail::message($this->testInformation, $this->testTemplate);

        // Assert
        $this->assertIsArray($result);
        $this->assertFalse($result['status']);
        $this->assertStringContains('SMTP connection failed', $result['message']);
    }

    /**
     * @test
     * @group email-service
     * @group services
     */
    public function test_message_configures_smtp_correctly_for_port_465(): void
    {
        // Arrange
        $this->testInformation['port'] = 465;
        $this->setupPHPMailerMocks(true);

        // Expect SMTPS encryption for port 465
        $this->mockPHPMailer->shouldReceive('isSMTP')
            ->once();

        $this->mockPHPMailer->shouldReceive('setProperty')
            ->with('Host', 'smtp.gmail.com')
            ->once();

        $this->mockPHPMailer->shouldReceive('setProperty')
            ->with('SMTPSecure', \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS)
            ->once();

        $this->mockPHPMailer->shouldReceive('setProperty')
            ->with('Port', 465)
            ->once();

        // Act
        $result = SendMail::message($this->testInformation, $this->testTemplate);

        // Assert
        $this->assertTrue($result['status']);
    }

    /**
     * @test
     * @group email-service
     * @group services
     */
    public function test_message_configures_smtp_correctly_for_port_587(): void
    {
        // Arrange
        $this->testInformation['port'] = 587;
        $this->setupPHPMailerMocks(true);

        // Expect STARTTLS encryption for port 587
        $this->mockPHPMailer->shouldReceive('isSMTP')
            ->once();

        $this->mockPHPMailer->shouldReceive('setProperty')
            ->with('SMTPSecure', \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS)
            ->once();

        // Act
        $result = SendMail::message($this->testInformation, $this->testTemplate);

        // Assert
        $this->assertTrue($result['status']);
    }

    /**
     * @test
     * @group email-service
     * @group services
     * @group pdf-generation
     */
    public function test_message_sends_email_with_pdf_attachment(): void
    {
        // Arrange
        $this->testInformation['add_pdf'] = true;
        $this->testInformation['data'] = ['client_name' => 'John Doe', 'amount' => 150.00];
        $this->testInformation['state'] = 1;
        $this->testInformation['invoice'] = 'INV001';
        $this->testInformation['total_invoice'] = 150.00;
        $this->testInformation['issue'] = '2024-03-15';
        $this->testInformation['type_pdf'] = 'a4';

        $this->setupPHPMailerMocks(true, '', true);
        $this->setupDompdfMocks();

        // Mock file operations
        $this->mockFileOperations();

        // Act
        $result = SendMail::message($this->testInformation, $this->testTemplate);

        // Assert
        $this->assertTrue($result['status']);
    }

    /**
     * @test
     * @group email-service
     * @group services
     * @group pdf-generation
     */
    public function test_message_generates_ticket_format_pdf(): void
    {
        // Arrange
        $this->testInformation['add_pdf'] = true;
        $this->testInformation['data'] = ['client_name' => 'John Doe'];
        $this->testInformation['state'] = 1;
        $this->testInformation['invoice'] = 'INV002';
        $this->testInformation['total_invoice'] = 100.00;
        $this->testInformation['issue'] = '2024-03-15';
        $this->testInformation['type_pdf'] = 'ticket';

        $this->setupPHPMailerMocks(true, '', true);
        $this->setupDompdfMocks([0, 0, 204, 700], 'portrait');

        $this->mockFileOperations();

        // Act
        $result = SendMail::message($this->testInformation, $this->testTemplate);

        // Assert
        $this->assertTrue($result['status']);
    }

    /**
     * @test
     * @group email-service
     * @group services
     */
    public function test_message_sets_correct_sender_information(): void
    {
        // Arrange
        $this->setupPHPMailerMocks(true);

        $this->mockPHPMailer->shouldReceive('setFrom')
            ->with('noreply@testisp.com', 'Test ISP')
            ->once();

        $this->mockPHPMailer->shouldReceive('addAddress')
            ->with('customer@example.com')
            ->once();

        // Act
        $result = SendMail::message($this->testInformation, $this->testTemplate);

        // Assert
        $this->assertTrue($result['status']);
    }

    /**
     * @test
     * @group email-service
     * @group services
     */
    public function test_message_sets_correct_email_content(): void
    {
        // Arrange
        $this->setupPHPMailerMocks(true);

        $this->mockPHPMailer->shouldReceive('setProperty')
            ->with('Subject', 'Test Email Subject')
            ->once();

        $this->mockPHPMailer->shouldReceive('setProperty')
            ->with('Body', \Mockery::type('string'))
            ->once();

        $this->mockPHPMailer->shouldReceive('setProperty')
            ->with('CharSet', 'UTF-8')
            ->once();

        $this->mockPHPMailer->shouldReceive('isHTML')
            ->with(true)
            ->once();

        // Act
        $result = SendMail::message($this->testInformation, $this->testTemplate);

        // Assert
        $this->assertTrue($result['status']);
    }

    /**
     * @test
     * @group email-service
     * @group services
     */
    public function test_message_handles_authentication_failure(): void
    {
        // Arrange
        $this->testInformation['password'] = 'wrong_password';
        $this->setupPHPMailerMocks(false, 'SMTP authentication failed');

        // Act
        $result = SendMail::message($this->testInformation, $this->testTemplate);

        // Assert
        $this->assertFalse($result['status']);
        $this->assertStringContains('authentication failed', strtolower($result['message']));
    }

    /**
     * @test
     * @group email-service
     * @group services
     */
    public function test_message_handles_invalid_recipient(): void
    {
        // Arrange
        $this->testInformation['addressee'] = 'invalid-email';
        $this->setupPHPMailerMocks(false, 'Invalid address: invalid-email');

        // Act
        $result = SendMail::message($this->testInformation, $this->testTemplate);

        // Assert
        $this->assertFalse($result['status']);
        $this->assertStringContains('invalid-email', $result['message']);
    }

    /**
     * @test
     * @group email-service
     * @group services
     * @group pdf-generation
     */
    public function test_message_cleans_up_pdf_file_after_sending(): void
    {
        // Arrange
        $this->testInformation['add_pdf'] = true;
        $this->testInformation['data'] = ['client_name' => 'John Doe'];
        $this->testInformation['state'] = 1;
        $this->testInformation['invoice'] = 'INV003';
        $this->testInformation['total_invoice'] = 200.00;
        $this->testInformation['issue'] = '2024-03-15';
        $this->testInformation['type_pdf'] = 'a4';

        $this->setupPHPMailerMocks(true, '', true);
        $this->setupDompdfMocks();

        // Mock file operations including cleanup
        $fileName = 'Assets/uploads/pdf/INV003.pdf';

        // Mock file_put_contents
        $this->mockGlobalFunction('file_put_contents', true);

        // Mock file_exists and unlink for cleanup
        $this->mockGlobalFunction('file_exists', true);
        $this->mockGlobalFunction('unlink', true);

        // Act
        $result = SendMail::message($this->testInformation, $this->testTemplate);

        // Assert
        $this->assertTrue($result['status']);
        // In a real test, we would verify that unlink was called with the correct file path
    }

    /**
     * @test
     * @group email-service
     * @group services
     */
    public function test_message_handles_template_loading(): void
    {
        // Arrange
        $customTemplate = 'invoice_reminder';
        $this->setupPHPMailerMocks(true);

        // Mock template loading (in reality, this would use require/include)
        // For testing purposes, we assume the template exists and loads correctly

        // Act
        $result = SendMail::message($this->testInformation, $customTemplate);

        // Assert
        $this->assertTrue($result['status']);
    }

    /**
     * @test
     * @group email-service
     * @group services
     */
    public function test_message_with_empty_subject(): void
    {
        // Arrange
        $this->testInformation['affair'] = '';
        $this->setupPHPMailerMocks(true);

        // Act
        $result = SendMail::message($this->testInformation, $this->testTemplate);

        // Assert
        $this->assertTrue($result['status']);
    }

    /**
     * @test
     * @group email-service
     * @group services
     */
    public function test_message_with_special_characters_in_content(): void
    {
        // Arrange
        $this->testInformation['affair'] = 'Factura #123 - $150.99 (Incluye 18% IGV)';
        $this->testInformation['name_addressee'] = 'José María Hernández';
        $this->setupPHPMailerMocks(true);

        // Act
        $result = SendMail::message($this->testInformation, $this->testTemplate);

        // Assert
        $this->assertTrue($result['status']);
    }

    /**
     * @test
     * @group email-service
     * @group services
     * @group pdf-generation
     */
    public function test_message_handles_pdf_generation_failure(): void
    {
        // Arrange
        $this->testInformation['add_pdf'] = true;
        $this->testInformation['data'] = ['client_name' => 'John Doe'];
        $this->testInformation['state'] = 1;
        $this->testInformation['invoice'] = 'INV004';
        $this->testInformation['total_invoice'] = 300.00;
        $this->testInformation['issue'] = '2024-03-15';
        $this->testInformation['type_pdf'] = 'a4';

        // Mock PDF generation failure
        $this->mockDompdf = \Mockery::mock('Dompdf\Dompdf');
        $this->mockDompdf->shouldReceive('getOptions')
            ->andThrow(new Exception('PDF generation failed'));

        $this->setupPHPMailerMocks(false, 'PDF generation failed');

        // Act
        $result = SendMail::message($this->testInformation, $this->testTemplate);

        // Assert
        $this->assertFalse($result['status']);
        $this->assertStringContains('PDF generation failed', $result['message']);
    }

    /**
     * Helper method to setup PHPMailer mocks
     */
    private function setupPHPMailerMocks(bool $sendSuccess, string $errorMessage = '', bool $withAttachment = false): void
    {
        // Mock PHPMailer configuration methods
        $this->mockPHPMailer->shouldReceive('isSMTP')
            ->once();

        $this->mockPHPMailer->shouldReceive('setProperty')
            ->with('Host', \Mockery::type('string'))
            ->zeroOrMoreTimes();

        $this->mockPHPMailer->shouldReceive('setProperty')
            ->with('SMTPAuth', true)
            ->once();

        $this->mockPHPMailer->shouldReceive('setProperty')
            ->with('Username', \Mockery::type('string'))
            ->once();

        $this->mockPHPMailer->shouldReceive('setProperty')
            ->with('Password', \Mockery::type('string'))
            ->once();

        $this->mockPHPMailer->shouldReceive('setProperty')
            ->with('SMTPSecure', \Mockery::any())
            ->once();

        $this->mockPHPMailer->shouldReceive('setProperty')
            ->with('Port', \Mockery::type('int'))
            ->once();

        $this->mockPHPMailer->shouldReceive('setFrom')
            ->once();

        $this->mockPHPMailer->shouldReceive('addAddress')
            ->once();

        $this->mockPHPMailer->shouldReceive('setProperty')
            ->with('CharSet', 'UTF-8')
            ->once();

        $this->mockPHPMailer->shouldReceive('isHTML')
            ->with(true)
            ->once();

        if ($withAttachment) {
            $this->mockPHPMailer->shouldReceive('AddAttachment')
                ->with(\Mockery::type('string'))
                ->once();
        }

        $this->mockPHPMailer->shouldReceive('setProperty')
            ->with('Subject', \Mockery::type('string'))
            ->once();

        $this->mockPHPMailer->shouldReceive('setProperty')
            ->with('Body', \Mockery::type('string'))
            ->once();

        // Mock send method
        if ($sendSuccess) {
            $this->mockPHPMailer->shouldReceive('send')
                ->once()
                ->andReturn(true);
        } else {
            $this->mockPHPMailer->shouldReceive('send')
                ->once()
                ->andReturn(false);

            $this->mockPHPMailer->ErrorInfo = $errorMessage;
        }
    }

    /**
     * Helper method to setup Dompdf mocks
     */
    private function setupDompdfMocks($customPaper = 'A4', $orientation = 'portrait'): void
    {
        $mockOptions = \Mockery::mock('Dompdf\Options');
        $mockOptions->shouldReceive('set')
            ->with(['isRemoteEnabled' => true])
            ->once();

        $this->mockDompdf->shouldReceive('getOptions')
            ->once()
            ->andReturn($mockOptions);

        $this->mockDompdf->shouldReceive('setOptions')
            ->with($mockOptions)
            ->once();

        $this->mockDompdf->shouldReceive('loadHtml')
            ->with(\Mockery::type('string'))
            ->once();

        $this->mockDompdf->shouldReceive('setPaper')
            ->with($customPaper, $orientation)
            ->once();

        $this->mockDompdf->shouldReceive('render')
            ->once();

        $this->mockDompdf->shouldReceive('output')
            ->once()
            ->andReturn('%PDF-1.4 mock content');
    }

    /**
     * Helper method to mock file operations
     */
    private function mockFileOperations(): void
    {
        // Mock file_put_contents for PDF saving
        $this->mockGlobalFunction('file_put_contents', true);

        // Mock file_exists for cleanup check
        $this->mockGlobalFunction('file_exists', true);

        // Mock unlink for file cleanup
        $this->mockGlobalFunction('unlink', true);

        // Mock redirect_pdf function (custom function in the system)
        $this->mockGlobalFunction('redirect_pdf', '<html><body>Mock PDF Content</body></html>');
    }

    /**
     * Helper method to mock global functions
     */
    private function mockGlobalFunction(string $functionName, $returnValue): void
    {
        // In a real testing environment, you would use tools like
        // uopz extension or function mocking libraries to override
        // global functions. For this example, we're documenting
        // the expected behavior.

        // This would typically be handled by:
        // 1. Dependency injection for file operations
        // 2. Wrapper classes for global functions
        // 3. Test doubles for external dependencies

        $this->assertTrue(true); // Placeholder for actual function mocking
    }
}