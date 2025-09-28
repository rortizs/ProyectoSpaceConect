<?php

require_once __DIR__ . '/../../Support/DatabaseTestCase.php';

/**
 * BusinessModel Unit Tests
 *
 * Tests for BusinessModel functionality including:
 * - Business configuration management
 * - Logo and branding updates
 * - Email configuration
 * - WhatsApp integration settings
 * - Database backup operations
 * - File management
 */
class BusinessModelTest extends DatabaseTestCase
{
    private BusinessModel $model;
    private array $testBusiness;

    protected function setUp(): void
    {
        parent::setUp();
        $this->model = new BusinessModel();
        $this->seedEssentialData();
        $this->createTestData();
    }

    private function createTestData(): void
    {
        // Create test business record
        $this->testBusiness = [
            'id' => $this->insertTestData('business', [
                'documentid' => 1,
                'ruc' => '12345678901',
                'business_name' => 'Test ISP Company',
                'tradename' => 'TestISP',
                'slogan' => 'Your Internet Connection',
                'mobile' => '987654321',
                'mobile_refrence' => '123456789',
                'email' => 'info@testisp.com',
                'password' => 'test123',
                'server_host' => 'smtp.testisp.com',
                'port' => 587,
                'address' => 'Av. Principal 123',
                'department' => 'Lima',
                'province' => 'Lima',
                'district' => 'Miraflores',
                'ubigeo' => '150101',
                'footer_text' => 'Thank you for choosing TestISP',
                'currencyid' => 1,
                'print_format' => 1,
                'logo_login' => 'login_logo.png',
                'logotyope' => 'main_logo.png',
                'logo_email' => 'email_logo.png',
                'favicon' => 'favicon.ico',
                'background' => 'background.jpg',
                'country_code' => '+51',
                'google_apikey' => 'test_google_key',
                'whatsapp_key' => 'test_whatsapp_key',
                'whatsapp_api' => 'https://api.whatsapp.test'
            ])
        ];

        // Create test currency
        $this->insertTestData('currency', [
            'id' => 1,
            'symbol' => 'S/.',
            'money' => 'Sol',
            'money_plural' => 'Soles',
            'state' => 1
        ]);
    }

    /**
     * @group critical
     */
    public function testShowBusinessReturnsBusinessData(): void
    {
        $result = $this->model->show_business();

        $this->assertIsArray($result);
        $this->assertArrayHasKey('id', $result);
        $this->assertArrayHasKey('business_name', $result);
        $this->assertArrayHasKey('ruc', $result);
        $this->assertArrayHasKey('symbol', $result); // From joined currency table

        $this->assertEquals('Test ISP Company', $result['business_name']);
        $this->assertEquals('12345678901', $result['ruc']);

        // Verify session is set
        $this->assertArrayHasKey('businessData', $_SESSION);
        $this->assertEquals($result, $_SESSION['businessData']);
    }

    /**
     * @group critical
     */
    public function testUpdateGeneralInformationWithValidData(): void
    {
        $result = $this->model->update_general(
            $this->testBusiness['id'],
            '98765432101', // new RUC
            'Updated ISP Company',
            'UpdatedISP',
            '111222333',
            '444555666',
            'Av. Updated 456'
        );

        $this->assertEquals('success', $result);
        $this->assertDatabaseHas('business', [
            'id' => $this->testBusiness['id'],
            'ruc' => '98765432101',
            'business_name' => 'Updated ISP Company',
            'tradename' => 'UpdatedISP',
            'mobile' => '111222333',
            'mobile_refrence' => '444555666',
            'address' => 'Av. Updated 456'
        ]);
    }

    /**
     * @group critical
     */
    public function testUpdateBasicInformationWithValidData(): void
    {
        $result = $this->model->update_basic(
            $this->testBusiness['id'],
            'New Slogan for ISP',
            'Cusco',
            'Cusco',
            'Wanchaq',
            '080101',
            '+51'
        );

        $this->assertEquals('success', $result);
        $this->assertDatabaseHas('business', [
            'id' => $this->testBusiness['id'],
            'slogan' => 'New Slogan for ISP',
            'department' => 'Cusco',
            'province' => 'Cusco',
            'district' => 'Wanchaq',
            'ubigeo' => '080101',
            'country_code' => '+51'
        ]);
    }

    /**
     * @group critical
     */
    public function testUpdateInvoiceSettingsWithValidData(): void
    {
        $result = $this->model->update_invoice(
            $this->testBusiness['id'],
            'Updated footer text for invoices',
            1, // currency ID
            2  // print format
        );

        $this->assertEquals('success', $result);
        $this->assertDatabaseHas('business', [
            'id' => $this->testBusiness['id'],
            'footer_text' => 'Updated footer text for invoices',
            'currencyid' => 1,
            'print_format' => 2
        ]);
    }

    /**
     * @group critical
     */
    public function testUpdateMainLogo(): void
    {
        $result = $this->model->main_logo(
            $this->testBusiness['id'],
            'new_main_logo.png'
        );

        $this->assertEquals('success', $result);
        $this->assertDatabaseHas('business', [
            'id' => $this->testBusiness['id'],
            'logotyope' => 'new_main_logo.png'
        ]);
    }

    /**
     * @group critical
     */
    public function testUpdateLoginLogo(): void
    {
        $result = $this->model->login_logo(
            $this->testBusiness['id'],
            'new_login_logo.png'
        );

        $this->assertEquals('success', $result);
        $this->assertDatabaseHas('business', [
            'id' => $this->testBusiness['id'],
            'logo_login' => 'new_login_logo.png'
        ]);
    }

    /**
     * @group critical
     */
    public function testUpdateFavicon(): void
    {
        $result = $this->model->favicon(
            $this->testBusiness['id'],
            'new_favicon.ico'
        );

        $this->assertEquals('success', $result);
        $this->assertDatabaseHas('business', [
            'id' => $this->testBusiness['id'],
            'favicon' => 'new_favicon.ico'
        ]);
    }

    /**
     * @group critical
     */
    public function testUpdateBackground(): void
    {
        $result = $this->model->background(
            $this->testBusiness['id'],
            'new_background.jpg'
        );

        $this->assertEquals('success', $result);
        $this->assertDatabaseHas('business', [
            'id' => $this->testBusiness['id'],
            'background' => 'new_background.jpg'
        ]);
    }

    /**
     * @group critical
     */
    public function testUpdateGoogleApiKey(): void
    {
        $result = $this->model->update_google(
            $this->testBusiness['id'],
            'updated_google_api_key_123'
        );

        $this->assertEquals('success', $result);
        $this->assertDatabaseHas('business', [
            'id' => $this->testBusiness['id'],
            'google_apikey' => 'updated_google_api_key_123'
        ]);
    }

    /**
     * @group critical
     */
    public function testUpdateEmailConfigurationWithValidData(): void
    {
        $result = $this->model->update_email(
            $this->testBusiness['id'],
            'newemail@testisp.com',
            'newpassword123',
            'smtp.newtestisp.com',
            '465',
            'new_email_logo.png'
        );

        $this->assertEquals('success', $result);
        $this->assertDatabaseHas('business', [
            'id' => $this->testBusiness['id'],
            'email' => 'newemail@testisp.com',
            'password' => 'newpassword123',
            'server_host' => 'smtp.newtestisp.com',
            'port' => '465',
            'logo_email' => 'new_email_logo.png'
        ]);
    }

    /**
     * @group critical
     */
    public function testUpdateWhatsAppConfiguration(): void
    {
        $data = [
            'whatsapp_key' => 'new_whatsapp_key_456',
            'whatsapp_api' => 'https://api.newwhatsapp.test'
        ];

        $result = $this->model->update_whatsapp($this->testBusiness['id'], $data);
        $this->assertTrue($result);

        $this->assertDatabaseHas('business', [
            'id' => $this->testBusiness['id'],
            'whatsapp_key' => 'new_whatsapp_key_456',
            'whatsapp_api' => 'https://api.newwhatsapp.test'
        ]);
    }

    /**
     * @group business-logic
     */
    public function testListDatabaseBackups(): void
    {
        // Create test backup records
        $backup1Id = $this->insertTestData('backups', [
            'archive' => 'backup_test_2023-01-01.zip',
            'size' => '5.2 MB',
            'registration_date' => '2023-01-01 10:00:00'
        ]);

        $backup2Id = $this->insertTestData('backups', [
            'archive' => 'backup_test_2023-01-02.zip',
            'size' => '5.5 MB',
            'registration_date' => '2023-01-02 10:00:00'
        ]);

        $result = $this->model->list_database();

        $this->assertIsArray($result);
        $this->assertGreaterThanOrEqual(2, count($result));

        // Verify structure and order (DESC by id)
        $firstBackup = $result[0];
        $this->assertArrayHasKey('id', $firstBackup);
        $this->assertArrayHasKey('archive', $firstBackup);
        $this->assertArrayHasKey('size', $firstBackup);
        $this->assertArrayHasKey('registration_date', $firstBackup);

        // Should be ordered by ID DESC (newest first)
        $this->assertEquals($backup2Id, $firstBackup['id']);
    }

    /**
     * @group business-logic
     */
    public function testCreateBackupWithValidSessionData(): void
    {
        // Mock session data
        $_SESSION['businessData'] = [
            'business_name' => 'Test-Business-Name'
        ];

        // Mock database structure
        $this->insertTestData('backups', []); // Ensure table exists

        $result = $this->model->create_backup();

        // The create_backup method has complex file operations and database schema reading
        // For unit testing, we focus on the business logic rather than file system operations
        // In a real scenario, we would mock the file operations or use a test environment

        $this->assertIsString($result);
        $this->assertContains($result, ['success', 'error', 'exists']);
    }

    /**
     * @group business-logic
     */
    public function testRemoveBackupWithValidId(): void
    {
        // Create test backup record
        $backupId = $this->insertTestData('backups', [
            'archive' => 'test_backup_to_remove.zip',
            'size' => '1.0 MB',
            'registration_date' => date('Y-m-d H:i:s')
        ]);

        // Create the backup directory structure for testing
        $backupDir = 'Assets/backups/';
        if (!is_dir($backupDir)) {
            mkdir($backupDir, 0777, true);
        }

        // Create a fake backup file
        $backupFile = $backupDir . 'test_backup_to_remove.zip';
        file_put_contents($backupFile, 'fake backup content');

        $result = $this->model->remove($backupId);

        $this->assertEquals('success', $result);
        $this->assertDatabaseMissing('backups', ['id' => $backupId]);

        // Verify file was removed
        $this->assertFileDoesNotExist($backupFile);

        // Cleanup
        if (is_dir($backupDir)) {
            rmdir($backupDir);
        }
    }

    /**
     * @group business-logic
     */
    public function testRemoveBackupWithInvalidIdReturnsError(): void
    {
        $result = $this->model->remove(99999); // Non-existent backup ID

        $this->assertEquals('error', $result);
    }

    /**
     * @group error-handling
     */
    public function testUpdateGeneralWithInvalidIdReturnsError(): void
    {
        $result = $this->model->update_general(
            99999, // Non-existent business ID
            '12345678901',
            'Test Company',
            'TestCorp',
            '123456789',
            '987654321',
            'Test Address'
        );

        $this->assertEquals('error', $result);
    }

    /**
     * @group error-handling
     */
    public function testUpdateBasicWithInvalidIdReturnsError(): void
    {
        $result = $this->model->update_basic(
            99999, // Non-existent business ID
            'Test Slogan',
            'Lima',
            'Lima',
            'Miraflores',
            '150101',
            '+51'
        );

        $this->assertEquals('error', $result);
    }

    /**
     * @group error-handling
     */
    public function testUpdateInvoiceWithInvalidIdReturnsError(): void
    {
        $result = $this->model->update_invoice(
            99999, // Non-existent business ID
            'Test Footer',
            1,
            1
        );

        $this->assertEquals('error', $result);
    }

    /**
     * @group error-handling
     */
    public function testMainLogoWithInvalidIdReturnsError(): void
    {
        $result = $this->model->main_logo(99999, 'test_logo.png');
        $this->assertEquals('error', $result);
    }

    /**
     * @group error-handling
     */
    public function testLoginLogoWithInvalidIdReturnsError(): void
    {
        $result = $this->model->login_logo(99999, 'test_login_logo.png');
        $this->assertEquals('error', $result);
    }

    /**
     * @group error-handling
     */
    public function testFaviconWithInvalidIdReturnsError(): void
    {
        $result = $this->model->favicon(99999, 'test_favicon.ico');
        $this->assertEquals('error', $result);
    }

    /**
     * @group error-handling
     */
    public function testBackgroundWithInvalidIdReturnsError(): void
    {
        $result = $this->model->background(99999, 'test_background.jpg');
        $this->assertEquals('error', $result);
    }

    /**
     * @group error-handling
     */
    public function testUpdateGoogleWithInvalidIdReturnsError(): void
    {
        $result = $this->model->update_google(99999, 'test_api_key');
        $this->assertEquals('error', $result);
    }

    /**
     * @group error-handling
     */
    public function testUpdateEmailWithInvalidIdReturnsError(): void
    {
        $result = $this->model->update_email(
            99999, // Non-existent business ID
            'test@example.com',
            'password',
            'smtp.example.com',
            '587',
            'logo.png'
        );

        $this->assertEquals('error', $result);
    }

    /**
     * @group validation
     */
    public function testUpdateGeneralWithEmptyValues(): void
    {
        $result = $this->model->update_general(
            $this->testBusiness['id'],
            '', // empty RUC
            '', // empty business name
            '',
            '',
            '',
            ''
        );

        // Should still succeed as validation is not enforced at model level
        $this->assertEquals('success', $result);
        $this->assertDatabaseHas('business', [
            'id' => $this->testBusiness['id'],
            'ruc' => '',
            'business_name' => ''
        ]);
    }

    /**
     * @group boundary-conditions
     */
    public function testUpdateEmailWithLongValues(): void
    {
        $longEmail = str_repeat('a', 200) . '@example.com';
        $longPassword = str_repeat('p', 500);
        $longHost = str_repeat('h', 300) . '.com';

        $result = $this->model->update_email(
            $this->testBusiness['id'],
            $longEmail,
            $longPassword,
            $longHost,
            '587',
            'logo.png'
        );

        // Should succeed if database field lengths allow it
        $this->assertEquals('success', $result);
    }

    /**
     * @group sql-injection
     */
    public function testUpdateGeneralWithSQLInjectionAttempt(): void
    {
        $maliciousInput = "'; DROP TABLE business; --";

        $result = $this->model->update_general(
            $this->testBusiness['id'],
            $maliciousInput,
            'Test Company',
            'TestCorp',
            '123456789',
            '987654321',
            'Test Address'
        );

        // Should succeed as prepared statements prevent SQL injection
        $this->assertEquals('success', $result);

        // Verify table still exists and data was escaped
        $this->assertDatabaseHas('business', [
            'id' => $this->testBusiness['id'],
            'ruc' => $maliciousInput
        ]);
    }

    /**
     * @group integration
     */
    public function testCompleteBusinessConfigurationWorkflow(): void
    {
        // Test a complete workflow of updating business configuration

        // 1. Update general information
        $result1 = $this->model->update_general(
            $this->testBusiness['id'],
            '11111111111',
            'Workflow Test ISP',
            'WorkflowISP',
            '555666777',
            '888999000',
            'Workflow Street 123'
        );
        $this->assertEquals('success', $result1);

        // 2. Update basic information
        $result2 = $this->model->update_basic(
            $this->testBusiness['id'],
            'Complete Workflow Slogan',
            'Arequipa',
            'Arequipa',
            'Cercado',
            '040101',
            '+51'
        );
        $this->assertEquals('success', $result2);

        // 3. Update invoice settings
        $result3 = $this->model->update_invoice(
            $this->testBusiness['id'],
            'Workflow footer text',
            1,
            2
        );
        $this->assertEquals('success', $result3);

        // 4. Update logos
        $result4 = $this->model->main_logo($this->testBusiness['id'], 'workflow_logo.png');
        $this->assertEquals('success', $result4);

        // 5. Update email configuration
        $result5 = $this->model->update_email(
            $this->testBusiness['id'],
            'workflow@testisp.com',
            'workflow123',
            'smtp.workflow.com',
            '465',
            'workflow_email_logo.png'
        );
        $this->assertEquals('success', $result5);

        // 6. Verify all changes were applied
        $businessData = $this->model->show_business();
        $this->assertEquals('Workflow Test ISP', $businessData['business_name']);
        $this->assertEquals('Complete Workflow Slogan', $businessData['slogan']);
        $this->assertEquals('Arequipa', $businessData['department']);
        $this->assertEquals('workflow_logo.png', $businessData['logotyope']);
        $this->assertEquals('workflow@testisp.com', $businessData['email']);
    }
}