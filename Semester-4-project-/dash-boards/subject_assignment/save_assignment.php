<?php
session_start();
include_once("../../dbconnection.php");

// Security check
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'Coordinator') {
    die("Unauthorized access.");
}

if (isset($_POST['save_assignment'])) {
    // Collect and sanitize input
    $course_id   = mysqli_real_escape_string($con, $_POST['course_id']);
    $staff_id    = mysqli_real_escape_string($con, $_POST['staff_id']);
    $section_name = mysqli_real_escape_string($con, $_POST['section_name']);
    $semester_id = mysqli_real_escape_string($con, $_POST['semester_id']);

    // Check if this teacher is already assigned to this exact section/course
    $check = mysqli_query($con, "SELECT * FROM sections 
                                 WHERE course_id = '$course_id' 
                                 AND section_name = '$section_name' 
                                 AND semester_id = '$semester_id'");

    if (mysqli_num_rows($check) > 0) {
        header("Location: ../subject_assigment/coordinator.php?error=already_assigned");
        exit();
    }

    // Insert into sections table
    $query = "INSERT INTO sections (course_id, staff_id, section_name, semester_id) 
              VALUES ('$course_id', '$staff_id', '$section_name', '$semester_id')";

    if (mysqli_query($con, $query)) {
        header("Location: ../subject_assigment/coordinator.php?success=assigned");
    } else {
        header("Location: ../subject_assigment/coordinator.php?error=database_fail");
    }
}
?>