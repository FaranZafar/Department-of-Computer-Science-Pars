<?php
session_start();
include_once("../../dbconnection.php");

if (!isset($_SESSION['user_id'])) { header("Location: ../login.php"); exit(); }

// --- 1. DELETE LOGIC ---
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $con->query("DELETE FROM assigned_teacher WHERE subjectassigned_id = $id");
    header("Location: assigned_teacher_room.php");
    exit();
}

// --- 2. AJAX HANDLER ---
if (isset($_POST['ajax_action'])) {
    if (ob_get_length()) ob_clean(); 
    if ($_POST['ajax_action'] == 'get_semesters') {
        $degree_id = intval($_POST['degree_id']);
        $query = $con->query("SELECT * FROM semester WHERE degree_id = $degree_id");
        echo '<option value="">-- Select Semester --</option>';
        while ($row = $query->fetch_assoc()) {
            echo '<option value="'.$row['semester_id'].'">'.$row['semester_name'].'</option>';
        }
        exit; 
    }
    if ($_POST['ajax_action'] == 'get_details') {
        $sem_id = intval($_POST['semester_id']);
        $course_query = $con->query("SELECT course_id, course_title FROM courses WHERE semester_id = $sem_id");
        $section_query = $con->query("SELECT section_id, section_name FROM sections WHERE semester_id = $sem_id");
        echo json_encode([
            'courses' => $course_query->fetch_all(MYSQLI_ASSOC),
            'sections' => $section_query->fetch_all(MYSQLI_ASSOC)
        ]);
        exit;
    }
}

// --- 3. SEARCH & FILTER LOGIC ---
$search_degree = isset($_GET['filter_degree']) ? intval($_GET['filter_degree']) : null;
$search_semester = isset($_GET['filter_semester']) ? intval($_GET['filter_semester']) : null;

$where_clauses = [];
if ($search_degree) $where_clauses[] = "at.degree_id = $search_degree";
if ($search_semester) $where_clauses[] = "at.semester_id = $search_semester";
$where_sql = (count($where_clauses) > 0) ? " WHERE " . implode(" AND ", $where_clauses) : "";

// --- 4. DATA FETCHING ---
$teachers = $con->query("SELECT StaffID, FirstName, LastName FROM staff WHERE UserRole IN ('Teacher', 'Coordinator')")->fetch_all(MYSQLI_ASSOC);
$degrees  = $con->query("SELECT * FROM degree")->fetch_all(MYSQLI_ASSOC);
$rooms    = $con->query("SELECT * FROM room")->fetch_all(MYSQLI_ASSOC);

$total_assignments = $con->query("SELECT COUNT(*) as total FROM assigned_teacher")->fetch_assoc()['total'];
$total_teachers = $con->query("SELECT COUNT(DISTINCT StaffID) as total FROM assigned_teacher")->fetch_assoc()['total'];

$assignments = $con->query("SELECT at.*, s.FirstName, s.LastName, c.course_title, sec.section_name, r.room_name, d.degree_name, sem.semester_name
    FROM assigned_teacher at 
    JOIN staff s ON at.StaffID = s.StaffID 
    JOIN degree d ON at.degree_id = d.degree_id
    JOIN semester sem ON at.semester_id = sem.semester_id
    JOIN courses c ON at.course_id = c.course_id 
    JOIN sections sec ON at.section_id = sec.section_id 
    LEFT JOIN room r ON at.room_id = r.room_id 
    $where_sql
    ORDER BY at.subjectassigned_id DESC")->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Teacher Assignments</title>
    <link rel="icon" type="image/png" href="../../images/pars.png">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        .stat-card { border-radius: 12px; border: none; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        .card { border-radius: 10px; border: none; box-shadow: 0 4px 6px rgba(0,0,0,0.1); margin-bottom: 25px; }
        .form-section { border-left: 5px solid #28a745; }
        .table thead th { background: #343a40; color: white; vertical-align: middle; border: none; }
        .header-filter { background: rgba(255,255,255,0.1); color: white; border: 1px solid rgba(255,255,255,0.3); font-weight: bold; }
        .header-filter option { color: black; }
        .btn-action { padding: 2px 8px; font-size: 0.85rem; }
    </style>
</head>
<body class="bg-light">

<div class="container-fluid py-4 px-4">
    
    <!-- 1. STATS -->
    <div class="row mb-4">
        <div class="col-md-3"><div class="card stat-card bg-primary text-white"><div class="card-body py-3 d-flex align-items-center"><i class="fas fa-database fa-2x mr-3"></i><div><small class="d-block">Total Records</small><strong><?= $total_assignments ?></strong></div></div></div></div>
        <div class="col-md-3"><div class="card stat-card bg-info text-white"><div class="card-body py-3 d-flex align-items-center"><i class="fas fa-user-tie fa-2x mr-3"></i><div><small class="d-block">Active Instructors</small><strong><?= $total_teachers ?></strong></div></div></div></div>
    </div>

    <!-- 2. ADD FORM -->
    <div class="card form-section">
        <div class="card-header bg-white text-success font-weight-bold"><i class="fas fa-plus-circle mr-2"></i> Add New Assignment</div>
        <div class="card-body">
            <form method="POST" action="save_logic.php">
                <div class="row">
                    <div class="col-md-3 form-group">
                        <label class="small font-weight-bold">Teacher</label>
                        <select name="staff_id" class="form-control form-control-sm" required>
                            <option value="">Select Teacher</option>
                            <?php foreach($teachers as $t): ?>
                                <option value="<?= $t['StaffID'] ?>"><?= $t['FirstName'] ?> <?= $t['LastName'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3 form-group">
                        <label class="small font-weight-bold">Degree</label>
                        <select name="degree_id" id="degree_select" class="form-control form-control-sm" required>
                            <option value="">Select Degree</option>
                            <?php foreach($degrees as $d): ?>
                                <option value="<?= $d['degree_id'] ?>"><?= $d['degree_name'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2 form-group">
                        <label class="small font-weight-bold">Semester</label>
                        <select name="semester_id" id="semester_select" class="form-control form-control-sm" required disabled><option value="">--</option></select>
                    </div>
                    <div class="col-md-2 form-group">
                        <label class="small font-weight-bold">Course</label>
                        <select name="course_id" id="course_select" class="form-control form-control-sm" required disabled><option value="">--</option></select>
                    </div>
                    <div class="col-md-2 form-group">
                        <label class="small font-weight-bold">Section</label>
                        <select name="section_id" id="section_select" class="form-control form-control-sm" required disabled><option value="">--</option></select>
                    </div>
                </div>
                <div class="row align-items-end">
                    <div class="col-md-2 form-group"><label class="small font-weight-bold">Day</label><select name="day" class="form-control form-control-sm"><?php foreach(['Monday','Tuesday','Wednesday','Thursday','Friday'] as $day) echo "<option value='$day'>$day</option>"; ?></select></div>
                    <div class="col-md-2 form-group"><label class="small font-weight-bold">Start Time</label><input type="time" name="start_time" class="form-control form-control-sm" required></div>
                    <div class="col-md-2 form-group"><label class="small font-weight-bold">End Time</label><input type="time" name="end_time" class="form-control form-control-sm" required></div>
                    <div class="col-md-2 form-group">
                        <label class="small font-weight-bold">Room</label>
                        <select name="room_id" class="form-control form-control-sm">
                            <option value="">Room</option>
                            <?php foreach($rooms as $r): ?> <option value="<?= $r['room_id'] ?>"><?= $r['room_name'] ?></option> <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4 form-group">
                        <button type="submit" name="save_assignment" class="btn btn-success btn-sm btn-block font-weight-bold">SAVE TO DATABASE</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- 3. HISTORY TABLE -->
    <div class="card">
        <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
            <span>Assignment History</span>
            <?php if($search_degree || $search_semester): ?>
                <a href="assigned_teacher_room.php" class="badge badge-warning text-dark"><i class="fas fa-times"></i> Clear Filters</a>
            <?php endif; ?>
        </div>
        <div class="table-responsive">
            <form method="GET">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Instructor</th>
                            <th style="min-width: 180px;">
                                <select name="filter_degree" class="form-control form-control-sm header-filter" onchange="this.form.submit()">
                                    <option value="">Degree (All)</option>
                                    <?php foreach($degrees as $d): ?>
                                        <option value="<?= $d['degree_id'] ?>" <?= $search_degree == $d['degree_id'] ? 'selected' : '' ?>><?= $d['degree_name'] ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </th>
                            <th>
                                <select name="filter_semester" id="filter_semester" class="form-control form-control-sm header-filter" onchange="this.form.submit()">
                                    <option value="">Semester (All)</option>
                                </select>
                            </th>
                            <th>Course/Section</th>
                            <th>Schedule</th>
                            <th>Room</th>
                            <th class="text-center" style="width:120px;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(empty($assignments)): ?>
                            <tr><td colspan="7" class="text-center p-5 text-muted">No records found.</td></tr>
                        <?php else: ?>
                            <?php foreach($assignments as $a): ?>
                            <tr>
                                <td><strong><?= $a['FirstName'] ?> <?= $a['LastName'] ?></strong></td>
                                <td class="text-primary"><?= $a['degree_name'] ?></td>
                                <td><?= $a['semester_name'] ?></td>
                                <td><?= $a['course_title'] ?> <br><span class="badge badge-secondary"><?= $a['section_name'] ?></span></td>
                               <td>
                                <small class="font-weight-bold text-uppercase"><?= $a['day'] ?></small><br>
                                <small>
                               <!-- Displays both Start and End Time -->
                               <?= date("g:i A", strtotime($a['time_start'])) ?> - 
                               <?= date("g:i A", strtotime($a['time_end'])) ?>
                                </small>
                                 </td>
                                
                                <td><span class="text-dark font-weight-bold"><?= $a['room_name'] ?? 'TBA' ?></span></td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-sm btn-outline-info btn-action mr-1" onclick='editRow(<?= json_encode($a) ?>)'>
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <a href="?delete=<?= $a['subjectassigned_id'] ?>" class="btn btn-sm btn-outline-danger btn-action" onclick="return confirm('Delete this?')">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </form>
        </div>
    </div>
</div>

<!-- UPDATE MODAL -->
<div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <form action="update_logic.php" method="POST">
                <input type="hidden" name="assigned_id" id="edit_id">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title"><i class="fas fa-edit mr-2"></i> Update Full Assignment Details</h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-4 form-group">
                            <label class="font-weight-bold">Instructor</label>
                            <select name="staff_id" id="edit_staff" class="form-control" required>
                                <?php foreach($teachers as $t): ?>
                                    <option value="<?= $t['StaffID'] ?>"><?= $t['FirstName'] ?> <?= $t['LastName'] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4 form-group">
                            <label class="font-weight-bold">Degree</label>
                            <select name="degree_id" id="edit_degree" class="form-control" required>
                                <?php foreach($degrees as $d): ?>
                                    <option value="<?= $d['degree_id'] ?>"><?= $d['degree_name'] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4 form-group">
                            <label class="font-weight-bold">Semester</label>
                            <select name="semester_id" id="edit_semester" class="form-control" required></select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 form-group">
                            <label class="font-weight-bold">Course</label>
                            <select name="course_id" id="edit_course" class="form-control" required></select>
                        </div>
                        <div class="col-md-4 form-group">
                            <label class="font-weight-bold">Section</label>
                            <select name="section_id" id="edit_section" class="form-control" required></select>
                        </div>
                        <div class="col-md-4 form-group">
                            <label class="font-weight-bold">Room</label>
                            <select name="room_id" id="edit_room" class="form-control">
                                <option value="">TBA</option>
                                <?php foreach($rooms as $r): ?> <option value="<?= $r['room_id'] ?>"><?= $r['room_name'] ?></option> <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 form-group">
                            <label class="font-weight-bold">Day</label>
                            <select name="day" id="edit_day" class="form-control">
                                <?php foreach(['Monday','Tuesday','Wednesday','Thursday','Friday'] as $day) echo "<option value='$day'>$day</option>"; ?>
                            </select>
                        </div>
                        <div class="col-md-4 form-group">
                            <label class="font-weight-bold">Start Time</label>
                            <input type="time" name="start_time" id="edit_start" class="form-control" required>
                        </div>
                        <div class="col-md-4 form-group">
                            <label class="font-weight-bold">End Time</label>
                            <input type="time" name="end_time" id="edit_end" class="form-control" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" name="update_assignment" class="btn btn-info font-weight-bold">SAVE UPDATED RECORD</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
$(document).ready(function() {
    function fetchSems(degreeId, target, selected = null) {
        return $.post('assigned_teacher_room.php', {ajax_action: 'get_semesters', degree_id: degreeId}, function(data) {
            $(target).html(data).prop('disabled', false);
            if(selected) $(target).val(selected);
        });
    }

    function fetchDetails(semesterId, cTarget, sTarget, cSelected = null, sSelected = null) {
        return $.post('assigned_teacher_room.php', {ajax_action: 'get_details', semester_id: semesterId}, function(data) {
            var json = JSON.parse(data);
            var c_opt = '<option value="">Course</option>';
            json.courses.forEach(c => { c_opt += `<option value="${c.course_id}">${c.course_title}</option>`; });
            $(cTarget).html(c_opt).prop('disabled', false);
            if(cSelected) $(cTarget).val(cSelected);
            
            var s_opt = '<option value="">Section</option>';
            json.sections.forEach(s => { s_opt += `<option value="${s.section_id}">${s.section_name}</option>`; });
            $(sTarget).html(s_opt).prop('disabled', false);
            if(sSelected) $(sTarget).val(sSelected);
        });
    }

    $('#degree_select').change(function() { fetchSems($(this).val(), '#semester_select'); });
    $('#semester_select').change(function() { fetchDetails($(this).val(), '#course_select', '#section_select'); });
    $('#edit_degree').change(function() { fetchSems($(this).val(), '#edit_semester'); });
    $('#edit_semester').change(function() { fetchDetails($(this).val(), '#edit_course', '#edit_section'); });

window.editRow = function(data) {
    $('#edit_id').val(data.subjectassigned_id);
    $('#edit_staff').val(data.StaffID);
    $('#edit_degree').val(data.degree_id);
    $('#edit_day').val(data.day);
    $('#edit_start').val(data.time_start);
    $('#edit_end').val(data.time_end); // Ensure this line exists to populate the end time field
    $('#edit_room').val(data.room_id);

    // Chain the AJAX calls to set selected values in modal
    fetchSems(data.degree_id, '#edit_semester', data.semester_id).then(function() {
        fetchDetails(data.semester_id, '#edit_course', '#edit_section', data.course_id, data.section_id);
    });

    $('#editModal').modal('show');
};

    <?php if($search_degree): ?>
        fetchSems(<?= $search_degree ?>, '#filter_semester', <?= $search_semester ?? 'null' ?>);
    <?php endif; ?>
});
</script>
</body>
</html>