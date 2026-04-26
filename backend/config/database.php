<?php
$servername = "localhost";
$username = "root";
$password = ""; // default XAMPP password
$dbname = "faculty_evaluation";  // make sure this is correct!

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>