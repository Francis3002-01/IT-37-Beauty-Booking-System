<?php
/**
 * ==============================================================================
 * DATABASE CONNECTION CONFIGURATION 
 * ==============================================================================
 */
$host     = 'localhost';
$dbname   = ''; // CHANGE THIS to the datbase name
$username = 'root';  // Default XAMPP username
$password = '';      // Default XAMPP password (leave empty)

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    
    // Enable error reporting and set default fetch mode to associative arrays
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC); 
} 

catch (PDOException $e) {
    die("Database Connection Error: " . $e->getMessage());
}