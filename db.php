<?php
// Database connection
$servername = "localhost";
$username = "root"; // Default WAMP username
$password = "";     // Default WAMP password (empty)
$dbname = "tours";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "Connection successful!"; // Add this line for debugging
} catch(PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
    die();
}
?>