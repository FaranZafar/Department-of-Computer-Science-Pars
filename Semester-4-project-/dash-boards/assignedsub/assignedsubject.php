<?php
session_start();
include_once("../../dbconnection.php"); 

if (!isset($_SESSION['user_id'])) { header("Location: ../login.php"); exit(); }

$teacher_id = $_SESSION['user_id'];

// Fetch teacher info
$teacher_res = mysqli_query($con, "SELECT FirstName, LastName FROM staff WHERE StaffID = '$teacher_id'");
$teacher = mysqli_fetch_assoc($teacher_res);

$query = "SELECT 
            c.course_title, c.course_code, c.course_id,
            sec.section_name, sec.section_id,
            r.room_name,
            at.day, at.time_start, at.time_end,
            s.full_name, s.ag_no, s.email
          FROM assigned_teacher at
          INNER JOIN courses c ON at.course_id = c.course_id
          INNER JOIN sections sec ON at.section_id = sec.section_id
          LEFT JOIN room r ON at.room_id = r.room_id
          LEFT JOIN students s ON sec.section_id = s.section_id 
          WHERE at.StaffID = '$teacher_id'
          ORDER BY c.course_title ASC, at.day ASC, s.full_name ASC";

$result = mysqli_query($con, $query);

$data = [];
if ($result) {
    while($row = mysqli_fetch_assoc($result)) {
        $course_key = $row['course_code'] . "-" . $row['section_id'];
        
        if (!isset($data[$course_key])) {
            $data[$course_key] = [
                'details' => [
                    'id'      => $row['course_id'],
                    'title'   => $row['course_title'], 
                    'code'    => $row['course_code'], 
                    'section' => $row['section_name'],
                    'sec_id'  => $row['section_id']
                ],
                'schedule' => [], 
                'students' => [] 
            ];
        }

        $sched_id = $row['day'] . $row['time_start'] . $row['room_name'];
        if (!isset($data[$course_key]['schedule'][$sched_id])) {
            $data[$course_key]['schedule'][$sched_id] = [
                'day'   => $row['day'],
                'start' => $row['time_start'],
                'end'   => $row['time_end'],
                'room'  => $row['room_name'] ?? 'TBA'
            ];
        }

        if ($row['ag_no'] && !isset($data[$course_key]['students'][$row['ag_no']])) {
            $data[$course_key]['students'][$row['ag_no']] = [
                'full_name' => $row['full_name'],
                'email'     => $row['email']
            ];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teacher Dashboard</title>
    <link rel="icon" type="image/png" href="../../images/pars.png">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        body { background-color: #f8fafc; font-family: 'Inter', sans-serif; }
        .course-card { border: none; border-radius: 15px; margin-bottom: 25px; box-shadow: 0 4px 12px rgba(0,0,0,0.08); overflow: hidden; }
        .btn-course { width: 100%; text-align: left; padding: 20px; background: #fff; border: none; display: flex; justify-content: space-between; align-items: center; }
        .btn-course:not(.collapsed) { background-color: #e0eaff; color: #2563eb; }
        .sched-item { background: #f1f5f9; padding: 5px 12px; border-radius: 8px; font-size: 0.85rem; margin-right: 8px; display: inline-block; border: 1px solid #dee2e6; }
        .attendance-radio { width: 18px; height: 18px; cursor: pointer; }
        .submit-area { background: #f8f9fa; border-top: 1px solid #dee2e6; padding: 15px 25px; }
    </style>
</head>
<body>

<div class="container py-5">
    <!-- DASHBOARD HEADER & DATE -->
    <div class="d-flex justify-content-between align-items-start mb-5">
        <div>
            <h2 class="font-weight-bold mb-1">Instructor Dashboard</h2>
            <p class="text-primary font-weight-bold mb-0"><i class="far fa-calendar-alt mr-2"></i>Today is <?= date('l, F j, Y') ?></p>
            <p class="text-muted small">Welcome, Prof. <?= htmlspecialchars($teacher['FirstName'] . " " . $teacher['LastName']) ?></p>
        </div>
        <a href="../../logout.php" class="btn btn-outline-danger shadow-sm"><i class="fas fa-sign-out-alt mr-2"></i>Logout</a>
    </div>

    <?php if (empty($data)): ?>
        <div class="alert alert-info shadow-sm">No assigned courses found for your profile.</div>
    <?php else: ?>
        <div id="courseAccordion">
            <?php $i = 0; foreach($data as $key => $content): $i++; ?>
                <div class="card course-card">
                    <!-- HEADER -->
                    <div class="card-header p-0">
                        <button class="btn btn-course <?php echo ($i > 1) ? 'collapsed' : ''; ?>" data-toggle="collapse" data-target="#collapse<?= $i ?>">
                            <div>
                                <span class="h5 mb-0 font-weight-bold">#<?= $content['details']['code'] ?> - <?= $content['details']['title'] ?></span>
                                <span class="badge badge-pill badge-primary ml-2"><?= $content['details']['section'] ?></span>
                                <div class="mt-2">
                                    <?php foreach($content['schedule'] as $s): ?>
                                        <span class="sched-item">
                                            <i class="far fa-clock mr-1 text-primary"></i> 
                                            <?= $s['day'] ?>: 
                                            <strong><?= date("g:i A", strtotime($s['start'])) ?> — <?= date("g:i A", strtotime($s['end'])) ?></strong>
                                            <span class="ml-2 text-muted">|</span>
                                            <span class="ml-2"><i class="fas fa-map-marker-alt mr-1"></i> <?= $s['room'] ?></span>
                                        </span>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            <div class="text-right">
                                <div class="student-count h6 mb-0"><?= count($content['students']) ?> Students</div>
                                <i class="fas fa-chevron-down mt-2"></i>
                            </div>
                        </button>
                    </div>

                    <!-- COLLAPSIBLE BODY (ATTENDANCE FORM) -->
                    <div id="collapse<?= $i ?>" class="collapse <?php echo ($i == 1) ? 'show' : ''; ?>" data-parent="#courseAccordion">
                        <form action="save_attendance.php" method="POST">
                            <!-- Hidden info for the processor -->
                            <input type="hidden" name="course_id" value="<?= $content['details']['id'] ?>">
                            <input type="hidden" name="section_id" value="<?= $content['details']['sec_id'] ?>">
                            <input type="hidden" name="attendance_date" value="<?= date('Y-m-d') ?>">

                            <div class="card-body p-0">
                                <table class="table table-hover mb-0">
                                    <thead class="bg-light">
                                        <tr>
                                            <th class="pl-4 py-3">Student ID</th>
                                            <th class="py-3">Full Name</th>
                                            <th class="py-3 text-center text-success">Present</th>
                                            <th class="py-3 text-center text-danger">Absent</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if(empty($content['students'])): ?>
                                            <tr><td colspan="4" class="text-center py-4 text-muted">No students enrolled in this section.</td></tr>
                                        <?php else: ?>
                                            <?php foreach($content['students'] as $ag_no => $s): ?>
                                                <tr>
                                                    <td class="pl-4 align-middle"><strong><?= $ag_no ?></strong></td>
                                                    <td class="align-middle"><?= htmlspecialchars($s['full_name']) ?></td>
                                                    <td class="text-center align-middle">
                                                        <input type="radio" name="status[<?= $ag_no ?>]" value="Present" class="attendance-radio" checked>
                                                    </td>
                                                    <td class="text-center align-middle">
                                                        <input type="radio" name="status[<?= $ag_no ?>]" value="Absent" class="attendance-radio">
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                            
                            <!-- SUBMIT BUTTON AT END OF SECTION -->
                            <?php if(!empty($content['students'])): ?>
                            <div class="submit-area text-right">
                                <button type="submit" class="btn btn-primary px-5 font-weight-bold shadow-sm">
                                    <i class="fas fa-save mr-2"></i> Submit Attendance for <?= $content['details']['section'] ?>
                                </button>
                            </div>
                            <?php endif; ?>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/jquery@3.5.1/dist/jquery.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>