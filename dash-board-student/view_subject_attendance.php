<?php
session_start();
include_once("../dbconnection.php");

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== "Student") {
    header("Location: ../login.php");
    exit();
}

$student_id = $_SESSION['user_id'];
$course_code = mysqli_real_escape_string($con, $_GET['course_code'] ?? '');

if (empty($course_code)) {
    header("Location: student_dashboard.php");
    exit();
}

/** 
 * 1. FETCH PRECISE COURSE, TEACHER, AND STUDENT CONTEXT
 * Removed 'st.name' to fix the "Unknown column" error.
 */
$info_query = "
    SELECT 
        c.course_id, 
        c.course_title, 
        c.course_code, 
        staff.FirstName AS teacher_fname, 
        staff.LastName AS teacher_lname,
        st.full_name AS std_fname,
        st.ag_no
    FROM students st
    INNER JOIN sections sec ON st.section_id = sec.section_id
    CROSS JOIN courses c 
    LEFT JOIN assigned_teacher at ON (c.course_id = at.course_id AND at.section_id = st.section_id)
    LEFT JOIN Staff staff ON at.StaffID = staff.StaffID
    WHERE st.student_id = '$student_id' 
    AND c.course_code = '$course_code'
    LIMIT 1";

$info_result = mysqli_query($con, $info_query);

// Check if query failed
if (!$info_result) {
    die("Query Failed: " . mysqli_error($con));
}

$data = mysqli_fetch_assoc($info_result);

if (!$data) {
    header("Location: student_dashboard.php");
    exit();
}

// Correctly combine names for display
$student_display_name = $data['std_fname'] . " " . ($data['std_lname'] ?? '');
// Logic for handling different naming conventions in your DB
$student_display_name = !empty($data['full_name']) ? $data['full_name'] : ($data['std_fname']);

$current_course_id = $data['course_id'];

/** 
 * 2. FETCH ATTENDANCE LOGS (Specific to this Course)
 */
$logs_query = "
    SELECT date, status 
    FROM student_attendance 
    WHERE student_id = '$student_id' 
    AND course_id = '$current_course_id'
    ORDER BY date DESC";
$logs = mysqli_query($con, $logs_query);

/** 
 * 3. CALCULATE STATS
 */
$stats_query = "
    SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN status = 'Present' THEN 1 ELSE 0 END) as present
    FROM student_attendance 
    WHERE student_id = '$student_id' 
    AND course_id = '$current_course_id'";

$stats_res = mysqli_query($con, $stats_query);
$stats = mysqli_fetch_assoc($stats_res);

$percent = ($stats['total'] > 0) ? ($stats['present'] / $stats['total']) * 100 : 0;
$status_color = ($percent < 75) ? 'text-danger' : 'text-success';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($data['course_code']) ?> | Attendance</title>
    <link rel="icon" type="image/png" href="../images/pars.png">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        body { background-color: #f0f2f5; font-family: 'Segoe UI', sans-serif; }
        .detail-header { background: #152259; color: white; border-bottom: 3px solid #ffc107; padding: 30px 0; margin-bottom: 30px; }
        .card { border-radius: 15px; border: none; box-shadow: 0 5px 15px rgba(0,0,0,0.05); }
        .status-badge { border-radius: 20px; padding: 5px 15px; font-size: 0.8rem; font-weight: 700; }
        .badge-present { background-color: #d4edda; color: #155724; }
        .badge-absent { background-color: #f8d7da; color: #721c24; }
    </style>
</head>
<body>

<div class="detail-header">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <a href="student_dashboard.php" class="text-warning text-decoration-none"><i class="fas fa-arrow-left mr-2"></i> Back to Dashboard</a>
            <span class="badge badge-warning px-3 py-2">
                Student: <?= htmlspecialchars($student_display_name) ?> (<?= htmlspecialchars($data['ag_no']) ?>)
            </span>
        </div>
        <div class="row align-items-center">
            <div class="col-md-8">
                <h1 class="display-5 font-weight-bold mb-1"><?= htmlspecialchars($data['course_title']) ?></h1>
                <p class="h5 text-white-50">
                    <i class="fas fa-code mr-2"></i><?= htmlspecialchars($data['course_code']) ?> 
                    <span class="mx-3">|</span>
                    <i class="fas fa-user-tie mr-2"></i>Instructor: <?= htmlspecialchars(($data['teacher_fname'] ?? 'Staff') . " " . ($data['teacher_lname'] ?? '')) ?>
                </p>
            </div>
            <div class="col-md-4 text-md-right mt-3 mt-md-0">
                <div class="bg-white text-dark d-inline-block p-3 rounded-lg shadow-sm">
                    <div class="h2 mb-0 <?= $status_color ?> font-weight-bold"><?= round($percent, 1) ?>%</div>
                    <div class="small text-muted text-uppercase font-weight-bold">Attendance Rate</div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="container">
    <div class="row">
        <div class="col-md-4 mb-4">
            <div class="card p-4 h-100">
                <h5 class="font-weight-bold mb-4 border-bottom pb-2">Course Metrics</h5>
                <p class="mb-2">Total Sessions: <strong><?= $stats['total'] ?></strong></p>
                <p class="mb-2">Present: <strong class="text-success"><?= $stats['present'] ?></strong></p>
                <p class="mb-4">Absent: <strong class="text-danger"><?= $stats['total'] - $stats['present'] ?></strong></p>
                <div class="alert <?= ($percent < 75) ? 'alert-danger' : 'alert-success' ?> mb-0">
                    <small class="font-weight-bold">
                        <?= ($percent < 75) ? "Warning: Under 75% threshold." : "You are meeting criteria." ?>
                    </small>
                </div>
            </div>
        </div>

        <div class="col-md-8 mb-4">
            <div class="card overflow-hidden">
                <div class="card-header bg-white py-3 border-bottom">
                    <h5 class="mb-0 font-weight-bold text-primary"><i class="fas fa-history mr-2"></i>Attendance Log</h5>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th>#</th>
                                <th>Date</th>
                                <th>Day</th>
                                <th class="text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($logs && mysqli_num_rows($logs) > 0): ?>
                                <?php $count = 1; while($row = mysqli_fetch_assoc($logs)): ?>
                                <tr>
                                    <td><?= $count++ ?></td>
                                    <td><?= date("d M, Y", strtotime($row['date'])) ?></td>
                                    <td><?= date("l", strtotime($row['date'])) ?></td>
                                    <td class="text-center">
                                        <span class="status-badge <?= ($row['status'] == 'Present') ? 'badge-present' : 'badge-absent' ?>">
                                            <?= $row['status'] ?>
                                        </span>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr><td colspan="4" class="text-center py-5">No records found.</td></tr>
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