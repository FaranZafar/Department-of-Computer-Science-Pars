<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pars Campus - Sign Up</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body { 
            font-family: 'Inter', sans-serif; 
            background-color: #f0f2f5; 
            min-height: 100vh; 
            display: flex;
            align-items: center;
            justify-content: center; 
            margin: 0;
         }
        .login-card {
             border: none; 
             border-radius: 15px;
             box-shadow: 0 10px 25px rgba(0,0,0,0.1);
             width: 100%; 
             max-width: 450px;
             background: #fff;
             }
        .btn-primary-custom { 
            background-color: #0056b3; 
            color: white; 
            border: none; 
            padding: 10px;
            font-weight: 600;
            transition: 0.3s;
         }
        .btn-primary-custom:hover { 
            background-color: #004494; 
            color: white;
         }
        .brand-logo { 
            text-align: center; 
            margin-bottom: 25px; 
        }
        /* Hide teacher fields by default */
        #teacherFields { display: none; }
    </style>
</head>
<body>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="card login-card p-4">
            <div class="brand-logo">
                <h4 class="font-weight-bold text-dark mb-0">Pars Campus</h4>
                <small class="text-muted">Create your Account</small>
            </div>
            
            <form action="signup_process.php" method="POST">
                <div class="form-group">
                    <label class="small font-weight-bold">Register As</label>
                    <select name="role" id="roleSelect" class="form-control form-control-sm">
                        <option value="student">Student</option>
                        <option value="teacher">Teacher</option>
                        <option value="coordinator">Coordinator</option>
                    </select>
                </div>

                <div class="form-row">
                    <div class="form-group col-6">
                        <label for="firstname" class="small font-weight-bold">First Name</label>
                        <input type="text" name="firstname" class="form-control" placeholder="Faran" required>
                    </div>
                    <div class="form-group col-6">
                        <label for="lastname" class="small font-weight-bold">Last Name</label>
                        <input type="text" name="lastname" class="form-control" placeholder="Zafar" required>
                    </div>
                </div>

                <div id="studentFields">
                    <div class="form-group">
                        <label for="degree" class="small font-weight-bold">Degree</label>
                        <input type="text" name="degree" id="degree" class="form-control" placeholder="BS Software Engineering">
                    </div>
                    <div class="form-row">
                        <div class="form-group col-6">
                            <label for="semester" class="small font-weight-bold">Semester</label>
                            <input type="number" name="semester" id="semester" class="form-control" placeholder="4">
                        </div>
                        <div class="form-group col-6">
                            <label for="section" class="small font-weight-bold">Section</label>
                            <input type="text" name="section" id="section" class="form-control" placeholder="A">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="rollno" class="small font-weight-bold">Ag-NO</label>
                        <input type="text" name="rollno" id="rollno" class="form-control" placeholder="2024-ag-XXXX">
                    </div>
                </div>

                <div id="teacherFields">
                    <div class="form-group">
                        <label for="department" class="small font-weight-bold">Department</label>
                        <select name="department" id="department" class="form-control">
                            <option value="">Select Department</option>
                            <option value="Computer Science">Computer Science</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label for="email" class="small font-weight-bold">Email Address</label>
                    <input type="email" name="email" class="form-control" placeholder="name@example.com" required>
                </div>

                <div class="form-group">
                    <label for="password" class="small font-weight-bold">Password</label>
                    <input type="password" name="password" class="form-control" placeholder="••••••••" required>
                </div>

                <button type="submit" class="btn btn-outline-primary btn-block">Sign Up</button>
                
                <div class="text-center mt-3">
                   <small>Already have an account?</small> <a href="login.php" class="small font-weight-bold">Login</a>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.slim.min.js"></script>
<script>
$(document).ready(function() {
    $('#roleSelect').change(function() {
        var role = $(this).val();
        
        if (role === 'student') {
            $('#studentFields').show();
            $('#teacherFields').hide();
            // Add required to student inputs
            $('#studentFields input').prop('required', true);
            $('#department').prop('required', false);
        } else {
            $('#studentFields').hide();
            $('#teacherFields').show();
            // Remove required from student, add to teacher
            $('#studentFields input').prop('required', false);
            $('#department').prop('required', true);
        }
    });

    // Trigger on load to set initial state
    $('#roleSelect').trigger('change');
});
</script>
</body>
</html>