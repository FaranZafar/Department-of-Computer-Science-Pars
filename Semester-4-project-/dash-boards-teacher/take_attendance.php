<?php
session_start();
include_once("../dbconnection.php");

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_SESSION['user_id'])) {
    $course_id = $_POST['course_id'];
    $section_id = $_POST['section_id'];
    $date = $_POST['attendance_date'];
    $staff_id = $_SESSION['user_id'];
    $now = date("Y-m-d H:i:s");

    foreach ($_POST['status'] as $student_id => $status) {
        // Check if a record already exists for this specific student on this specific date/course
        $check = mysqli_query($con, "SELECT attendance_id FROM student_attendance 
                                    WHERE student_id = '$student_id' 
                                    AND course_id = '$course_id' 
                                    AND date = '$date'");

        if (mysqli_num_rows($check) > 0) {
            // UPDATE existing record
            $sql = "UPDATE student_attendance SET status = '$status', time_recorded = '$now', StaffID = '$staff_id' 
                    WHERE student_id = '$student_id' AND course_id = '$course_id' AND date = '$date'";
        } else {
            // INSERT new record
            $sql = "INSERT INTO student_attendance (course_id, section_id, student_id, date, status, StaffID, time_recorded) 
                    VALUES ('$course_id', '$section_id', '$student_id', '$date', '$status', '$staff_id', '$now')";
        }
        mysqli_query($con, $sql);
    }

    header("Location: teacher_dashboard.php?date=$date&msg=Attendance updated successfully");
    exit();
}