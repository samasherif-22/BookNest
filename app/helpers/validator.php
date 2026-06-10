<?php

class Validator {
    // Array to store any validation error messages
    private array $errors = [];

    // 1. Email Validation: Checks if the input is a valid email address format
    public function email($field, $value) {
        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $this->errors[$field] = "Invalid email format.";
        }
    }

    // 2. Required Validation: Ensures the user didn't leave the input blank
    public function required($field, $value) {
        if (empty(trim((string)$value))) {
            $this->errors[$field] = "This field is required.";
        }
    }

    // 3. Numeric Validation: Ensures the input (like price or quantity) is a number
    public function numeric($field, $value) {
        if (!is_numeric($value)) {
            $this->errors[$field] = "Must be a number.";
        }
    }

    // 4. Password Length Validation: Ensures the password is 8 characters or more
    public function passwordLength($field, $value) {
        if (strlen($value) < 8) {
            $this->errors[$field] = "Password must be at least 8 characters.";
        }
    }

    // Returns True if there are NO errors (meaning validation passed)
    public function passes() {
        return empty($this->errors);
    }

    // Returns the array of errors to be displayed to the user
    public function getErrors() {
        return $this->errors;
    }
}