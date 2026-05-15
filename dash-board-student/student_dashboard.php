<?php
session_start();
include_once("../dbconnection.php");

// 1. SECURITY & SESSION CHECK
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== "Student") {
    header("Location: ../login.php");
    exit();
}

$student_id = $_SESSION['user_id'];

/** 
 * 2. FETCH STUDENT PROFILE & DEGREE
 */
$student_info_query = "
    SELECT st.*, s.semester_name, sec.section_name, d.degree_name 
    FROM students st 
    LEFT JOIN semester s ON st.semester_id = s.semester_id 
    LEFT JOIN sections sec ON st.section_id = sec.section_id 
    LEFT JOIN degree d ON sec.degree_id = d.degree_id
    WHERE st.student_id = '$student_id'";

$student_result = mysqli_query($con, $student_info_query);
$student_data = mysqli_fetch_assoc($student_result);

$display_name = $student_data['name'] ?? ($student_data['full_name'] ?? $_SESSION['user_name']);
$ag_no = $student_data['ag_no'] ?? 'N/A';
$semester = $student_data['semester_name'] ?? 'Not Assigned';
$section_name = $student_data['section_name'] ?? 'Not Assigned';
$degree_name = $student_data['degree_name'] ?? 'Degree Not Set';
$section_id = $student_data['section_id'] ?? 0;

/** 
 * 3. FETCH SCHEDULE
 */
$course_query = "
    SELECT 
        c.course_title, c.course_code, 
        at.day, at.time_start, at.time_end, at.room_id,
        stf.FirstName, stf.LastName
    FROM assigned_teacher at
    JOIN courses c ON at.course_id = c.course_id
    LEFT JOIN Staff stf ON at.StaffID = stf.StaffID
    WHERE at.section_id = '$section_id'";

$courses = mysqli_query($con, $course_query);

/** 
 * 4. ATTENDANCE SUMMARY QUERY
 * This defines the variable that was causing your error.
 */
$attendance_query = "
    SELECT 
        c.course_title, c.course_code,
        COUNT(a.attendance_id) as total,
        SUM(CASE WHEN a.status = 'Present' THEN 1 ELSE 0 END) as present
    FROM student_attendance a
    JOIN courses c ON a.course_id = c.course_id
    WHERE a.student_id = '$student_id'
    GROUP BY c.course_code, c.course_title";

$attendance_records = mysqli_query($con, $attendance_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard | Pars Campus</title>
    <link rel="icon" type="image/png" href="../images/pars.png">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        body { background-color: #f0f2f5; font-family: 'Segoe UI', sans-serif; }
        .navbar { background: #152259; border-bottom: 3px solid #ffc107; }
        .profile-card { background: linear-gradient(135deg, #152259 0%, #2a3d8f 100%); color: white; border: none; border-radius: 15px; }
        .section-badge { background: rgba(255, 255, 255, 0.2); border: 1px solid rgba(255, 255, 255, 0.4); padding: 5px 15px; border-radius: 20px; font-size: 0.9rem; }
        .table-card { border-radius: 15px; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.05); border: none; }
        .badge-soft-primary { background-color: #e7f0ff; color: #0056b3; }
        .badge-code { background-color: #fff3cd; color: #856404; border: 1px solid #ffeeba; font-size: 0.75rem; }
        .progress { border-radius: 10px; height: 7px; }
        .info-label { color: #adb5bd; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 1px; font-weight: bold; }
        .info-value { color: #152259; font-weight: 600; }
    </style>
</head>
<body>

<nav class="navbar navbar-dark py-3">
    <div class="container">
        <a class="navbar-brand font-weight-bold" href="#">Department Of Computer Science Pars</a>
        <a href="../logout.php" class="btn btn-outline-warning btn-sm px-3">Logout</a>
    </div>
</nav>

<div class="container mt-4">
    <div class="card profile-card p-4 mb-4 shadow">
        <div class="row align-items-center">
            <div class="col-md-7">
                <h2 class="mb-0 font-weight-bold text-capitalize">Welcome, <?= htmlspecialchars($display_name) ?></h2>
                <p class="text-warning font-weight-bold mb-3"><?= htmlspecialchars($degree_name) ?></p>
                <div class="d-flex flex-wrap align-items-center">
                    <span class="mr-3 mb-2"><strong>Ag No:</strong> <?= htmlspecialchars($ag_no) ?></span>
                    <span class="mr-3 mb-2"><strong>Semester:</strong> <?= htmlspecialchars($semester) ?></span>
                    <span class="section-badge mb-2"><i class="fas fa-users mr-1"></i> Section: <?= htmlspecialchars($section_name) ?></span>
                </div>
            </div>
            <div class="col-md-5 text-md-right">
                <div class="h5 mb-1"><i class="far fa-calendar-alt mr-2"></i>Session 2024-2028</div>
                <div class="small text-warning">University of Agriculture, Faisalabad</div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- LEFT COLUMN: SCHEDULE -->
        <div class="col-lg-8">
            <div class="card table-card bg-white mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 font-weight-bold"><i class="fas fa-list-alt text-primary mr-2"></i>My Class Schedule</h5>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="bg-light small text-uppercase">
                            <tr>
                                <th>Course & Code</th>
                                <th>Time</th>
                                <th>Room</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($courses && mysqli_num_rows($courses) > 0): ?>
                                <?php while($row = mysqli_fetch_assoc($courses)): ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center mb-1">
                                            <span class="badge badge-code mr-2"><?= $row['course_code'] ?></span>
                                            <span class="font-weight-bold"><?= htmlspecialchars($row['course_title']) ?></span>
                                        </div>
                                        <small class="text-muted"><i class="fas fa-chalkboard-teacher mr-1"></i><?= htmlspecialchars($row['FirstName'] . ' ' . $row['LastName']) ?></small>
                                    </td>
                                    <td>
                                        <span class="badge badge-soft-primary px-2"><?= $row['day'] ?></span><br>
                                        <small><?= date("h:i A", strtotime($row['time_start'])) ?> - <?= date("h:i A", strtotime($row['time_end'])) ?></small>
                                    </td>
                                    <td class="align-middle small">Room <?= htmlspecialchars($row['room_id']) ?></td>
                                    <td class="align-middle">
                                        <a href="view_subject_attendance.php?course_code=<?= $row['course_code'] ?>" class="btn btn-sm btn-primary py-1 px-3" style="border-radius: 15px;">View</a>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr><td colspan="4" class="text-center py-4 text-muted">No classes assigned yet.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- RIGHT COLUMN: PROFILE & STATS -->
        <div class="col-lg-4">
            <div class="card table-card bg-white p-4">
                <h5 class="font-weight-bold mb-4">Attendance Stats</h5>
                <?php if ($attendance_records && mysqli_num_rows($attendance_records) > 0): ?>
                    <?php while($att = mysqli_fetch_assoc($attendance_records)): 
                        $percent = ($att['total'] > 0) ? ($att['present'] / $att['total']) * 100 : 0;
                        $color = ($percent < 75) ? 'danger' : 'success';
                    ?>
                    <div class="mb-4">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="small font-weight-bold"><?= $att['course_code'] ?> <span class="text-muted font-weight-normal">| <?= htmlspecialchars($att['course_title']) ?></span></span>
                            <span class="small text-<?= $color ?> font-weight-bold"><?= round($percent, 1) ?>%</span>
                        </div>
                        <div class="progress">
                            <div class="progress-bar bg-<?= $color ?>" style="width: <?= $percent ?>%"></div>
                        </div>
                    </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p class="text-center text-muted py-4">No records found.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

</body>
</html>