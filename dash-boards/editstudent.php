<?php
include_once("../dbconnection.php");

$message = "";
$error = "";

// 1. FETCH EXISTING DATA
if (isset($_GET['id'])) {
    $student_id = $_GET['id'];
    $stmt = $con->prepare("SELECT * FROM students WHERE student_id = ?");
    $stmt->bind_param("i", $student_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $student = $result->fetch_assoc();

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
    $password    = $_POST['password']; // Stores exactly what you enter
    $phone       = $_POST['phone_no'];
    $dob         = $_POST['dob'];
    $gender      = $_POST['gender'];
    $enroll_date = $_POST['enrollment_date'];
    $status      = $_POST['status'];
    $deg_id      = $_POST['degree_id'];
    $sem_id      = $_POST['semester_id'];
    $sec_id      = $_POST['section_id'];

    $update_sql = "UPDATE students SET ag_no=?, section_id=?, full_name=?, email=?, password=?, phone_no=?, dob=?, gender=?, enrollment_date=?, status=?, degree_id=?, semester_id=? WHERE student_id=?";
    $update_stmt = $con->prepare($update_sql);
    
    // sisssssssssii -> Type definition for parameters
    $update_stmt->bind_param("sisssssssssii", $ag_no, $sec_id, $full_name, $email, $password, $phone, $dob, $gender, $enroll_date, $status, $deg_id, $sem_id, $student_id);

    if ($update_stmt->execute()) {
        header("Location: view_students.php?msg=Updated");
        exit();
    } else {
        $error = "Update failed: " . $con->error;
    }
}

// 3. FETCH DROPDOWNS
$degrees = $con->query("SELECT * FROM degree")->fetch_all(MYSQLI_ASSOC);
$semesters = $con->query("SELECT * FROM semester")->fetch_all(MYSQLI_ASSOC);
$sections = $con->query("SELECT * FROM sections")->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Student</title>
    <link rel="icon" type="image/png" href="../images/pars.png">
    <!-- Bootstrap 4 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        body { background-color: #f4f7f6; padding-top: 50px; padding-bottom: 50px; }
        .card { border: none; border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); }
        .card-header {  color: white; border-radius: 15px 15px 0 0 ! vegetable; }
        .form-control:focus { border-color: #4a90e2; box-shadow: none; }
        label { font-weight: 600; color: #555; }
    </style>
</head>
<body>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            
            <?php if($error): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>

            <div class="card">
                <div class="card-header bg-primary py-3 d-flex justify-content-between align-items-center">
                    <h4 class="mb-0"><i class="fas fa-user-edit mr-2"></i> Edit Student Information</h4>
                    <a href="view_students.php" class="btn btn-sm btn-light text-primary font-weight-bold">
                        <i class="fas fa-arrow-left"></i> Back to List
                    </a>
                </div>
                <div class="card-body p-4">
                    <form method="POST">
                        <div class="row">
                            <!-- Column 1 -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Full Name</label>
                                    <input type="text" name="full_name" class="form-control" value="<?php echo $student['full_name']; ?>" required>
                                </div>
                                <div class="form-group">
                                    <label>AG Registration No</label>
                                    <input type="text" name="ag_no" class="form-control" value="<?php echo $student['ag_no']; ?>" required>
                                </div>
                                <div class="form-group">
                                    <label>Email Address</label>
                                    <input type="email" name="email" class="form-control" value="<?php echo $student['email']; ?>" required>
                                </div>
                                <div class="form-group">
                                    <label>Password (Visible)</label>
                                    <input type="text" name="password" class="form-control" value="<?php echo $student['password']; ?>" required>
                                </div>
                                <div class="form-group">
                                    <label>Phone Number</label>
                                    <input type="text" name="phone_no" class="form-control" value="<?php echo $student['phone_no']; ?>">
                                </div>
                                <div class="form-group">
                                    <label>Section</label>
                                    <select name="section_id" class="form-control" required>
                                        <?php foreach($sections as $sec): ?>
                                            <option value="<?php echo $sec['section_id']; ?>" <?php if($sec['section_id'] == $student['section_id']) echo 'selected'; ?>>
                                                <?php echo $sec['section_name']; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>

                            <!-- Column 2 -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Date of Birth</label>
                                    <input type="date" name="dob" class="form-control" value="<?php echo $student['dob']; ?>" required>
                                </div>
                                <div class="form-group">
                                    <label>Gender</label>
                                    <select name="gender" class="form-control">
                                        <option value="Male" <?php if($student['gender'] == 'Male') echo 'selected'; ?>>Male</option>
                                        <option value="Female" <?php if($student['gender'] == 'Female') echo 'selected'; ?>>Female</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Enrollment Date</label>
                                    <input type="date" name="enrollment_date" class="form-control" value="<?php echo $student['enrollment_date']; ?>">
                                </div>
                                <div class="form-group">
                                    <label>Status</label>
                                    <select name="status" class="form-control">
                                        <?php 
                                        $opts = ['Active', 'Graduated', 'Freezed', 'Inactive'];
                                        foreach($opts as $opt) {
                                            $sel = ($student['status'] == $opt) ? 'selected' : '';
                                            echo "<option value='$opt' $sel>$opt</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Degree / Program</label>
                                    <select name="degree_id" class="form-control" required>
                                        <?php foreach($degrees as $d): ?>
                                            <option value="<?php echo $d['degree_id']; ?>" <?php if($d['degree_id'] == $student['degree_id']) echo 'selected'; ?>>
                                                <?php echo $d['degree_name']; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Semester</label>
                                    <select name="semester_id" class="form-control" required>
                                        <?php foreach($semesters as $sem): ?>
                                            <option value="<?php echo $sem['semester_id']; ?>" <?php if($sem['semester_id'] == $student['semester_id']) echo 'selected'; ?>>
                                                <?php echo $sem['semester_name']; ?>
                                            </option>
                                        <?php endforeach; ?>
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

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/jquery@3.5.1/dist/jquery.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>