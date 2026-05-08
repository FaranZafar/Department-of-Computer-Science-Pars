<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Coordinator Dashboard</title>
    <link rel="icon" type="image/png" href="../images/pars.png">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body {
             font-family: 'Inter', sans-serif; 
             background-color: #f8f9fa; 
            }
        .jumbotron {
             background: #ffffff; 
             border-bottom: 1px solid #dee2e6; 
             padding: 2rem 1rem; 
            }
        .card { 
            border: none; 
            transition: transform 0.2s; 
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            margin-bottom: 20px; 
        }
        .card:hover { 
            transform: translateY(-5px); 
        }
        .btn-custom { 
            padding: 15px; 
            font-weight: 600; 
            width: 100%; 
        }
        .section-title {
             margin: 40px 0 20px; 
             font-weight: 700; 
             color: #343a40; 
        }
    </style>
</head>
<body>

<div class="jumbotron">
    <div class="container">
        <h1 class="display-5 text-center mb-4">Management Portal</h1>
        <div class="row justify-content-center">
            <!-- Add offered programs -->
            <div class="col-lg-3 col-md-4 col-sm-6">
                <div class="card">
                    <div class="card-body p-2">
                        <a href="../offered-programs/addprogram.php" class="btn btn-primary btn-custom">Add Program</a>
                    </div>
                </div>
            </div>
            <!-- Add leadership -->
            <div class="col-lg-3 col-md-4 col-sm-6">
                <div class="card">
                    <div class="card-body p-2">
                        <a href="../homepage/addleader.php" class="btn btn-primary btn-custom">Add Leadership</a>
                    </div>
                </div>
            </div>
            <!-- timetable -->
            <div class="col-lg-3 col-md-4 col-sm-6">
                <div class="card">
                    <div class="card-body p-2">
                        <a href="#" class="btn btn-primary btn-custom">Upload TimeTable</a>
                    </div>
                </div>
            </div>
            <!-- messages -->
            <div class="col-lg-3 col-md-4 col-sm-6">
                <div class="card">
                    <div class="card-body p-2">
                        <a href="./messages/seenmsg.php" class="btn btn-primary btn-custom">Messages</a>
                    </div>
                </div>
            </div>
            
        </div>
    </div>
</div>

<div class="container mb-5">
    <h2 class="section-title text-center">Staff Records</h2>
    <hr class="mb-4">
    <div class="row">
        <div class="col-md-4">
            <div class="card">
                <div class="card-body text-center">
                    <h5 class="card-title">Administrative</h5>
                    <p class="text-muted small">Manage Admin & Coordinators</p>
                    <a href="./Coordinator-record/Coordinator-record.php" class="btn btn-outline-dark btn-block">View Records</a>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-body text-center">
                    <h5 class="card-title">Faculty</h5>
                    <p class="text-muted small">Manage Permanent & Visiting Teachers</p>
                    <a href="./teacher-record/teacher-record.php" class="btn btn-outline-dark btn-block">View Records</a>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-body text-center">
                    <h5 class="card-title">Support</h5>
                    <p class="text-muted small">Manage Technical & Support Staff</p>
                    <a href="./staff-record/staffrecord.php" class="btn btn-outline-dark btn-block">View Records</a>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- assigning Teachers & Room -->
<div class="container mb-5">
    <h2 class="section-title text-center">Assigning Teachers & Room</h2>
    <hr class="mb-4">
    <div class="row">
        <div class="col-md-4">
            <div class="card">
                <div class="card-body text-center">
                    <h5 class="card-title">Assign teachers & Room</h5>
                    <p class="text-muted small">Manage Assigned</p>
                    <a href="./assignedsub/assigned_teacher_room.php" class="btn btn-outline-dark btn-block">View Records</a>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-body text-center">
                    <h5 class="card-title">Taught Course</h5>
                    <p class="text-muted small">Here are sections/courses who taught you</p>
                    <a href="./assignedsub/assignedsubject.php" class="btn btn-outline-dark btn-block">View sections/Courses</a>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Student/Courses Management -->
<div class="container mb-5">
    <h2 class="section-title text-center">Student & Courses Management</h2>
    <hr class="mb-4">
    <div class="row">
        <div class="col-md-4">
            <div class="card">
                <div class="card-body text-center">
                    <h5 class="card-title">Add New Student</h5>
                    <p class="text-muted small">Manage Students Enrollment & record Here</p>
                    <a href="enroll_student.php" class="btn btn-outline-dark btn-block">Add/View & Manage</a>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-body text-center">
                    <h5 class="card-title">Add New Semester </h5>
                    <p class="text-muted small">Manage Semesters Here</p>
                    <a href="./assignedsub/addsem.php" class="btn btn-outline-dark btn-block">Add & Manage</a>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-body text-center">
                    <h5 class="card-title">Add New Degree</h5>
                    <p class="text-muted small">Manage offered Degree Here</p>
                    <a href="./assignedsub/adddegree.php" class="btn btn-outline-dark btn-block">Add & Manage</a>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-body text-center">
                    <h5 class="card-title">Add New Course</h5>
                    <p class="text-muted small">Manage Courses Here</p>
                    <a href="./assignedsub/addcourses.php" class="btn btn-outline-dark btn-block">Add & Manage</a>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-body text-center">
                    <h5 class="card-title">Create Sections</h5>
                    <p class="text-muted small">Manage Sctions Here</p>
                    <a href="./assignedsub/manage_sections.php" class="btn btn-outline-dark btn-block">Create & Manage</a>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-body text-center">
                    <h5 class="card-title">Add Room</h5>
                    <p class="text-muted small">Manage Room detail here </p>
                    <a href="./assignedsub/addroom.php" class="btn btn-outline-dark btn-block">Create & Manage</a>
                </div>
            </div>
        </div>
        
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>