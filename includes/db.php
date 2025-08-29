
<?php
// Database configuration
$servername = getenv("DB_HOST");
$username   = getenv("DB_USER");
$password   = getenv("DB_PASS");
$dbname     = getenv("DB_NAME");
$port       = getenv("DB_PORT");

// Create connection
$conn = mysqli_connect($servername, $username, $password, $dbname, $port);


// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Set charset to utf8
mysqli_set_charset($conn, "utf8");
?>

