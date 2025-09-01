
<?php
// Database configuration
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


<?php
$servername = getenv("DB_HOST") ?: "127.0.0.1";  
$username   = getenv("DB_USER") ?: "root";  
$password   = getenv("DB_PASSWORD") ?: "";  
$dbname     = getenv("DB_NAME") ?: "quiz_appdb";  
$port       = getenv("DB_PORT") ?: 3306;  

$conn = new mysqli($servername, $username, $password, $dbname, $port);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
