<!-- 
<?php
//Database configuration
// $servername = "localhost";
// $username = "root";
// $password = "";
// $dbname = "quiz_appdb";

// // Create connection
// $conn = mysqli_connect($servername, $username, $password, $dbname);

// // Check connection
// if (!$conn) {
//     die("Connection failed: " . mysqli_connect_error());
// }

// // Set charset to utf8
// mysqli_set_charset($conn, "utf8");


?> -->

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
    
    // echo "✅ Connected to Render PostgreSQL successfully!"; // Debug only
} catch (PDOException $e) {
    die("❌ Database connection failed: " . $e->getMessage());
}
?>
