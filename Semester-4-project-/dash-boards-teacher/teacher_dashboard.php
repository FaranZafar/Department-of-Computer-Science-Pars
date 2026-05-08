<?php
session_start();
include_once("../dbconnection.php"); 

if (!isset($_SESSION['user_id'])) { header("Location: ../login.php"); exit(); }

$teacher_id = $_SESSION['user_id'];
$view_date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');

// Fetch teacher info
$teacher_res = mysqli_query($con, "SELECT FirstName, LastName FROM staff WHERE StaffID = '$teacher_id'");
$teacher = mysqli_fetch_assoc($teacher_res);

// Fetch data with dynamic date check and schedule details
$query = "SELECT 
            c.course_title, c.course_code, c.course_id,
            sec.section_name, sec.section_id,
            r.room_name, at.day, at.time_start, at.time_end,
            s.full_name, s.ag_no, s.email, s.student_id,
            att.status AS daily_status,
            (SELECT COUNT(*) FROM student_attendance sa 
             WHERE sa.course_id = c.course_id 
             AND sa.section_id = sec.section_id 
             AND sa.date = '$view_date') as submission_check
          FROM assigned_teacher at
          INNER JOIN courses c ON at.course_id = c.course_id
          INNER JOIN sections sec ON at.section_id = sec.section_id
          LEFT JOIN room r ON at.room_id = r.room_id
          LEFT JOIN students s ON sec.section_id = s.section_id 
          LEFT JOIN student_attendance att ON (s.student_id = att.student_id AND att.date = '$view_date' AND att.course_id = c.course_id)
          WHERE at.StaffID = '$teacher_id'
          ORDER BY c.course_title ASC, s.full_name ASC";

$result = mysqli_query($con, $query);
$data = [];
if ($result) {
    while($row = mysqli_fetch_assoc($result)) {
        $course_key = $row['course_code'] . "-" . $row['section_id'];
        if (!isset($data[$course_key])) {
            $data[$course_key] = [
                'details' => [
                    'id' => $row['course_id'], 
                    'title' => $row['course_title'], 
                    'code' => $row['course_code'], 
                    'section' => $row['section_name'], 
                    'sec_id' => $row['section_id'],
                    'day' => $row['day'], 
                    'start' => $row['time_start'], 
                    'end' => $row['time_end'], 
                    'room' => $row['room_name'],
                    'is_submitted' => ($row['submission_check'] > 0)
                ],
                'students' => [] 
            ];
        }
        if ($row['ag_no']) {
            $data[$course_key]['students'][$row['ag_no']] = [
                'student_id' => $row['student_id'], 
                'full_name' => $row['full_name'], 
                'status' => $row['daily_status']
            ];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Instructor Dashboard</title>
    <link rel="icon" type="image/png" href="../images/pars.png">
    
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        body { 
            background-color: #f4f7f6; 
            font-family: 'Inter', sans-serif; 
        }
        .course-card { 
            border: none; 
            border-radius: 12px; 
            margin-bottom: 20px; 
            box-shadow: 0 2px 10px rgba(0,0,0,0.05); 
        }
        .btn-course { 
            width: 100%; 
            text-align: left; 
            padding: 1.5rem; 
            background: #fff; 
            border: none; 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
        }
        .btn-course:not(.collapsed) { 
            background-color: #eef2ff; 
            border-bottom: 2px solid #2563eb; 
        }
        .sched-info { 
            font-size: 0.85rem; 
            color: #64748b; 
            margin-top: 5px;
         }
        
        .print-only-status { 
            
            display: none; 
            font-weight: bold; 
        }

        @media print {
            body * { 
                visibility: hidden; 
            }
            .print-section, .print-section * { 
                visibility: visible; 
            }
            .print-section { 
                position: absolute; 
                left: 0; 
                top: 0; 
                width: 100%; 
                background: white; 
            }
            .no-print { 
                display: none !important; 
            }
            .btn-group-toggle { 
                display: none !important; 
            }
            .print-only-status { 
                display: block !important; 
            }
            table { 
                width: 100% !important; 
                border-collapse: collapse; 
            }
            th, td { 
                border: 1px solid #000 !important; 
                padding: 8px !important; 
            }
        }
    </style>
</head>
<body>

<div class="container py-4">
    <div class="row align-items-center mb-4 no-print">
        <div class="col-md-6">
            <h2 class="font-weight-bold">Instructor Dashboard</h2>
            <p class="text-muted">Instructor: <?= htmlspecialchars($teacher['FirstName'] . " " . $teacher['LastName']) ?></p>
        </div>
        <div class="col-md-6 text-right">
            <form method="GET" class="form-inline justify-content-end">
                <label class="mr-2 font-weight-bold">Date:</label>
                <input type="date" name="date" class="form-control mr-2" value="<?= $view_date ?>" onchange="this.form.submit()">
                <a href="../logout.php" class="btn btn-danger btn-sm"><i class="fas fa-sign-out-alt">Log Out</i></a>
            </form>
        </div>
    </div>

    <?php if (empty($data)): ?>
        <div class="alert alert-warning">No classes found for this date.</div>
    <?php else: ?>
        <div id="courseAccordion">
            <?php $i = 0; foreach($data as $key => $content): $i++; ?>
                <div class="card course-card print-target-<?= $i ?>">
                    <div class="card-header p-0 no-print">
                        <button class="btn btn-course collapsed" data-toggle="collapse" data-target="#collapse<?= $i ?>">
                            <div>
                                <span class="h5 mb-0 font-weight-bold"><?= $content['details']['code'] ?>: <?= $content['details']['title'] ?></span>
                                <span class="badge badge-info ml-2">Section: <?= $content['details']['section'] ?></span>
                                
                                <?php if($content['details']['is_submitted']): ?>
                                    <span class="badge badge-success ml-1"><i class="fas fa-check-circle"></i> Marked</span>
                                <?php else: ?>
                                    <span class="badge badge-danger ml-1"><i class="fas fa-times-circle"></i> Not Marked</span>
                                <?php endif; ?>

                                <div class="sched-info">
                                    <i class="far fa-clock"></i> <?= $content['details']['day'] ?> | 
                                    <?= date("g:i A", strtotime($content['details']['start'])) ?> - <?= date("g:i A", strtotime($content['details']['end'])) ?> | 
                                    <i class="fas fa-map-marker-alt"></i> Room: <?= $content['details']['room'] ?>
                                </div>
                            </div>
                            <i class="fas fa-chevron-down"></i>
                        </button>
                    </div>

                    <div id="collapse<?= $i ?>" class="collapse" data-parent="#courseAccordion">
                        <div class="print-section">
                            <div class="d-none d-print-block mb-4 text-center">
                                <h3>Attendance Sheet</h3>
                                <h5><?= $content['details']['code'] ?> - <?= $content['details']['title'] ?> (Section: <?= $content['details']['section'] ?>)</h5>
                                <p>Date: <?= date('M d, Y', strtotime($view_date)) ?> | Room: <?= $content['details']['room'] ?></p>
                            </div>

                            <form action="take_attendance.php" method="POST">
                                <input type="hidden" name="course_id" value="<?= $content['details']['id'] ?>">
                                <input type="hidden" name="section_id" value="<?= $content['details']['sec_id'] ?>">
                                <input type="hidden" name="attendance_date" value="<?= $view_date ?>">

                                <div class="card-body p-0">
                                    <table class="table table-bordered table-hover mb-0">
                                        <thead class="thead-light">
                                            <tr>
                                                <th>AG Number</th>
                                                <th>Student Name</th>
                                                <th class="text-center">Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach($content['students'] as $ag_no => $s): ?>
                                                <tr>
                                                    <td><?= $ag_no ?></td>
                                                    <td><?= htmlspecialchars($s['full_name']) ?></td>
                                                    <td class="text-center">
                                                        <span class="print-only-status">
                                                            <?= $s['status'] ? $s['status'] : 'Not Marked' ?>
                                                        </span>

                                                        <div class="btn-group btn-group-toggle no-print" data-toggle="buttons">
                                                            <label class="btn btn-sm btn-outline-success <?= ($s['status'] == 'Present' || !$s['status']) ? 'active' : '' ?>">
                                                                <input type="radio" name="status[<?= $s['student_id'] ?>]" value="Present" <?= ($s['status'] == 'Present' || !$s['status']) ? 'checked' : '' ?>> Present
                                                            </label>
                                                            <label class="btn btn-sm btn-outline-danger <?= ($s['status'] == 'Absent') ? 'active' : '' ?>">
                                                                <input type="radio" name="status[<?= $s['student_id'] ?>]" value="Absent" <?= ($s['status'] == 'Absent') ? 'checked' : '' ?>> Absent
                                                            </label>
                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                                
                                <div class="submit-area d-flex justify-content-between p-3 no-print">
                                    <button type="button" class="btn btn-secondary" onclick="printSection(<?= $i ?>)">
                                        <i class="fas fa-print mr-1"></i> Print Report
                                    </button>
                                    <button type="submit" class="btn btn-primary px-5">
                                        <i class="fas fa-save mr-1"></i> <?= $content['details']['is_submitted'] ? 'Update Records' : 'Save Attendance' ?>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/jquery@3.5.1/dist/jquery.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
function printSection(index) {
    const target = document.querySelector('.print-target-' + index);
    target.classList.add('print-section');
    window.print();
    target.classList.remove('print-section');
}
</script>
</body>
</html>