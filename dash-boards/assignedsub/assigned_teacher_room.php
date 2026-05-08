<?php
session_start();
include_once("../../dbconnection.php");

if (!isset($_SESSION['user_id'])) { header("Location: ../login.php"); exit(); }

$message = "";
$edit_data = null;

// --- 1. HANDLE DELETE ---
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $con->query("DELETE FROM assigned_teacher WHERE subjectassigned_id = $id");
    header("Location: assigned_teacher_room.php?msg=deleted");
    exit();
}

// --- 2. HANDLE FORM SUBMISSION (INSERT or UPDATE) ---
if (isset($_POST['save_assignment'])) {
    $staff_id   = $_POST['staff_id'];
    $course_id  = $_POST['course_id'];
    $section_id = $_POST['section_id'];
    $room_id    = $_POST['room_id'];
    $day        = $_POST['day'];
    $start_time = $_POST['start_time'];
    $end_time   = $_POST['end_time'];
    $edit_id    = $_POST['edit_id']; // Hidden field

    if (!empty($edit_id)) {
        // UPDATE EXISTING
        $stmt = $con->prepare("UPDATE assigned_teacher SET StaffID=?, day=?, time_start=?, time_end=?, room_id=?, course_id=?, section_id=? WHERE subjectassigned_id=?");
        $stmt->bind_param("isssiiii", $staff_id, $day, $start_time, $end_time, $room_id, $course_id, $section_id, $edit_id);
        $message = "Updated";
    } else {
        // INSERT NEW
        $stmt = $con->prepare("INSERT INTO assigned_teacher (StaffID, day, time_start, time_end, room_id, course_id, section_id) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("isssiii", $staff_id, $day, $start_time, $end_time, $room_id, $course_id, $section_id);
        $message = "Assigned";
    }
    
    if ($stmt->execute()) {
        header("Location: assigned_teacher_room.php?msg=$message");
        exit();
    }
}

// --- 3. FETCH DATA FOR EDITING ---
if (isset($_GET['edit'])) {
    $id = $_GET['edit'];
    $edit_res = $con->query("SELECT * FROM assigned_teacher WHERE subjectassigned_id = $id");
    $edit_data = $edit_res->fetch_assoc();
}

// --- 4. DATA FETCHING FOR DROPDOWNS & TABLE ---
$teachers = $con->query("SELECT StaffID, FirstName, LastName FROM staff WHERE UserRole IN ('Teacher', 'Coordinator')")->fetch_all(MYSQLI_ASSOC);
$courses  = $con->query("SELECT * FROM courses")->fetch_all(MYSQLI_ASSOC);
$sections = $con->query("SELECT * FROM sections")->fetch_all(MYSQLI_ASSOC);
$rooms    = $con->query("SELECT * FROM room")->fetch_all(MYSQLI_ASSOC);

$assignments = $con->query("SELECT at.*, s.FirstName, s.LastName, c.course_title, sec.section_name, r.room_name 
                            FROM assigned_teacher at 
                            JOIN staff s ON at.StaffID = s.StaffID 
                            JOIN courses c ON at.course_id = c.course_id 
                            JOIN sections sec ON at.section_id = sec.section_id 
                            LEFT JOIN room r ON at.room_id = r.room_id 
                            ORDER BY at.subjectassigned_id DESC")->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Manage Assignments</title>
    <link rel="icon" type="image/png" href="../../images/pars.png">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body class="bg-light">
<div class="container mt-5 mb-5">
    
    <!-- ASSIGNMENT FORM -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-primary text-white d-flex justify-content-between">
            <h5 class="mb-0"><?= $edit_data ? 'Edit Assignment' : 'New Assignment' ?></h5>
            <?php if($edit_data): ?><a href="assigned_teacher_room.php" class="btn btn-sm btn-light">Cancel Edit</a><?php endif; ?>
        </div>
        <div class="card-body">
            <form method="POST">
                <input type="hidden" name="edit_id" value="<?= $edit_data['subjectassigned_id'] ?? '' ?>">
                
                <div class="row">
                    <div class="col-md-4 form-group">
                        <label>Teacher</label>
                        <select name="staff_id" class="form-control" required>
                            <?php foreach($teachers as $t): ?>
                                <option value="<?= $t['StaffID'] ?>" <?= ($edit_data && $edit_data['StaffID'] == $t['StaffID']) ? 'selected' : '' ?>>
                                    <?= $t['FirstName'] ?> <?= $t['LastName'] ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4 form-group">
                        <label>Course</label>
                        <select name="course_id" class="form-control" required>
                            <?php foreach($courses as $c): ?>
                                <option value="<?= $c['course_id'] ?>" <?= ($edit_data && $edit_data['course_id'] == $c['course_id']) ? 'selected' : '' ?>>
                                    <?= $c['course_title'] ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4 form-group">
                        <label>Section</label>
                        <select name="section_id" class="form-control" required>
                            <?php foreach($sections as $sec): ?>
                                <option value="<?= $sec['section_id'] ?>" <?= ($edit_data && $edit_data['section_id'] == $sec['section_id']) ? 'selected' : '' ?>>
                                    <?= $sec['section_name'] ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-3 form-group">
                        <label>Day</label>
                        <select name="day" class="form-control" required>
                            <?php 
                            $days = ['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'];
                            foreach($days as $d) {
                                $sel = ($edit_data && $edit_data['day'] == $d) ? 'selected' : '';
                                echo "<option value='$d' $sel>$d</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="col-md-3 form-group">
                        <label>Start Time</label>
                        <input type="time" name="start_time" class="form-control" value="<?= $edit_data['time_start'] ?? '' ?>" required>
                    </div>
                    <div class="col-md-3 form-group">
                        <label>End Time</label>
                        <input type="time" name="end_time" class="form-control" value="<?= $edit_data['time_end'] ?? '' ?>" required>
                    </div>
                    <div class="col-md-3 form-group">
                        <label>Room</label>
                        <select name="room_id" class="form-control">
                            <option value="">Select Room</option>
                            <?php foreach($rooms as $r): ?>
                                <option value="<?= $r['room_id'] ?>" <?= ($edit_data && $edit_data['room_id'] == $r['room_id']) ? 'selected' : '' ?>>
                                    <?= $r['room_name'] ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <button type="submit" name="save_assignment" class="btn btn-success btn-block">
                    <?= $edit_data ? 'Update Schedule' : 'Create Assignment' ?>
                </button>
            </form>
        </div>
    </div>

    <!-- ASSIGNMENT TABLE -->
    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <table class="table table-hover mb-0">
                <thead class="bg-light">
                    <tr>
                        <th>Instructor</th>
                        <th>Course & Section</th>
                        <th>Schedule</th>
                        <th>Room</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($assignments as $a): ?>
                    <tr>
                        <td><strong><?= $a['FirstName'] ?> <?= $a['LastName'] ?></strong></td>
                        <td><?= $a['course_title'] ?> <span class="badge badge-secondary"><?= $a['section_name'] ?></span></td>
                        <td><small><?= $a['day'] ?> | <?= date("g:i A", strtotime($a['time_start'])) ?> - <?= date("g:i A", strtotime($a['time_end'])) ?></small></td>
                        <td><span class="text-primary"><?= $a['room_name'] ?? 'TBA' ?></span></td>
                        <td class="text-center">
                            <a href="?edit=<?= $a['subjectassigned_id'] ?>" class="btn btn-sm btn-info">Edit</a>
                            <a href="?delete=<?= $a['subjectassigned_id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this assignment?')">Delete</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/jquery@3.5.1/dist/jquery.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>