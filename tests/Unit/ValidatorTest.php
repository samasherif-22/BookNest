<?php
use PHPUnit\Framework\TestCase;

// Include the Validator class to test it
require_once "./app/helpers/validator.php";

class ValidatorTest extends TestCase {
    
    private $validator;

    // This runs before each test to create a fresh Validator object
    protected function setUp(): void {
        $this->validator = new Validator();
    }

    // TEST 1: Check if the system correctly validates emails
    public function test_email_validation() {
        // Expected to PASS: Correct email format
        $this->validator->email('email', 'meryam@test.com');
        $this->assertTrue($this->validator->passes());

        // Expected to FAIL: Missing the '@' symbol
        $v2 = new Validator();
        $v2->email('email', 'sama.mail.com'); 
        $this->assertFalse($v2->passes());
    }

    // TEST 2: Check if the system prevents empty inputs
    public function test_required_field_validation() {
        // Expected to PASS: A real string is provided
        $this->validator->required('title', 'Uncharted');
        $this->assertTrue($this->validator->passes());

      // Expected to FAIL: An empty string is provided
        $v2 = new Validator();
        $v2->required('title', ''); 
        $this->assertFalse($v2->passes());
    }

    // TEST 3: Check if the system enforces numbers for specific fields
    public function test_numeric_input_validation() {
        // Expected to PASS: A valid decimal number is provided
        $this->validator->numeric('price', '150.50');
        $this->assertTrue($this->validator->passes());

        // Expected to FAIL: Number mixed with text/currency
        $v2 = new Validator();
        $v2->numeric('price', '150 EGP');
        $this->assertFalse($v2->passes());
    }

    // TEST 4: Check if the system enforces strong password lengths
    public function test_password_length_validation() {
        // Expected to PASS: Strong password (8+ characters)
        $this->validator->passwordLength('user_password', 'P@ssw0rd2026');
        $this->assertTrue($this->validator->passes());

        // Expected to FAIL: Borderline short password (exactly 7 characters)
        $v2 = new Validator();
        $v2->passwordLength('user_password', '1234567');
        $this->assertFalse($v2->passes());
    }
      //run with .\vendor\bin\phpunit
}