<?php
session_start();
include_once("../../dbconnection.php"); 

if (!isset($_SESSION['user_id'])) { header("Location: ../login.php"); exit(); }

$teacher_id = $_SESSION['user_id'];
$view_date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');

// Fetch teacher info
$teacher_res = mysqli_query($con, "SELECT FirstName, LastName FROM staff WHERE StaffID = '$teacher_id'");
$teacher = mysqli_fetch_assoc($teacher_res);

// FIXED QUERY: Uses LEFT JOIN to ensure the subject shows even if students aren't assigned yet
$query = "SELECT 
            at.subjectassigned_id, at.day, at.time_start, at.time_end,
            c.course_title, c.course_code, c.course_id,
            sec.section_name, sec.section_id,
            r.room_name,
            s.full_name, s.ag_no, s.student_id,
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
        // Use subjectassigned_id for the key to handle multiple sections of the same course
        $course_key = $row['subjectassigned_id'];
        
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
                    'room' => $row['room_name'] ?? 'TBD',
                    'is_submitted' => ($row['submission_check'] > 0)
                ],
                'students' => [] 
            ];
        }
        
        if (!empty($row['ag_no'])) {
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
    <title>Instructor Dashboard | UAF</title>
    <link rel="icon" type="image/png" href="../../images/pars.png">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        body { background-color: #f4f7f6; font-family: 'Inter', sans-serif; }
        .course-card { border: none; border-radius: 12px; margin-bottom: 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); overflow: hidden;}
        .btn-course { width: 100%; text-align: left; padding: 1.5rem; background: #fff; border: none; display: flex; justify-content: space-between; align-items: center; }
        .btn-course:not(.collapsed) { background-color: #eef2ff; border-bottom: 2px solid #2563eb; }
        .sched-info { font-size: 0.85rem; color: #64748b; margin-top: 5px; }

        /* PROFESSIONAL PRINT UI CSS */
        .print-header-section { display: none; }
        .print-only-status { display: none; font-weight: bold; }

        @media print {
            body * { visibility: hidden; }
            .print-active, .print-active * { visibility: visible; }
            .print-active { position: absolute; left: 0; top: 0; width: 100%; background: white; }
            .no-print { display: none !important; }
            
            .print-header-section { 
                display: block !important; 
                margin-bottom: 20px;
                border-bottom: 2px solid #000;
                padding-bottom: 10px;
            }
            .print-top-row {
                display: flex;
                justify-content: space-between;
                align-items: center;
                margin-bottom: 10px;
            }
            .print-logo { width: 60px; height: auto; }
            .uni-info { text-align: center; margin-bottom: 15px; flex-grow: 1; }
            .uni-info h2 { margin: 0; font-weight: 800; font-size: 22px; text-transform: uppercase; }
            
            .meta-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; font-size: 13px; margin-bottom: 15px; }
            .meta-box { border-bottom: 1px dashed #ccc; padding: 2px 0; }
            .meta-label { font-weight: bold; width: 90px; display: inline-block; }

            table { width: 100% !important; border-collapse: collapse !important; }
            th { background-color: #f2f2f2 !important; -webkit-print-color-adjust: exact; border: 1px solid #000 !important; }
            td { border: 1px solid #000 !important; padding: 6px !important; }
            .print-only-status { display: block !important; }
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
                <input type="date" name="date" class="form-control mr-2" value="<?= $view_date ?>" onchange="this.form.submit()">
                <a href="../../logout.php" class="btn btn-danger btn-sm"><i class="fas fa-sign-out-alt"></i> Log Out</a>
            </form>
        </div>
    </div>

    <?php if (empty($data)): ?>
        <div class="alert alert-warning text-center">
            No classes assigned for your Staff ID (<?= $teacher_id ?>) in the database.
        </div>
    <?php else: ?>
        <div id="courseAccordion">
            <?php $i = 0; foreach($data as $key => $content): $i++; ?>
                <div class="card course-card" id="print-area-<?= $i ?>">
                    
                    <div class="card-header p-0 no-print">
                        <button class="btn btn-course collapsed" data-toggle="collapse" data-target="#collapse<?= $i ?>">
                            <div>
                                <span class="h5 mb-0 font-weight-bold"><?= $content['details']['code'] ?>: <?= $content['details']['title'] ?></span>
                                <span class="badge badge-info ml-2">Section: <?= $content['details']['section'] ?></span>
                                
                                <span class="badge badge-<?= $content['details']['is_submitted'] ? 'success' : 'danger' ?> ml-1">
                                    <i class="fas fa-<?= $content['details']['is_submitted'] ? 'check' : 'times' ?>-circle"></i> 
                                    <?= $content['details']['is_submitted'] ? 'Marked' : 'Not Marked' ?>
                                </span>

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
                        
                        <!-- PROFESSIONAL PRINT HEADER -->
                        <div class="print-header-section">
                            <div class="print-top-row">
                                <div class="left-logo">
                                    <img src="../../images/pars.png" alt="UAF Logo" class="print-logo">
                                </div>
                                <div class="uni-info">
                                    <h2>UNIVERSITY OF AGRICULTURE FAISALABAD</h2>
                                    <h4>Department Of Computer Science Pars</h4>
                                </div>
                            </div>
                            <div class="meta-grid">
                                <div class="meta-box"><span class="meta-label">Course:</span> <?= $content['details']['code'] ?> - <?= $content['details']['title'] ?></div>
                                <div class="meta-box"><span class="meta-label">Instructor:</span> <?= $teacher['FirstName'] ?> <?= $teacher['LastName'] ?></div>
                                <div class="meta-box"><span class="meta-label">Section:</span> <?= $content['details']['section'] ?></div>
                                <div class="meta-box"><span class="meta-label">Date:</span> <?= date('d-M-Y', strtotime($view_date)) ?></div>
                                <div class="meta-box"><span class="meta-label">Schedule:</span> <?= $content['details']['day'] ?> (<?= $content['details']['start'] ?>)</div>
                                <div class="meta-box"><span class="meta-label">Venue:</span><?= $content['details']['room'] ?></div>
                            </div>
                        </div>

                        <form action="take_attendance.php" method="POST">
                            <input type="hidden" name="course_id" value="<?= $content['details']['id'] ?>">
                            <input type="hidden" name="section_id" value="<?= $content['details']['sec_id'] ?>">
                            <input type="hidden" name="attendance_date" value="<?= $view_date ?>">

                            <table class="table table-bordered mb-0">
                                <thead class="thead-light">
                                    <tr>
                                        <th>AG Number</th>
                                        <th>Student Name</th>
                                        <th class="text-center">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if(empty($content['students'])): ?>
                                        <tr><td colspan="3" class="text-center text-muted py-3">No students assigned to this section.</td></tr>
                                    <?php else: ?>
                                        <?php foreach($content['students'] as $ag_no => $s): ?>
                                            <tr>
                                                <td class="font-weight-bold"><?= $ag_no ?></td>
                                                <td><?= htmlspecialchars($s['full_name']) ?></td>
                                                <td class="text-center">
                                                    <span class="print-only-status"><?= $s['status'] ? $s['status'] : 'Present' ?></span>
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
                                    <?php endif; ?>
                                </tbody>
                            </table>

                            <div class="d-flex justify-content-between p-3 no-print">
                                <?php if($content['details']['is_submitted']): ?>
                                    <button type="button" class="btn btn-dark" onclick="runProfessionalPrint(<?= $i ?>)">
                                        <i class="fas fa-print mr-2"></i> Print Sheet
                                    </button>
                                <?php else: ?>
                                    <span></span>
                                <?php endif; ?>
                                
                                <?php if(!empty($content['students'])): ?>
                                    <button type="submit" class="btn btn-primary px-5">
                                        <i class="fas fa-save mr-2"></i> <?= $content['details']['is_submitted'] ? 'Update' : 'Save Attendance' ?>
                                    </button>
                                <?php endif; ?>
                            </div>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/jquery@3.5.1/dist/jquery.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
function runProfessionalPrint(index) {
    const area = document.getElementById('print-area-' + index);
    const rows = area.querySelectorAll('tbody tr');
    rows.forEach(row => {
        const checkedInput = row.querySelector('input[type="radio"]:checked');
        const statusSpan = row.querySelector('.print-only-status');
        if(checkedInput) {
            statusSpan.innerText = checkedInput.value;
        }
    });

    area.classList.add('print-active');
    window.print();
    area.classList.remove('print-active');
}
</script>
</body>
</html>