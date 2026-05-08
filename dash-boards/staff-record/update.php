<?php
include_once("../../dbconnection.php");

$message = "";

// 1. GET THE ID 
if (isset($_POST['staff_id'])) {
    $id = mysqli_real_escape_string($con, $_POST['staff_id']);
} elseif (isset($_GET['id'])) {
    $id = mysqli_real_escape_string($con, $_GET['id']);
} else {
    $id = "";
}

if (empty($id)) {
    header("Location: pteacher-record.php");
    exit();
}

// 2. HANDLE THE UPDATE SUBMISSION
if (isset($_POST['update_teacher'])) {
    $firstname   = mysqli_real_escape_string($con, $_POST['firstname']);
    $lastname    = mysqli_real_escape_string($con, $_POST['lastname']);
    $email       = mysqli_real_escape_string($con, $_POST['email']);
    $password    = mysqli_real_escape_string($con, $_POST['password']);
    $phoneNo     = mysqli_real_escape_string($con, $_POST['phoneNo']);
    $role        = mysqli_real_escape_string($con, $_POST['role']);
    $designation = mysqli_real_escape_string($con, $_POST['designation']);
    $department  = mysqli_real_escape_string($con, $_POST['department']);
    $status      = mysqli_real_escape_string($con, $_POST['status']);
    $empType     = mysqli_real_escape_string($con, $_POST['employment_type']); // New variable

    $update_sql = "UPDATE staff SET 
                    FirstName = '$firstname', 
                    LastName = '$lastname', 
                    Email = '$email', 
                    Password = '$password',
                    PhoneNo = '$phoneNo', 
                    UserRole = '$role', 
                    Designation = '$designation', 
                    Department = '$department',
                    Status = '$status',
                    EmploymentType = '$empType' 
                    WHERE StaffID = '$id'";

    if ($con->query($update_sql)) {
        echo "<script>window.location.href='pteacher-record.php?msg=updated';</script>";
        exit();
    } else {
        $message = "<div class='alert alert-danger'>Update Failed: " . $con->error . "</div>";
    }
}

// 3. FETCH DATA
$fetch_query = "SELECT * FROM staff WHERE StaffID = '$id'";
$record = $con->query($fetch_query);

if ($record && $record->num_rows > 0) {
    $data = $record->fetch_assoc();
} else {
    header("Location: pteacher-record.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Update Teacher Record</title>
    <link rel="icon" type="image/png" href="../../images/pars.png">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f8f9fa; }
        .card { border-radius: 15px; margin-top: 50px; margin-bottom: 50px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow border-0 p-4">
                    <h2 class="text-center mb-4 text-primary">Edit Staff Information</h2>
                    <?php echo $message; ?>

                    <form action="" method="POST">
                        <input type="hidden" name="staff_id" value="<?php echo $id; ?>">

                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label class="font-weight-bold">First Name</label>
                                <input type="text" name="firstname" class="form-control" value="<?php echo htmlspecialchars($data['FirstName']); ?>" required>
                            </div>
                            <div class="col-md-6 form-group">
                                <label class="font-weight-bold">Last Name</label>
                                <input type="text" name="lastname" class="form-control" value="<?php echo htmlspecialchars($data['LastName']); ?>">
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label class="font-weight-bold">Email Address</label>
                                <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($data['Email']); ?>" required>
                            </div>
                            <div class="col-md-6 form-group">
                                <label class="font-weight-bold">Password</label>
                                <input type="text" name="password" class="form-control" value="<?php echo htmlspecialchars($data['Password']); ?>" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label class="font-weight-bold">Phone Number</label>
                                <input type="text" name="phoneNo" class="form-control" value="<?php echo htmlspecialchars($data['PhoneNo']); ?>">
                            </div>
                            <div class="col-md-6 form-group">
                                <label class="font-weight-bold">Status</label>
                                <select name="status" class="form-control">
                                    <option value="Active" <?php echo ($data['Status'] == 'Active') ? 'selected' : ''; ?>>Active</option>
                                    <option value="Inactive" <?php echo ($data['Status'] == 'Inactive') ? 'selected' : ''; ?>>Inactive</option>
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label class="font-weight-bold">Role</label>
                                   <input type="text" name="role" class="form-control" value="<?php echo htmlspecialchars($data['UserRole']);?>">
                            
                            </div>
                            <div class="col-md-6 form-group">
                                <label class="font-weight-bold">Department</label>
                                <input type="text" name="department" class="form-control" value="<?php echo htmlspecialchars($data['Department']); ?>">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label class="font-weight-bold">Designation</label>
                                <input type="text" name="designation" class="form-control" value="<?php echo htmlspecialchars($data['Designation']); ?>">
                            </div>
                            <div class="col-md-6 form-group">
                                <label class="font-weight-bold">Employment Type</label>
                                <select name="employment_type" class="form-control">
                                    <option value="Permanent" <?php echo ($data['EmploymentType'] == 'Permanent') ? 'selected' : ''; ?>>Permanent</option>
                                    <option value="Visiting" <?php echo ($data['EmploymentType'] == 'Visiting') ? 'selected' : ''; ?>>Visiting</option>
                                </select>
                            </div>
                        </div>

                        <div class="mt-4">
                            <button type="submit" name="update_teacher" class="btn btn-success btn-lg btn-block shadow-sm">Update Staff Member</button>
                            <a href="staffrecord.php" class="btn btn-link btn-block text-muted">Cancel and Go Back</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html>