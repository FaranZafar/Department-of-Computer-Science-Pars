<?php
session_start();
include_once("../../dbconnection.php");

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'Coordinator') {
    header("Location: ../login.php");
    exit();
}

// --- DYNAMIC CRUD LOGIC ---
if (isset($_POST['save_assignment'])) {
    $course_id = $_POST['course_id'];
    $staff_id = $_POST['staff_id'];
    $section_name = $_POST['section_name'];
    $semester_id = $_POST['semester_id'];

    // CHECK FOR DUPLICATION FIRST
    $check_duplicate = mysqli_query($con, "SELECT section_id FROM sections 
        WHERE course_id = '$course_id' 
        AND section_name = '$section_name' 
        AND semester_id = '$semester_id'");

    if (mysqli_num_rows($check_duplicate) > 0) {
        // Redirect with an error if it already exists
        header("Location: coordinator.php?error=duplicate");
        exit();
    } else {
        // Proceed with insert if no duplicate is found
        $query = "INSERT INTO sections (course_id, staff_id, section_name, semester_id) 
                  VALUES ('$course_id', '$staff_id', '$section_name', '$semester_id')";
        mysqli_query($con, $query);
        header("Location: coordinator.php?success=1");
    }
}

// --- DATA FETCHING ---
$courses = mysqli_query($con, "SELECT * FROM courses");
$staff_list = mysqli_query($con, "SELECT * FROM staff WHERE UserRole = 'Teacher'");
$semesters = mysqli_query($con, "SELECT * FROM semester");
$students_list = mysqli_query($con, "SELECT * FROM students");

// Assigned Subjects Query
$assign_query = "SELECT sec.*, c.course_title, st.FirstName, st.LastName, sem.semester_name 
                 FROM sections sec
                 JOIN courses c ON sec.course_id = c.course_id
                 JOIN staff st ON sec.staff_id = st.StaffID
                 JOIN semester sem ON sec.semester_id = sem.semester_id";
$assignments = mysqli_query($con, $assign_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Coordinator Console | Pars Campus</title>
    <link rel="icon" type="image/png" href="../../images/pars.png">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body class="bg-light">

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="font-weight-bold">Coordinator Dashboard</h3>
        <div>
            <button class="btn btn-primary" data-toggle="modal" data-target="#assignModal">
                <i class="fas fa-plus mr-1"></i> Assign Subject
            </button>
            <button class="btn btn-success" data-toggle="modal" data-target="#enrollModal">
                <i class="fas fa-user-plus mr-1"></i> New Enrollment
            </button>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white font-weight-bold">Active Subject Assignments</div>
                <div class="card-body p-0">
                    <table class="table table-hover mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th>Subject</th>
                                <th>Teacher</th>
                                <th>Section</th>
                                <th>Semester</th>
                                <th class="text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($row = mysqli_fetch_assoc($assignments)): ?>
                            <tr>
                                <td><?php echo $row['course_title']; ?></td>
                                <td><?php echo $row['FirstName'] . " " . $row['LastName']; ?></td>
                                <td><span class="badge badge-info"><?php echo $row['section_name']; ?></span></td>
                                <td><?php echo $row['semester_name']; ?></td>
                                <td class="text-right">
                                    <form method="POST" style="display:inline;">
                                        <input type="hidden" name="section_id" value="<?php echo $row['section_id']; ?>">
                                        <input type="hidden" name="action" value="delete_assignment">
                                        <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this assignment?')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="assignModal" tabindex="-1">
    <div class="modal-dialog">
        <form class="modal-content" method="POST">
            <div class="modal-header"><h5>Assign New Subject</h5></div>
            <div class="modal-body">
                <input type="hidden" name="action" value="assign_subject">
                <div class="form-group">
                    <label>Select Course</label>
                    <select name="course_id" class="form-control" required>
                        <?php while($c = mysqli_fetch_assoc($courses)) echo "<option value='{$c['course_id']}'>{$c['course_title']}</option>"; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Select Teacher</label>
                    <select name="staff_id" class="form-control" required>
                        <?php mysqli_data_seek($staff_list, 0); while($s = mysqli_fetch_assoc($staff_list)) echo "<option value='{$s['StaffID']}'>{$s['FirstName']} {$s['LastName']}</option>"; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Section Name (e.g. 4th A)</label>
                    <input type="text" name="section_name" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Semester</label>
                    <select name="semester_id" class="form-control" required>
                        <?php while($sem = mysqli_fetch_assoc($semesters)) echo "<option value='{$sem['semester_id']}'>{$sem['semester_name']}</option>"; ?>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="submit" class="btn btn-primary"><a href="save_assignment.php">Save</a></button>
            </div>
        </form>
    </div>
</div>

<div class="modal fade" id="enrollModal" tabindex="-1">
    <div class="modal-dialog">
        <form class="modal-content" method="POST">
            <div class="modal-header"><h5>Enroll Student in Section</h5></div>
            <div class="modal-body">
                <input type="hidden" name="action" value="enroll_student">
                <div class="form-group">
                    <label>Select Student</label>
                    <select name="student_id" class="form-control" required>
                        <?php while($st = mysqli_fetch_assoc($students_list)) echo "<option value='{$st['student_id']}'>{$st['ag_no']} - {$st['full_name']}</option>"; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Select Active Section</label>
                    <select name="section_id" class="form-control" required>
                        <?php mysqli_data_seek($assignments, 0); while($as = mysqli_fetch_assoc($assignments)) echo "<option value='{$as['section_id']}'>{$as['course_title']} ({$as['section_name']})</option>"; ?>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="submit" class="btn btn-success">Enroll Now</button>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/jquery@3.5.1/dist/jquery.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>