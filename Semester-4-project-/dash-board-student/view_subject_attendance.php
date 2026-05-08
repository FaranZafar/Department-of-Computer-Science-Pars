<?php
session_start();
include_once("../dbconnection.php");

// 1. SECURITY & SESSION CHECK
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

/** * 2. FETCH COURSE & STUDENT CONTEXT
 * Fetching course_id here first prevents the "Subquery returns more than 1 row" error.
 */
$info_query = "
    SELECT c.course_id, c.course_title, c.course_code, s.FirstName, s.LastName
    FROM courses c
    LEFT JOIN assigned_teacher at ON c.course_id = at.course_id
    LEFT JOIN Staff s ON at.StaffID = s.StaffID
    WHERE c.course_code = '$course_code' 
    LIMIT 1";

$info_result = mysqli_query($con, $info_query);
$course_info = mysqli_fetch_assoc($info_result);

// Redirect if the course doesn't exist
if (!$course_info) {
    header("Location: student_dashboard.php");
    exit();
}

$current_course_id = $course_info['course_id'];

/** * 3. FETCH DETAILED LOGS FOR THIS SPECIFIC SUBJECT
 */
$logs_query = "
    SELECT date, status, time_recorded 
    FROM student_attendance 
    WHERE student_id = '$student_id' 
    AND course_id = '$current_course_id'
    ORDER BY date DESC";

$logs = mysqli_query($con, $logs_query);

/** * 4. CALCULATE STATS FOR THE HEADER
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

// Prevent division by zero
$percent = ($stats['total'] > 0) ? ($stats['present'] / $stats['total']) * 100 : 0;
$status_color = ($percent < 75) ? 'text-danger' : 'text-success';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance Details | <?= htmlspecialchars($course_code) ?></title>
    <link rel="icon" type="image/png" href="../images/pars.png">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        body { 
            background-color: #f0f2f5; 
            font-family: 'Segoe UI', sans-serif; 
        }
        .detail-header { 
            background: #152259; 
            color: white; 
            border-bottom: 3px solid #ffc107; 
            padding: 40px 0; 
        }
        .card { 
            border-radius: 15px; 
            border: none; 
            box-shadow: 0 5px 15px rgba(0,0,0,0.05); 
        }
        .status-badge { 
            border-radius: 20px; 
            padding: 4px 12px; 
            font-size: 0.85rem; font-weight: 600; 
        }
        .badge-present { 
            background-color: #d4edda; 
            color: #155724;
        }
        .badge-absent { 
            background-color: #f8d7da; 
            color: #721c24;
        }
        .back-btn { 
            color: #ffc107; 
            text-decoration: none; 
            transition: 0.3s; 
        }
        .back-btn:hover { 
            color: white;
            text-decoration: none;
        }
    </style>
</head>
<body>

<div class="detail-header mb-4">
    <div class="container">
        <a href="student_dashboard.php" class="back-btn mb-3 d-inline-block"><i class="fas fa-arrow-left mr-2"></i> Back to Dashboard</a>
        <div class="row align-items-center">
            <div class="col-md-8">
                <h1 class="font-weight-bold"><?= htmlspecialchars($course_info['course_title']) ?></h1>
                <p class="lead opacity-75"><?= htmlspecialchars($course_code) ?> — Instructor: <?= htmlspecialchars($course_info['FirstName'] ?? 'Staff') ?> <?= htmlspecialchars($course_info['LastName'] ?? '') ?></p>
            </div>
            <div class="col-md-4 text-md-right">
                <div class="h2 mb-0 <?= $status_color ?> font-weight-bold"><?= round($percent, 1) ?>%</div>
                <div class="small text-uppercase tracking-wider">Overall Attendance</div>
            </div>
        </div>
    </div>
</div>

<div class="container">
    <div class="row">
        <div class="col-md-4 mb-4">
            <div class="card p-4 h-100">
                <h5 class="font-weight-bold mb-4">Course Summary</h5>
                <div class="d-flex justify-content-between mb-3 border-bottom pb-2">
                    <span class="text-muted">Total Classes:</span>
                    <span class="font-weight-bold text-dark"><?= $stats['total'] ?></span>
                </div>
                <div class="d-flex justify-content-between mb-3 border-bottom pb-2">
                    <span class="text-muted">Total Present:</span>
                    <span class="font-weight-bold text-success"><?= $stats['present'] ?></span>
                </div>
                <div class="d-flex justify-content-between mb-3">
                    <span class="text-muted">Total Absent:</span>
                    <span class="font-weight-bold text-danger"><?= $stats['total'] - $stats['present'] ?></span>
                </div>
                
                <div class="alert <?= ($percent < 75) ? 'alert-warning' : 'alert-info' ?> mt-auto mb-0" role="alert">
                    <small>
                        <i class="fas fa-info-circle mr-1"></i>
                        <?= ($percent < 75) ? "Warning: Your attendance is below the required 75%." : "Great! Your attendance is above the 75% requirement." ?>
                    </small>
                </div>
            </div>
        </div>

        <div class="col-md-8 mb-4">
            <div class="card overflow-hidden">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 font-weight-bold text-primary">Attendance History</h5>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th>#</th>
                                <th>Date</th>
                                <th>Day</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($logs && mysqli_num_rows($logs) > 0): ?>
                                <?php $count = 1; while($row = mysqli_fetch_assoc($logs)): ?>
                                <tr>
                                    <td><?= $count++ ?></td>
                                    <td class="font-weight-bold"><?= date("d M, Y", strtotime($row['date'])) ?></td>
                                    <td class="text-muted"><?= date("l", strtotime($row['date'])) ?></td>
                                    <td>
                                        <?php if($row['status'] == 'Present'): ?>
                                            <span class="status-badge badge-present"><i class="fas fa-check mr-1"></i> Present</span>
                                        <?php else: ?>
                                            <span class="status-badge badge-absent"><i class="fas fa-times mr-1"></i> Absent</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4" class="text-center py-5 text-muted">
                                        <i class="fas fa-calendar-times fa-3x mb-3 d-block"></i>
                                        No attendance records found for this subject.
                                    </td>
                                </tr>
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