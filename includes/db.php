<?php
// Database configuration (from Render connection string)
$host = "dpg-d2s6abur433s73fb6k40-a";
$port = "5432";
$dbname = "quizdb_fbxq";
$username = "admin";
$password = "o4s3PyNSGwlDecn1kFyOtuguCVjRSKK3";

// Build DSN
$dsn = "pgsql:host=$host;port=$port;dbname=$dbname;";

try {
    // Create PDO connection
    $conn = new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, // throw exceptions
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC // return associative arrays
    ]);

    // Optional: set charset to UTF8
    $conn->exec("SET NAMES 'UTF8'");
    
    // ❌ Removed debug echo — never output inside db.php
} catch (PDOException $e) {
    // Use exception instead of echo to avoid output before headers
    die("❌ Database connection failed: " . $e->getMessage());

