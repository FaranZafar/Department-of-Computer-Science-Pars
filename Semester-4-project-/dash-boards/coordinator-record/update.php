<?php
include_once("../../dbconnection.php");

$message = "";

// 1. GET THE EXISTING DATA
if (isset($_GET['id'])) {
    $id = mysqli_real_escape_string($con, $_GET['id']);
    $fetch_query = "SELECT * FROM staff WHERE StaffID = '$id'";
    $record = $con->query($fetch_query);
    
    if ($record && $record->num_rows > 0) {
        $data = $record->fetch_assoc();
    } else {
        header("Location: coordinator-record.php");
        exit();
    }
}

// 2. HANDLE THE UPDATE SUBMISSION
if (isset($_POST['update_coordinator'])) {
    $firstname   = mysqli_real_escape_string($con, $_POST['firstname']);
    $lastname    = mysqli_real_escape_string($con, $_POST['lastname']);
    $email       = mysqli_real_escape_string($con, $_POST['email']);
    $password    = mysqli_real_escape_string($con, $_POST['password']);
    $phoneNo     = mysqli_real_escape_string($con, $_POST['phoneNo']);
    $role        = mysqli_real_escape_string($con, $_POST['role']);
    $designation = mysqli_real_escape_string($con, $_POST['designation']);
    $department  = mysqli_real_escape_string($con, $_POST['department']);
    $status      = mysqli_real_escape_string($con, $_POST['status']);

    // FIXED: Removed the semicolon after $password and added missing quotes/commas
    $update_sql = "UPDATE staff SET 
                   FirstName = '$firstname', 
                   LastName = '$lastname', 
                   Email = '$email', 
                   Password = '$password',
                   PhoneNo = '$phoneNo', 
                   UserRole = '$role', 
                   Designation = '$designation', 
                   Department = '$department',
                   Status = '$status' 
                   WHERE StaffID = '$id'";

    if ($con->query($update_sql)) {
        header("Location: coordinator-record.php?msg=updated");
        exit();
    } else {
        $message = "<div class='alert alert-danger'>Update Failed: " . $con->error . "</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Update Staff Member</title>
    <link rel="icon" type="image/png" href="../../images/pars.png">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
    <style>
    body 
    { 
    font-family: 'Inter', sans-serif; 
    background-color: #f8f9fa; 
    }
    </style>
</head>
<body>
    <div class="container mt-5 mb-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow border-0 p-4">
                    <h2 class="text-center mb-4">Edit Staff Information</h2>
                    <?php echo $message; ?>

                    <form action="" method="POST">
                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label class="font-weight-bold">First Name</label>
                                <input type="text" name="firstname" class="form-control" 
                                       value="<?php echo htmlspecialchars($data['FirstName']); ?>" required>
                            </div>
                            <div class="col-md-6 form-group">
                                <label class="font-weight-bold">Last Name</label>
                                <input type="text" name="lastname" class="form-control" 
                                       value="<?php echo htmlspecialchars($data['LastName']); ?>">
                            </div>
                        </div>
                        <div class="row">
                        <div class=" col-md-6 form-group">
                            <label class="font-weight-bold">Email Address</label>
                            <input type="email" name="email" class="form-control" 
                                   value="<?php echo htmlspecialchars($data['Email']); ?>" required>
                        </div>
                        <div class=" col-md-6 form-group">
                            <label class="font-weight-bold">Password</label>
                            <input type="password" name="password" class="form-control" 
                                   value="<?php echo htmlspecialchars($data['Password']); ?>" required>
                        </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label class="font-weight-bold">Phone Number</label>
                                <input type="text" name="phoneNo" class="form-control" 
                                       value="<?php echo htmlspecialchars($data['PhoneNo']); ?>">
                            </div>
                            <div class="col-md-6 form-group">
                                <label class="font-weight-bold">Status</label>
                                <select name="status" class="form-control">
                                    <option value="Active" <?php if($data['Status'] == 'Active') echo 'selected'; ?>>Active</option>
                                    <option value="Inactive" <?php if($data['Status'] == 'Inactive') echo 'selected'; ?>>Inactive</option>
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label class="font-weight-bold">Role</label>
                                <select name="role" class="form-control">
                                    <option value="Coordinator" <?php if($data['UserRole'] == 'Coordinator') echo 'selected'; ?>>Coordinator</option>
                                    <option value="Admin" <?php if($data['UserRole'] == 'Admin') echo 'selected'; ?>>Admin</option>
                                </select>
                            </div>
                            <div class="col-md-6 form-group">
                                <label class="font-weight-bold">Department</label>
                                <input type="text" name="department" class="form-control" 
                                       value="<?php echo htmlspecialchars($data['Department']); ?>">
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="font-weight-bold">Designation</label>
                            <input type="text" name="designation" class="form-control" 
                                   value="<?php echo htmlspecialchars($data['Designation']); ?>">
                        </div>

                        <div class="mt-4">
                            <button type="submit" name="update_coordinator" class="btn btn-success btn-lg btn-block">Save Changes</button>
                            <a href="coordinator-record.php" class="btn btn-link btn-block text-muted">Cancel and Go Back</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html>