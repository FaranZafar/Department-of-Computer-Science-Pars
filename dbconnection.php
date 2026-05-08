<?php
// session_start(); // Start the session at the very top

$host = "localhost";
$user = "root";
$pass = "";
$dbname = "umsproject";

$con = mysqli_connect($host, $user, $pass, $dbname);

// Only show alert if it hasn't been shown this session

if ($con && !isset($_SESSION['connection_verified'])) {
    // echo "<script>Connection Build Successfully;</script>";
    $_SESSION['connection_verified'] = true; // Set the flag
} elseif (!$con) {
    echo "<script>alert('Error: Connection not Built!!!');</scrip>";
}
?>