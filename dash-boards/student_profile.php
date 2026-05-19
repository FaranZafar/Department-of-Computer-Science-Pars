<?php
include_once("../dbconnection.php");

$student_id = $_GET['id'] ?? null;

if (!$student_id) {
    header("Location: view_students.php");
    exit();
}

// 1. Fetch Comprehensive Student Info
$student_query = "SELECT s.*, d.degree_name, sem.semester_name, sec.section_name 
                  FROM students s
                  LEFT JOIN degree d ON s.degree_id = d.degree_id
                  LEFT JOIN semester sem ON s.semester_id = sem.semester_id
                  LEFT JOIN sections sec ON s.section_id = sec.section_id
                  WHERE s.student_id = ?";
$stmt = $con->prepare($student_query);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$student = $stmt->get_result()->fetch_assoc();

// 2. Fetch Course Schedule, Full Teacher Name & Room Name (Ref: image_5fddf8.png)
$current_section = $student['section_id'] ?? 0;
$courses_query = "SELECT 
                    c.course_title, 
                    c.course_code, 
                    at.day, 
                    at.time_start, 
                    at.time_end, 
                    r.room_name,
                    CONCAT(st.FirstName, ' ', st.LastName) as full_teacher_name
                  FROM assigned_teacher at
                  INNER JOIN courses c ON at.course_id = c.course_id
                  LEFT JOIN staff st ON at.StaffID = st.StaffID
                  LEFT JOIN room r ON at.room_id = r.room_id
                  WHERE at.section_id = ?";
$stmt_c = $con->prepare($courses_query);
$stmt_c->bind_param("i", $current_section);
$stmt_c->execute();
$courses = $stmt_c->get_result()->fetch_all(MYSQLI_ASSOC);

// 3. Attendance Summary & History
$att_summary_query = "SELECT 
                        COUNT(*) as total,
                        SUM(CASE WHEN status = 'Present' THEN 1 ELSE 0 END) as present,
                        SUM(CASE WHEN status = 'Absent' THEN 1 ELSE 0 END) as absent
                      FROM student_attendance WHERE student_id = ?";
$stmt_s = $con->prepare($att_summary_query);
$stmt_s->bind_param("i", $student_id);
$stmt_s->execute();
$att_stats = $stmt_s->get_result()->fetch_assoc();

$att_history_query = "SELECT a.*, c.course_title 
                      FROM student_attendance a 
                      LEFT JOIN courses c ON a.course_id = c.course_id 
                      WHERE a.student_id = ? 
                      ORDER BY a.date DESC LIMIT 10";
$stmt_h = $con->prepare($att_history_query);
$stmt_h->bind_param("i", $student_id);
$stmt_h->execute();
$att_history = $stmt_h->get_result()->fetch_all(MYSQLI_ASSOC);

$percentage = ($att_stats['total'] > 0) ? round(($att_stats['present'] / $att_stats['total']) * 100, 1) : 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Student Profile | <?php echo htmlspecialchars($student['full_name']); ?></title>
    <link rel="icon" type="image/png" href="../images/pars.png">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        body { background-color: #f8f9fa; }
        .card { border: none; border-radius: 10px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
        .bg-uaf { background: #1a5276; color: white; }
        .table thead th { background-color: #f2f4f4; color: #566573; text-transform: uppercase; font-size: 0.8rem; }
    </style>
</head>
<body>

<div class="container py-5">
    <div class="row">
        <!-- Sidebar -->
        <div class="col-lg-4">
            <div class="card text-center mb-4 p-3">
                <div class="card-body">
                    <i class="fas fa-user-circle fa-6x text-secondary mb-3"></i>
                    <h4 class="font-weight-bold"><?php echo htmlspecialchars($student['full_name']); ?></h4>
                    <span class="badge badge-primary px-3"><?php echo htmlspecialchars($student['ag_no']); ?></span>
                    <hr>
                    <ul class="list-unstyled text-left small">
                        <li class="mb-2"><strong>Degree:</strong> <?php echo htmlspecialchars($student['degree_name']); ?></li>
                        <li class="mb-2"><strong>Semester:</strong> <?php echo htmlspecialchars($student['semester_name']); ?></li>
                        <li><strong>Section:</strong> <?php echo htmlspecialchars($student['section_name']); ?></li>
                    </ul>
                </div>
            </div>

            <div class="card bg-uaf p-3">
                <h6>Attendance Overview</h6>
                <h2 class="font-weight-bold"><?php echo $percentage; ?>%</h2>
                <div class="progress mb-3" style="height: 5px;">
                    <div class="progress-bar bg-white" style="width: <?php echo $percentage; ?>%"></div>
                </div>
                <div class="d-flex justify-content-between small">
                    <span>Present: <?php echo $att_stats['present']; ?></span>
                    <span>Absent: <?php echo $att_stats['absent']; ?></span>
                </div>
            </div>
            <hr>
           <a href="./view_students.php" class="btn btn-block btn-outline-primary">Back</a>
            


        </div>

        <!-- Main Content -->
        <div class="col-lg-8">
            <!-- Timetable -->
            <div class="card mb-4">
                <div class="card-header bg-white font-weight-bold">
                    <i class="fas fa-calendar-alt mr-2 text-primary"></i> Class Schedule
                </div>
                <div class="table-responsive">
                    <table class="table mb-0">
                        <thead>
                            <tr>
                                <th>Course</th>
                                <th>Instructor</th>
                                <th>Schedule</th>
                                <th>Room</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($courses as $c): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($c['course_code']); ?></strong></td>
                                <td><?php echo htmlspecialchars($c['full_teacher_name'] ?? 'TBA'); ?></td>
                                <td><small><?php echo $c['day']; ?> (<?php echo date("h:i A", strtotime($c['time_start'])); ?>)</small></td>
                                <td><span class="badge badge-success"><?php echo htmlspecialchars($c['room_name'] ?? 'N/A'); ?></span></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- History -->
            <div class="card">
                <div class="card-header bg-white font-weight-bold">
                    <i class="fas fa-history mr-2 text-info"></i> Recent Attendance History
                </div>
                <div class="table-responsive">
                    <table class="table table-striped mb-0">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Course</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($att_history as $log): ?>
                            <tr>
                                <td><?php echo date("M d, Y", strtotime($log['date'])); ?></td>
                                <td><?php echo htmlspecialchars($log['course_title']); ?></td>
                                <td>
                                    <span class="badge <?php echo ($log['status'] == 'Present') ? 'badge-success' : 'badge-danger'; ?>">
                                        <?php echo $log['status']; ?>
                                    </span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>