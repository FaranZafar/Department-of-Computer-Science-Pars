<?php
include_once("../../dbconnection.php");

$message = ""; // used to store error messages

if (isset($_POST['submit_Teacher'])) {
    // 1. Collect and Sanitize Data
    $firstname = mysqli_real_escape_string($con, $_POST['firstname']);
    $lastname  = mysqli_real_escape_string($con, $_POST['lastname']);
    $email     = mysqli_real_escape_string($con, $_POST['email']);
    $password  = $_POST['password']; // Will hash this later
    $phoneNo   = mysqli_real_escape_string($con, $_POST['phoneNo']);
    $cnic      = mysqli_real_escape_string($con, $_POST['cnic']);
    $role      = mysqli_real_escape_string($con, $_POST['staff']);
    $designation = mysqli_real_escape_string($con, $_POST['designation']);
    $department  = mysqli_real_escape_string($con, $_POST['department']);
    $joiningDate = mysqli_real_escape_string($con, $_POST['joiningDate']);
    $qualification = mysqli_real_escape_string($con, $_POST['qualification']);
    $salary      = mysqli_real_escape_string($con, $_POST['salary']);
    $status      = mysqli_real_escape_string($con, $_POST['status']);

    // 2. Validation Checks
    if (empty($firstname) || empty($email) || empty($password)) {
        $message = "<div class='alert alert-danger'>Required fields are missing.</div>";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "<div class='alert alert-danger'>Invalid email format.</div>";
    } elseif (strlen($password) < 6) {
        $message = "<div class='alert alert-danger'>Password must be at least 6 characters.</div>";
    } else {
        // 4. Insert Query
        $insert_query = "INSERT INTO staff (FirstName, LastName, Email, Password, PhoneNo, CNIC, UserRole, Designation, Department, joiningDate, Qualification, Salary, Status) 
                         VALUES ('$firstname', '$lastname', '$email', '$password', '$phoneNo', '$cnic', '$role', '$designation', '$department', '$joiningDate', '$qualification', '$salary', '$status')";

        if ($con->query($insert_query)) {
            $message = "<div class='alert alert-success'>Supporting staff member added successfully!</div>";
        } else {
            $message = "<div class='alert alert-danger'>Error: " . $con->error . "</div>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Teacher</title>
    <link rel="icon" type="image/png" href="../../images/pars.png">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style>body { font-family: 'Inter', sans-serif; background-color: #f4f7f6; }</style>
</head>
<body>
    
    <div class="container mt-5 mb-5">
        <div class="row justify-content-center">
            <div class="col-md-10">
                <div class="card shadow-sm border-0 p-4">
                    <h2 class="text-center mb-4">Add Supporting Staff</h2>
                    
                    <?php echo $message; ?>
                   <div class="row">
                    <div class="col-md-4">
                         <a href="staffrecord.php" class="btn btn-primary">Back</a>
                    </div>
                   </div>
                    <form action="" method="POST">
                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label class="small font-weight-bold">First Name</label>
                                <input type="text" name="firstname" class="form-control" placeholder="First name" required>
                            </div>
                            <div class="col-md-6 form-group">
                                <label class="small font-weight-bold">Last Name</label>
                                <input type="text" name="lastname" class="form-control" placeholder="Last name" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label class="small font-weight-bold">Email</label>
                                <input type="email" name="email" class="form-control" placeholder="email@example.com" required>
                            </div>
                            <div class="col-md-6 form-group">
                                <label class="small font-weight-bold">Password</label>
                                <input type="password" name="password" class="form-control" placeholder="Min 6 characters" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label class="small font-weight-bold">Phone No</label>
                                <input type="text" name="phoneNo" class="form-control" placeholder="0300-0000000" required>
                            </div>
                            <div class="col-md-6 form-group">
                                <label class="small font-weight-bold">CNIC</label>
                                <input type="text" name="cnic" class="form-control" placeholder="00000-0000000-0" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label class="small font-weight-bold">Role</label>
                                 <input type="text" name="staff" class="form-control" placeholder="Electrician etc">
                            </div>
                            <div class="col-md-6 form-group">
                                <label class="small font-weight-bold">Designation</label>
                                <input type="text" name="designation" class="form-control" placeholder="e.g. Lecturer">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label class="small font-weight-bold">Department</label>
                                <input type="text" name="department" class="form-control" placeholder="e.g. CS">
                            </div>
                            <div class="col-md-6 form-group">
                                <label class="small font-weight-bold">Joining Date</label>
                                <input type="date" name="joiningDate" class="form-control">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label class="small font-weight-bold">Qualification</label>
                                <input type="text" name="qualification" class="form-control" placeholder="e.g. MSCS">
                            </div>
                            <div class="col-md-4 form-group">
                                <label class="small font-weight-bold">Salary</label>
                                <input type="number" name="salary" class="form-control" placeholder="Amount">
                            </div>
                            <div class="col-md-2 form-group">
                                <label class="small font-weight-bold">Status</label>
                                <select name="status" class="form-control">
                                    <option value="Active">Active</option>
                                    <option value="Inactive">Inactive</option>
                                </select>
                            </div>
                        </div>

                        <button type="submit" name="submit_Teacher" class="btn btn-primary btn-block mt-3">Register Supporting Staff</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>