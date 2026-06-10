<?php

//singleton

class Database
{
    // Holds the single instance of this class
    private static $instance = null;

    // The PDO connection object
    private $pdo;

    // Database connection settings (XAMPP defaults)
    private $host     = "localhost";
    private $username = "root";
    private $password = "";          // XAMPP default: empty password
    private $dbname   = "bookstore";

    private function __construct() // Private constructor to prevent direct object creation
    {
        try {
            $dsn = "mysql:host={$this->host};dbname={$this->dbname};charset=utf8";
            $this->pdo = new PDO($dsn, $this->username, $this->password);  // Create PDO connection
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            // Stop everything if we cannot connect to the database
            die("<h2>Database Connection Failed:</h2><p>" . $e->getMessage() . "</p>");
        }
    }


    public static function getInstance(): Database
    {
        if (self::$instance === null) { //if no instance created before ..create one
            self::$instance = new Database();  // Creates it only once, then reuses the same instance every time
        }
        return self::$instance;
    }


    public function getConnection(): PDO // must return a pdo object
    {
        return $this->pdo;
    }

    // Prevent cloning of the singleton instance
    private function __clone() {}
}
