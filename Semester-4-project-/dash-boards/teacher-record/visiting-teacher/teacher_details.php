<?php
include_once("../../../dbconnection.php");

$staffID = $_GET['id'] ?? '';
if (empty($staffID)) {
    header("Location: visitingteacher-record.php");
    exit();
}

// 1. Fetch Teacher Personal Info
$staff_query = "SELECT * FROM staff WHERE StaffID = '$staffID'";
$staff_res = $con->query($staff_query);
$staff = $staff_res->fetch_assoc();

// 2. Fetch Assigned Courses & Sections
$course_query = "
    SELECT c.course_title, c.course_code, s.section_name, sem.semester_name
    FROM assigned_teacher at
    JOIN courses c ON at.course_id = c.course_id
    JOIN sections s ON at.section_id = s.section_id
    JOIN semester sem ON s.semester_id = sem.semester_id
    WHERE at.StaffID = '$staffID'";
$courses = $con->query($course_query);

// 3. Check if today's attendance is submitted
$today = date('Y-m-d');
$att_check = $con->query("SELECT value FROM staff_attendance WHERE StaffID = '$staffID' AND date = '$today'");
$attendance_status = ($att_check->num_rows > 0) ? $att_check->fetch_assoc()['value'] : "Not Marked";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Teacher Profile | Details</title>
    <link rel="icon" type="image/png" href="../../../images/pars.png">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        body { background-color: #f4f7f6; font-family: 'Inter', sans-serif; }
        .profile-header { background: #4e73df; color: white; padding: 40px 0; border-radius: 0 0 20px 20px; }
        .card { border: none; border-radius: 15px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
        .status-badge { padding: 5px 15px; border-radius: 20px; font-weight: bold; }
    </style>
</head>
<body>

<div class="profile-header text-center">
    <div class="container">
        <h1 class="display-4"><?php echo $staff['FirstName'] . " " . $staff['LastName']; ?></h1>
        <p class="lead"><?php echo $staff['Department']; ?> Department</p>
        <a href="visitingteacher-record.php" class="btn btn-light btn-sm mt-2">Back to List</a>
    </div>
</div>

<div class="container mt-n4">
    <div class="row">
        <!-- Sidebar: Personal Info -->
        <div class="col-lg-4">
            <div class="card p-4 mb-4">
                <h5 class="font-weight-bold mb-3 border-bottom pb-2">Information</h5>
                <p><strong>ID:</strong> <?php echo $staff['StaffID']; ?></p>
                <p><strong>Qualification:</strong> <?php echo $staff['Qualification']; ?></p>
                <p><strong>Joining Date:</strong> <?php echo date('M d, Y', strtotime($staff['joiningDate'])); ?></p>
                <hr>
                <p class="mb-1"><strong>Today's Attendance:</strong></p>
                <?php if($attendance_status == "Present"): ?>
                    <span class="badge badge-success status-badge">PRESENT</span>
                <?php elseif($attendance_status == "Absent"): ?>
                    <span class="badge badge-danger status-badge">ABSENT</span>
                <?php else: ?>
                    <span class="badge badge-secondary status-badge">NOT MARKED YET</span>
                <?php endif; ?>
            </div>
        </div>

        <!-- Main Content: Courses -->
        <div class="col-lg-8">
            <div class="card p-4">
                <h5 class="font-weight-bold mb-4"><i class="fas fa-book-open text-primary mr-2"></i>Assigned Workload</h5>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="bg-light">
                            <tr>
                                <th>Course Title</th>
                                <th>Code</th>
                                <th>Semester</th>
                                <th>Section</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if($courses->num_rows > 0): ?>
                                <?php while($c = $courses->fetch_assoc()): ?>
                                <tr>
                                    <td><strong><?php echo $c['course_title']; ?></strong></td>
                                    <td><span class="badge badge-info"><?php echo $c['course_code']; ?></span></td>
                                    <td><?php echo $c['semester_name']; ?></td>
                                    <td><?php echo $c['section_name']; ?></td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr><td colspan="4" class="text-center text-muted">No courses assigned to this teacher.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>