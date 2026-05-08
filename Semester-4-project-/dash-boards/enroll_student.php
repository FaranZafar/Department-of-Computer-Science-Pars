<?php
include_once("../dbconnection.php");

$message = $_GET['msg'] ?? null;
$error = null;

// HANDLE REGISTRATION
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $full_name   = $_POST['full_name'];
    $ag_no       = $_POST['ag_no'];
    $email       = $_POST['email'];
    $password    = $_POST['password']; 
    $phone       = $_POST['phone_no'];
    $dob         = $_POST['dob'];
    $gender      = $_POST['gender'];
    $enroll_date = $_POST['enrollment_date'];
    $status      = $_POST['status'];
    $deg_id      = $_POST['degree_id'];
    $sem_id      = $_POST['semester_id'];
    $sec_id      = $_POST['section_id']; 

    $sql = "INSERT INTO students (ag_no, section_id, full_name, email, password, phone_no, dob, gender, enrollment_date, status, degree_id, semester_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $con->prepare($sql);
    $stmt->bind_param("sissssssssii", $ag_no, $sec_id, $full_name, $email, $password, $phone, $dob, $gender, $enroll_date, $status, $deg_id, $sem_id);

    if ($stmt->execute()) {
        header("Location: " . $_SERVER['PHP_SELF'] . "?msg=" . urlencode("Student registered successfully!"));
        exit();
    } else { 
        $error = "Error: " . $con->error; 
    }
}

// Initial fetch for Degrees
$degrees = $con->query("SELECT * FROM degree ORDER BY degree_name ASC")->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Registration | Pars Campus</title>
    <link rel="icon" type="image/png" href="../images/pars.png">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        body { background-color: #f4f7f6; padding: 50px 0; }
        .card { border: none; border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); }
        .card-header { color: white; border-radius: 15px 15px 0 0; }
        .form-control:focus { border-color: #28a745; box-shadow: none; }
        label { font-weight: 600; color: #555; font-size: 0.9rem; }
    </style>
</head>
<body>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            
            <?php if($message): ?>
                <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
                    <i class="fas fa-check-circle mr-2"></i> <?php echo htmlspecialchars($message); ?>
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                </div>
            <?php endif; ?>

            <?php if($error): ?>
                <div class="alert alert-danger shadow-sm"><?php echo $error; ?></div>
            <?php endif; ?>

            <div class="card">
                <div class="card-header bg-primary py-3 d-flex justify-content-between align-items-center">
                    <h4 class="mb-0"><i class="fas fa-user-plus mr-2"></i> Student Registration</h4>
                    <a href="view_students.php" class="btn btn-sm btn-light text-primary font-weight-bold">
                        <i class="fas fa-list mr-1"></i> View Student List
                    </a>
                </div>
                
                <div class="card-body p-4">
                    <form method="POST" id="registrationForm">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Full Name</label>
                                    <input type="text" name="full_name" class="form-control" placeholder="Enter Full Name" required>
                                </div>
                                 <div class="form-group">
                                    <label>Gender</label>
                                    <select name="gender" class="form-control">
                                        <option value="Male">Male</option>
                                        <option value="Female">Female</option>
                                    </select>
                                </div>
                                 <div class="form-group">
                                    <label>Password</label>
                                    <input type="password" name="password" class="form-control" placeholder="Set Password" required>
                                </div>

                                <div class="form-group">
                                    <label>AG Registration No</label>
                                    <input type="text" name="ag_no" class="form-control" placeholder="e.g. 2024-AG-123" required>
                                </div>
                               <div class="form-group">
                                    <label>Enrollment Date</label>
                                    <input type="date" name="enrollment_date" class="form-control" value="<?php echo date('Y-m-d'); ?>">
                                </div>
                                <div class="form-group">
                                    <label>Semester</label>
                                    <select name="semester_id" id="semester_id" class="form-control" required>
                                        <option value="">-- First Select Degree --</option>
                                    </select>
                                </div>
                            </div>
                 <!-- 2nd column -->
                            <div class="col-md-6">
                                 <div class="form-group">
                                    <label>Date of Birth</label>
                                    <input type="date" name="dob" class="form-control" required>
                                </div>
                                 <div class="form-group">
                                    <label>Email Address</label>
                                    <input type="email" name="email" class="form-control" placeholder="example@mail.com" required>
                                </div>
                                 <div class="form-group">
                                    <label>Phone Number</label>
                                    <input type="text" name="phone_no" class="form-control" placeholder="03xx-xxxxxxx">
                                </div>
                                
                                <div class="form-group">
                                    <label>Status</label>
                                    <select name="status" class="form-control">
                                        <option value="Active">Active</option>
                                        <option value="Inactive">Inactive</option>
                                        <option value="Freezed">Freezed</option>
                                    </select>
                                </div>
                                
                                <div class="form-group">
                                    <label>Degree / Program</label>
                                    <select name="degree_id" id="degree_id" class="form-control" required>
                                        <option value="">-- Select Degree --</option>
                                        <?php foreach($degrees as $d): ?>
                                            <option value="<?php echo $d['degree_id']; ?>"><?php echo $d['degree_name']; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                
                                <div class="form-group">
                                    <label>Section</label>
                                    <select name="section_id" id="section_id" class="form-control" required>
                                        <option value="">-- First Select Semester --</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <hr>
                        <div class="text-right">
                            <button type="submit" class="btn btn-success btn-lg px-5 shadow-sm">
                                <i class="fas fa-check-circle mr-2"></i> Register Student
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
$(document).ready(function() {
    $('#degree_id').on('change', function() {
        var degreeID = $(this).val();
        $('#semester_id').html('<option value="">Loading...</option>');
        $('#section_id').html('<option value="">-- First Select Semester --</option>');

        if(degreeID) {
            $.ajax({
                url: 'fetch_details.php',
                type: 'POST',
                data: {degree_id: degreeID},
                dataType: 'json',
                success: function(data) {
                    $('#semester_id').empty().append('<option value="">-- Select Semester --</option>');
                    $.each(data, function(key, value) {
                        $('#semester_id').append('<option value="'+ value.semester_id +'">'+ value.semester_name +'</option>');
                    });
                }
            });
        } else {
            $('#semester_id').html('<option value="">-- First Select Degree --</option>');
        }
    });

    $('#semester_id').on('change', function() {
        var semesterID = $(this).val();
        if(semesterID) {
            $('#section_id').html('<option value="">Loading...</option>');
            $.ajax({
                url: 'fetch_details.php',
                type: 'POST',
                data: {semester_id: semesterID},
                dataType: 'json',
                success: function(data) {
                    $('#section_id').empty().append('<option value="">-- Select Section --</option>');
                    $.each(data, function(key, value) {
                        $('#section_id').append('<option value="'+ value.section_id +'">'+ value.section_name +'</option>');
                    });
                }
            });
        } else {
            $('#section_id').html('<option value="">-- First Select Semester --</option>');
        }
    });
});
</script>
</body>
</html>