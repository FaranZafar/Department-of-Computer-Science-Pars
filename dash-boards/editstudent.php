<?php
include_once("../dbconnection.php");

$message = "";
$error = "";

// 1. FETCH EXISTING DATA
if (isset($_GET['id'])) {
    $student_id = (int)$_GET['id'];
    $stmt = $con->prepare("SELECT * FROM students WHERE student_id = ?");
    $stmt->bind_param("i", $student_id);
    $stmt->execute();
    $student = $stmt->get_result()->fetch_assoc();

    if (!$student) {
        header("Location: view_students.php?msg=NotFound");
        exit();
    }
} else {
    header("Location: view_students.php");
    exit();
}

// 2. HANDLE UPDATE LOGIC
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $full_name   = $_POST['full_name'];
    $ag_no       = $_POST['ag_no'];
    $email       = $_POST['email'];
    $password    = $_POST['password']; 
    $phone       = $_POST['phone_no'];
    $dob         = $_POST['dob'];
    $gender      = $_POST['gender'];
    $status      = $_POST['status'];
    $deg_id      = $_POST['degree_id'];
    $sem_id      = $_POST['semester_id'];
    $sec_id      = $_POST['section_id'];
    $enroll_date = $_POST['enroll_date'];
    $student_id  = $_GET['id']; // Ensure you have the student_id from the URL or a hidden field

    // 13 placeholders total (12 in SET, 1 in WHERE)
    $update_sql = "UPDATE students SET ag_no=?, section_id=?, full_name=?, email=?, password=?, phone_no=?, dob=?, gender=?, status=?, degree_id=?, semester_id=?, enrollment_date=? WHERE student_id=?";
    
    $update_stmt = $con->prepare($update_sql);
    
    /**
     * Correction of Type Definition String:
     * ag_no(s), section_id(i), full_name(s), email(s), password(s), phone_no(s), 
     * dob(s), gender(s), status(s), degree_id(i), semester_id(i), enrollment_date(s), student_id(i)
     * * String: "sisssssssiisi" (Total 13 characters)
     */
    $update_stmt->bind_param("sisssssssiisi", 
        $ag_no,       // 1
        $sec_id,      // 2
        $full_name,   // 3
        $email,       // 4
        $password,    // 5
        $phone,       // 6
        $dob,         // 7
        $gender,      // 8
        $status,      // 9
        $deg_id,      // 10
        $sem_id,      // 11
        $enroll_date, // 12
        $student_id   // 13 (Must be last because it's for the WHERE clause)
    );

    if ($update_stmt->execute()) {
        header("Location: view_students.php?msg=Updated");
        exit();
    } else {
        $error = "Update failed: " . $con->error;
    }
}

// 3. FETCH DATA FOR DYNAMIC DROPDOWNS
$degrees = $con->query("SELECT * FROM degree")->fetch_all(MYSQLI_ASSOC);
$semesters_raw = $con->query("SELECT * FROM semester")->fetch_all(MYSQLI_ASSOC);
$sections_raw = $con->query("SELECT * FROM sections")->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Student Information</title>
    <link rel="icon" type="image/png" href="../images/pars.png">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        body { background-color: #f4f7f6; padding-top: 50px; padding-bottom: 50px; }
        .card { border: none; border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); }
        label { font-weight: 600; color: #555; }
        .form-control:focus { border-color: #4a90e2; box-shadow: none; }
    </style>
</head>
<body>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            
            <?php if($error): ?>
                <div class="alert alert-danger shadow-sm"><?php echo $error; ?></div>
            <?php endif; ?>

            <div class="card">
                <div class="card-header bg-primary py-3 d-flex justify-content-between align-items-center text-white">
                    <h4 class="mb-0"><i class="fas fa-user-edit mr-2"></i> Edit Student Information</h4>
                    <a href="view_students.php" class="btn btn-sm btn-light text-primary font-weight-bold">
                        <i class="fas fa-arrow-left"></i> Back to List
                    </a>
                </div>
                <div class="card-body p-4">
                    <form method="POST">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Full Name</label>
                                    <input type="text" name="full_name" class="form-control" value="<?php echo htmlspecialchars($student['full_name']); ?>" required>
                                </div>
                                <div class="form-group">
                                    <label>Phone Number</label>
                                    <input type="text" name="phone_no" class="form-control" value="<?php echo htmlspecialchars($student['phone_no']); ?>">
                                </div>
                                <div class="form-group">
                                    <label>Email Address</label>
                                    <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($student['email']); ?>" required>
                                </div>
                                 <div class="form-group">
                                    <label>Enrollment Date</label>
                                    <input type="date" name="enroll_date" class="form-control" value="<?php echo $student['enrollment_date']; ?>" required>
                                </div>
                                <div class="form-group">
                                    <label>AG Registration No</label>
                                    <input type="text" name="ag_no" class="form-control" value="<?php echo htmlspecialchars($student['ag_no']); ?>" required>
                                </div>
                                <hr>
                                <div class="form-group">
                                    <label class="text-primary">2. Semester</label>
                                    <select name="semester_id" id="semester_select" class="form-control" required disabled>
                                        <option value="">-- Waiting for Degree --</option>
                                    </select>
                                </div>
                                 
            
                               
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Date of Birth</label>
                                    <input type="date" name="dob" class="form-control" value="<?php echo $student['dob']; ?>" required>
                                </div>
                                <div class="form-group">
                                    <label>Gender</label>
                                    <select name="gender" class="form-control">
                                        <option value="Male" <?php echo ($student['gender'] == 'Male') ? 'selected' : ''; ?>>Male</option>
                                        <option value="Female" <?php echo ($student['gender'] == 'Female') ? 'selected' : ''; ?>>Female</option>
                                    </select>
                                </div>
                                 <div class="form-group">
                                    <label>Password (Visible)</label>
                                    <input type="text" name="password" class="form-control" value="<?php echo htmlspecialchars($student['password']); ?>" required>
                                </div>
                                <div class="form-group">
                                    <label>Status</label>
                                    <select name="status" class="form-control">
                                        <?php 
                                        foreach(['Active', 'Graduated', 'Freezed', 'Inactive'] as $opt) {
                                            $sel = ($student['status'] == $opt) ? 'selected' : '';
                                            echo "<option value='$opt' $sel>$opt</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                                 <div class="form-group">
                                    <label class="text-primary">1. Degree / Program</label>
                                    <select name="degree_id" id="degree_select" class="form-control" required onchange="filterData()">
                                        <option value="">-- Select Degree --</option>
                                        <?php foreach($degrees as $d): ?>
                                            <option value="<?php echo $d['degree_id']; ?>" <?php echo ($d['degree_id'] == $student['degree_id']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($d['degree_name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <hr>
                                <div class="form-group">
                                    <label class="text-primary">3. Assigned Section</label>
                                    <select name="section_id" id="section_select" class="form-control" required disabled>
                                        <option value="">-- Waiting for Degree --</option>
                                    </select>
                                </div>
                                
                            </div>
                        </div>
                        <hr>
                        <div class="text-right">
                            <button type="submit" class="btn btn-primary btn-lg px-5 shadow-sm">
                                <i class="fas fa-save mr-2"></i> Update Student Record
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// JSON encoded data from PHP
const allSemesters = <?php echo json_encode($semesters_raw); ?>;
const allSections = <?php echo json_encode($sections_raw); ?>;

// Student's currently saved values
const currentSem = "<?php echo $student['semester_id']; ?>";
const currentSec = "<?php echo $student['section_id']; ?>";

function filterData() {
    const degreeId = document.getElementById('degree_select').value;
    const semSelect = document.getElementById('semester_select');
    const secSelect = document.getElementById('section_select');

    // Reset Dropdowns
    semSelect.innerHTML = '';
    secSelect.innerHTML = '';

    if (degreeId === "") {
        semSelect.disabled = true;
        secSelect.disabled = true;
        semSelect.innerHTML = '<option value="">-- Waiting for Degree --</option>';
        secSelect.innerHTML = '<option value="">-- Waiting for Degree --</option>';
        return;
    }

    semSelect.disabled = false;
    secSelect.disabled = false;

    // Filter and Append Semesters
    const semFiltered = allSemesters.filter(s => s.degree_id == degreeId);
    if(semFiltered.length > 0) {
        semSelect.innerHTML = '<option value="">-- Select Semester --</option>';
        semFiltered.forEach(s => {
            const opt = document.createElement('option');
            opt.value = s.semester_id;
            opt.innerHTML = s.semester_name;
            if(s.semester_id == currentSem) opt.selected = true;
            semSelect.appendChild(opt);
        });
    }

    // Filter and Append Sections
    const secFiltered = allSections.filter(sec => sec.degree_id == degreeId);
    if(secFiltered.length > 0) {
        secSelect.innerHTML = '<option value="">-- Select Section --</option>';
        secFiltered.forEach(sec => {
            const opt = document.createElement('option');
            opt.value = sec.section_id;
            opt.innerHTML = sec.section_name;
            if(sec.section_id == currentSec) opt.selected = true;
            secSelect.appendChild(opt);
        });
    }
}

// Trigger filter on page load to handle the existing student record
window.onload = filterData;
</script>

<script src="https://cdn.jsdelivr.net/npm/jquery@3.5.1/dist/jquery.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>