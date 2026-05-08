<?php
include_once("../dbconnection.php");

// Turn off error display for clean JSON output, but log them
error_reporting(0); 
header('Content-Type: application/json');

// Get ID from either POST (AJAX) or GET (Manual testing)
$degree_id = $_POST['degree_id'] ?? $_GET['degree_id'] ?? null;
$semester_id = $_POST['semester_id'] ?? $_GET['semester_id'] ?? null;

if ($degree_id) {
    $stmt = $con->prepare("SELECT semester_id, semester_name FROM semester WHERE degree_id = ?");
    $stmt->bind_param("i", $degree_id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    echo json_encode($result);
    exit;
}

if ($semester_id) {
    $stmt = $con->prepare("SELECT section_id, section_name FROM sections WHERE semester_id = ?");
    $stmt->bind_param("i", $semester_id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    echo json_encode($result);
    exit;
}

// If nothing matches, return empty array
echo json_encode([]);
?>