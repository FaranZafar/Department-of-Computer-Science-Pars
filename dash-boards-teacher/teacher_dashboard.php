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
                'stats' => ['present' => 0, 'absent' => 0],
                'students' => [] 
            ];
        }
        if ($row['ag_no']) {
            $data[$course_key]['students'][$row['ag_no']] = [
                'student_id' => $row['student_id'], 
                'full_name' => $row['full_name'], 
                'status' => $row['daily_status']
            ];
            // Increment Stats
            if($row['daily_status'] == 'Present') $data[$course_key]['stats']['present']++;
            if($row['daily_status'] == 'Absent') $data[$course_key]['stats']['absent']++;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Instructor Dashboard | PARS</title>
    <link rel="icon" type="image/png" href="../images/pars.png">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        body { background-color: #f4f7f6; font-family: 'Inter', sans-serif; }
        .navbar-custom { background: #152259; border-bottom: 3px solid #ffc107; color: white; }
        .course-card { border: none; border-radius: 12px; margin-bottom: 20px; box-shadow: 0 4px 12px rgba(0,0,0,0.08); overflow: hidden; }
        .btn-course { width: 100%; text-align: left; padding: 1.2rem; background: #fff; border: none; display: flex; justify-content: space-between; align-items: center; }
        .btn-course:not(.collapsed) { background-color: #f0f4ff; border-bottom: 2px solid #2563eb; }
        
        /* Dynamic Status for Print */
        .print-status-text { display: none; font-weight: bold; }

        @media print {
            body { background: white !important; }
            .no-print, .btn-group-toggle, .navbar, .search-container, .btn, form > .mt-4 { display: none !important; }
            
            /* Only show the section being printed */
            .card { border: none !important; box-shadow: none !important; margin: 0 !important; padding: 0 !important; }
            .collapse { display: block !important; }
            .course-card { display: none; }
            .course-card.printing { display: block !important; }
            
            /* Show text status instead of buttons */
            .print-status-text { display: inline-block !important; }
            
            .table-sm td, .table-sm th { padding: 0.5rem; border: 1px solid #dee2e6 !important; }
            
            .print-header {
                display: flex !important;
                justify-content: space-between;
                align-items: center;
                border-bottom: 2px solid #152259;
                margin-bottom: 20px;
                padding-bottom: 10px;
            }
        }
        
        .print-header { display: none; }
        .logo-print { width: 80px; height: auto; }
    </style>
</head>
<body>

<nav class="navbar navbar-custom mb-4 no-print">
    <div class="container">
        <span class="navbar-brand font-weight-bold"><i class="fas fa-university mr-2"></i> PARS CAMPUS</span>
        <div class="ml-auto">
            <span class="mr-3 d-none d-md-inline text-white">Welcome, <strong><?= htmlspecialchars($teacher['FirstName']) ?></strong></span>
            <a href="../logout.php" class="btn btn-outline-warning btn-sm">Logout</a>
        </div>
    </div>
</nav>

<div class="container py-2">
    <!-- Header Controls -->
    <div class="row mb-4 no-print">
        <div class="col-md-6">
            <h3 class="font-weight-bold text-dark">Instructor Dashboard</h3>
        </div>
        <div class="col-md-6 text-right">
            <form method="GET" class="form-inline justify-content-end">
                <input type="date" name="date" class="form-control shadow-sm" value="<?= $view_date ?>" onchange="this.form.submit()">
            </form>
        </div>
    </div>

    <?php if (!empty($data)): ?>
        <div id="courseAccordion">
            <?php $i = 0; foreach($data as $key => $content): $i++; ?>
                <div class="card course-card print-target-<?= $i ?>" id="card-<?= $i ?>">
                    
                    <!-- Print Header (Hidden on Screen) -->
                    <div class="print-header">
                        <div>
                            <h2 style="color:#152259; margin:0;">PARS CAMPUS</h2>
                            <p style="margin:0;">Attendance Report: <?= date('M d, Y', strtotime($view_date)) ?></p>
                            <small><?= $content['details']['code'] ?> - <?= $content['details']['title'] ?> (Section <?= $content['details']['section'] ?>)</small>
                        </div>
                        <img src="../images/pars.png" class="logo-print" alt="Logo">
                    </div>

                    <div class="card-header p-0 no-print">
                        <button class="btn btn-course collapsed" data-toggle="collapse" data-target="#collapse<?= $i ?>">
                            <div>
                                <span class="h5 mb-0 font-weight-bold text-primary"><?= $content['details']['code'] ?> — <?= $content['details']['title'] ?></span>
                                <span class="badge badge-secondary ml-2">Sec <?= $content['details']['section'] ?></span>
                            </div>
                            <i class="fas fa-chevron-down text-muted"></i>
                        </button>
                    </div>

                    <div id="collapse<?= $i ?>" class="collapse" data-parent="#courseAccordion">
                        <div class="card-body">
                            
                            <div class="row mb-3 no-print align-items-center">
                                <div class="col-md-6 search-container">
                                    <i class="fas fa-search" style="position:absolute; left:25px; top:10px; color:#aaa;"></i>
                                    <input type="text" class="form-control pl-5" placeholder="Search student..." onkeyup="filterStudents(this, <?= $i ?>)">
                                </div>
                                <div class="col-md-6 text-right">
                                    <button type="button" class="btn btn-dark btn-sm" onclick="printSection(<?= $i ?>)">
                                        <i class="fas fa-print mr-1"></i> Print This Section
                                    </button>
                                </div>
                            </div>

                            <form action="take_attendance.php" method="POST">
                                <input type="hidden" name="course_id" value="<?= $content['details']['id'] ?>">
                                <input type="hidden" name="section_id" value="<?= $content['details']['sec_id'] ?>">
                                <input type="hidden" name="attendance_date" value="<?= $view_date ?>">

                                <table class="table table-sm table-bordered student-table-<?= $i ?>">
                                    <thead class="bg-light">
                                        <tr>
                                            <th>AG No.</th>
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
                                                    <!-- Live Status text for printing -->
                                                    <span class="print-status-text" id="status-text-<?= $s['student_id'] ?>">Unmarked</span>
                                                    
                                                    <div class="btn-group btn-group-toggle no-print" data-toggle="buttons">
                                                        <label class="btn btn-sm btn-outline-success <?= ($s['status'] == 'Present') ? 'active' : '' ?>">
                                                            <input type="radio" name="status[<?= $s['student_id'] ?>]" value="Present" <?= ($s['status'] == 'Present') ? 'checked' : '' ?>> P
                                                        </label>
                                                        <label class="btn btn-sm btn-outline-danger <?= ($s['status'] == 'Absent') ? 'active' : '' ?>">
                                                            <input type="radio" name="status[<?= $s['student_id'] ?>]" value="Absent" <?= ($s['status'] == 'Absent') ? 'checked' : '' ?>> A
                                                        </label>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                                
                                <div class="mt-4 no-print text-right">
                                    <button type="submit" class="btn btn-primary px-5 shadow-sm">Save to Database</button>
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
    const card = document.getElementById('card-' + index);
    
    // 1. Update the status text based on current radio selections
    const rows = card.querySelectorAll('tbody tr');
    rows.forEach(row => {
        const radios = row.querySelectorAll('input[type="radio"]');
        const statusSpan = row.querySelector('.print-status-text');
        let selectedValue = "Unmarked";
        
        radios.forEach(radio => {
            if (radio.checked) {
                selectedValue = radio.value;
            }
        });
        
        statusSpan.innerText = selectedValue;
        
        // Color coding for print status
        if(selectedValue === "Present") statusSpan.style.color = "green";
        else if(selectedValue === "Absent") statusSpan.style.color = "red";
        else statusSpan.style.color = "black";
    });

    // 2. Add 'printing' class to the specific card so CSS can hide others
    card.classList.add('printing');
    
    // 3. Trigger Print
    window.print();
    
    // 4. Cleanup
    card.classList.remove('printing');
}

function filterStudents(input, index) {
    let filter = input.value.toUpperCase();
    let table = document.querySelector('.student-table-' + index);
    let tr = table.getElementsByTagName('tr');

    for (let i = 1; i < tr.length; i++) {
        let txtValue = tr[i].textContent || tr[i].innerText;
        tr[i].style.display = txtValue.toUpperCase().indexOf(filter) > -1 ? "" : "none";
    }
}
</script>
</body>
</html>