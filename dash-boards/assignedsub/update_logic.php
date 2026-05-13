<?php
session_start();
include_once("../../dbconnection.php");

if (isset($_POST['update_assignment'])) {
    // 1. Collect and Sanitize Inputs
    $id = intval($_POST['assigned_id']);
    $staff_id = intval($_POST['staff_id']);
    $degree_id = intval($_POST['degree_id']);
    $semester_id = intval($_POST['semester_id']);
    $course_id = intval($_POST['course_id']);
    $section_id = intval($_POST['section_id']);
    $room_id = !empty($_POST['room_id']) ? intval($_POST['room_id']) : "NULL";
    $day = $con->real_escape_string($_POST['day']);
    $start_time = $_POST['start_time'];
    $end_time = $_POST['end_time'];

    // 2. Prepare the Update Query
    // NOTE: Ensure your database column names are 'time_start' and 'time_end'
    $sql = "UPDATE assigned_teacher SET 
            StaffID = '$staff_id', 
            degree_id = '$degree_id', 
            semester_id = '$semester_id', 
            course_id = '$course_id', 
            section_id = '$section_id', 
            room_id = $room_id, 
            day = '$day', 
            time_start = '$start_time', 
            time_end = '$end_time' 
            WHERE subjectassigned_id = $id";

    // 3. Execute and Redirect
    if ($con->query($sql)) {
        $_SESSION['success_msg'] = "Assignment updated successfully!";
        header("Location: assigned_teacher_room.php");
    } else {
        echo "Update failed: " . $con->error;
    }
    exit();
}
?>