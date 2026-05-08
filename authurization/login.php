<?php
session_start();
include_once("../dbconnection.php");

$error_msg = ""; 

if (isset($_POST['login_btn'])) {
    $email = mysqli_real_escape_string($con, $_POST['email']);
    $pass = mysqli_real_escape_string($con, $_POST['password']);
    $role = mysqli_real_escape_string($con, $_POST['role']);

    if (empty($email) || empty($pass)) {
        $error_msg = "Error: Fields must not be empty!";
    } else {
        // Corrected Logic: Ensure $role matches the "value" in the <select>
        if ($role === "Student") {
            // Using actual column names based on your previous messages
            $query = "SELECT * FROM students WHERE email = '$email' AND password = '$pass' LIMIT 1";
        } else {
            $query = "SELECT * FROM Staff WHERE Email = '$email' AND Password = '$pass' AND UserRole = '$role' LIMIT 1";
        }

        $result = mysqli_query($con, $query);

        if ($result && mysqli_num_rows($result) == 1) {
            $row = mysqli_fetch_assoc($result);

            if ($role === "Student") {
                $_SESSION['user_id'] = $row['student_id']; 
                $_SESSION['user_name'] = $row['name'];
            } else {
                $_SESSION['user_id'] = $row['StaffID'];
                $_SESSION['user_name'] = $row['FirstName'] . " " . $row['LastName'];
            }
            
            $_SESSION['user_role'] = $role;

            // Role-based redirection
            switch ($role) {
                case "Coordinator":
                    header("Location: ../dash-boards/coordinator.php");
                    break;
                case "Teacher":
                    header("Location: ../dash-boards-teacher/teacher_dashboard.php");
                    break;
                case "Student":
                    header("Location: ../dash-board-student/student_dashboard.php");
                    break;
                case "Admin":
                    header("Location: ../dash-boards/admin_dashboard.php");
                    break;
                default:
                    header("Location: ../index.php");
            }
            exit();
        } else {
            $error_msg = "Error: Invalid Credentials or Role mismatch!";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login Portal | Pars Campus</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="icon" type="image/png" href="../images/pars.png">
   <style>
        body { background-color: #f0f2f5; height: 100vh; display: flex; align-items: center; justify-content: center; }
        .login-card { border-radius: 15px; box-shadow: 0 10px 25px rgba(0,0,0,0.1); width: 100%; max-width: 400px; background: #fff; }
    </style>
</head>
<body>
    <div class="card login-card p-4">
        <div class="text-center mb-4">
            <h4 class="font-weight-bold">Pars Campus</h4>
            <small class="text-muted">Portal Login</small>
        </div>
        
        <form action="" method="POST">
            <?php if ($error_msg != ""): ?>
                <div class='alert alert-danger small'><?php echo $error_msg; ?></div>
            <?php endif; ?>

            <div class="form-group">
                <label class="small font-weight-bold">Email address</label>
                <input type="email" name="email" class="form-control" placeholder="name@uaf.edu.pk" required>
            </div>
            
            <div class="form-group">
                <label class="small font-weight-bold">Password</label>
                <input type="password" name="password" class="form-control" placeholder="••••••••" required>
            </div>

            <div class="form-group">
                <label class="small font-weight-bold">Role</label>
                <select name="role" class="form-control" required>
                  <option value="Coordinator">Coordinator</option>
                  <option value="Teacher">Teacher</option>
                  <option value="Student">Student</option> <!-- FIXED VALUE AND LABEL -->
                  <option value="Admin">Admin</option>
                </select>
            </div>

            <button type="submit" name="login_btn" class="btn btn-primary btn-block font-weight-bold">Log In</button>
        </form>
    </div>
</body>
</html>