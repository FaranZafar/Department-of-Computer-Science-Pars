<?php
include_once("../dbconnection.php"); 

// Start session to carry the success message over the redirect
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if(isset($_POST['submit'])){
    $name = mysqli_real_escape_string($con, $_POST['name']);
    $email = mysqli_real_escape_string($con, $_POST['email']);
    $semester = mysqli_real_escape_string($con, $_POST['semester']);
    $degree = mysqli_real_escape_string($con, $_POST['degree']);
    $rollno = mysqli_real_escape_string($con, $_POST['rollno']); 
    $section = mysqli_real_escape_string($con, $_POST['section']);
    $message = mysqli_real_escape_string($con, $_POST['message']);

    if(empty($name) || empty($email) || empty($message)){
        $_SESSION['status'] = "danger";
        $_SESSION['msg'] = "Please fill in all required fields.";
    } else {
        // Table name is 'messages' based on your schema
        $query = "INSERT INTO message (name, email, semester, degree, ag_no, section, message) 
                  VALUES ('$name', '$email', '$semester', '$degree', '$rollno', '$section', '$message')";
        
        if(mysqli_query($con, $query)){
            $_SESSION['status'] = "success";
            $_SESSION['msg'] = "Message sent successfully!";
        } else {
            $_SESSION['status'] = "danger";
            $_SESSION['msg'] = "Error: " . mysqli_error($con);
        }
    }
    
    // Redirect to the same page to clear POST data and avoid duplication on refresh
    header("Location: " . $_SERVER['PHP_SELF'] . "#contact");
    exit();
}

// Retrieve the message from session if it exists
$message_status = "";
if(isset($_SESSION['msg'])){
    $type = $_SESSION['status'];
    $text = $_SESSION['msg'];
    $message_status = "<div class='alert alert-$type shadow-sm'>$text</div>";
    
    // Clear session so message doesn't persist forever
    unset($_SESSION['msg']);
    unset($_SESSION['status']);
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pars Campus | Department of Computer Science</title>
    <link rel="icon" type="image/png" href="../images/pars.png">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8f9fa;
            color: #333;
        }

        /* Navbar Styling */
        .navbar { border-bottom: 3px solid #0056b3; }
        .brand-name { letter-spacing: 1px; color: grey; }

        /* Hero Section */
        .bg-image {
            background-image: linear-gradient(rgba(0, 0, 0, 0.7), rgba(0, 0, 0, 0.7)), url("../images/back.png");
            background-position: center;
            background-repeat: no-repeat;
            background-size: cover;
            min-height: calc(100vh - 75px); 
            width: 100%;
            display: flex;
            align-items: center;
            padding: 2rem 0;
            margin-bottom: 0;
        } 

        .lead { text-shadow: 2px 2px 4px rgba(0,0,0,0.5); }

        /* General Section Styling */
        #about, #contact { padding: 60px 0; }
        
        .header-section {
            padding-bottom: 40px;
            text-align: center;
        }

        .section-title {
            font-weight: 700;
            color: #1a2a6c;
            position: relative;
            display: inline-block;
            margin-bottom: 40px;
        }

        .section-title::after {
            content: '';
            width: 50px;
            height: 4px;
            background: #007bff;
            position: absolute;
            bottom: -15px;
            left: 50%;
            transform: translateX(-50%);
            border-radius: 2px;
        }

        /* Leadership Cards */
        .leadership-card {
            border: none;
            border-radius: 15px;
            overflow: hidden;
            background: #fff;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
            transition: all 0.3s ease;
            height: 80%;
        }

        .img-wrapper {
            width: 100%;
            aspect-ratio: 4 / 5; 
            overflow: hidden;
            background-color: #f0f0f0;
        }

        .img-wrapper img {
            width: 100%;
            height: 100%;
            object-fit: cover; 
            object-position: top; 
            display: block;
        }

        @media (max-width: 576px) {
            .dept-text { font-size: 0.85rem !important; }
            .brand-name { font-size: 1.2rem !important; }
            .hero-section { padding: 40px 15px; }
        }
    </style>
</head>
<body>

    <nav class="navbar navbar-expand-lg navbar-light bg-white sticky-top shadow-sm">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="#">
                <img src="../images/Pars.png" width="60" alt="LOGO" class="mr-3">
                <div class="d-flex flex-column">
                    <span class="dept-text font-weight-bold" style="font-size: 0.9rem; color:#0056b3; text-transform: uppercase;">Dept. of Computer Science</span>
                    <span class="brand-name font-weight-bold" style="font-size: 1.4rem; line-height: 1;">Pars Campus</span>
                </div>
            </a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#collapNavbar">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="collapNavbar">
                <ul class="navbar-nav ml-auto font-weight-bold">
                    <li class="nav-item px-2"><a class="nav-link text-primary" href="#home">Home</a></li>
                    <li class="nav-item px-2"><a class="nav-link" href="#about">About</a></li>
                    <li class="nav-item px-2"><a class="nav-link" href="../Offered-programs/program.php">Programs</a></li>
                    <li class="nav-item px-2"><a class="nav-link" href="#contact">Contact</a></li>
                    <li class="nav-item ml-lg-3">
                        <a href="../authurization/login.php" class="btn btn-outline-primary px-4 shadow-sm">Portal Login</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <section class="hero-section bg-image shadow-sm text-center" id="home">
        <div class="container"> 
            <div class="row">
                <div class="col-md-10 mx-auto">
                    <h1 class="display-3 font-weight-bold text-white mt-4 mb-3">Welcome to Pars Campus</h1>
                    <p class="lead text-left font-weight-bold" style="font-size: 1.8rem; color: white;">"The Forge of Ambition"</p>
                    <p class="text-white-50 text-left mx-auto" style="max-width: 800px; font-size: 1.2rem; line-height: 1.6;">
                        This is the space where aspirations take shape and the abstract becomes reality. 
                        We provide the environment designed for those ready to bridge the gap 
                        between who they are and who they intend to become.
                    </p>
                    <div class="mt-4 text-left">
                        <a class="btn btn-primary btn-lg px-5 shadow-lg" href="../Offered-programs/program.php" role="button">Explore Programs</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <main class="container">
        <section id="about">
            <div class="header-section">
                <h1 class="section-title">Our Leadership</h1>
            </div>

            <div class="row">
                <?php
                
                $result = mysqli_query($con, "SELECT * FROM leadership_staff ORDER BY id DESC");
                
                while ($row = mysqli_fetch_assoc($result)) {
                    $fullName = $row['first_name'] . " " . $row['last_name'];
                    $role = $row['user_role'];
                    $photo = "uploads/" . $row['staff_img'];
                ?>
                <div class="col-md-4">
                    <div class="card shadow-sm border-0 mb-4">
                        <div style="height:320px; overflow:hidden;">
                            <img src="<?php echo $photo; ?>" style="width:100%; height:100%; object-fit:cover; object-position:top;">
                        </div>
                        <div class="card-body text-center">
                            <h5 class="font-weight-bold"><?php echo $fullName; ?></h5>
                            <p class="text-primary"><?php echo $role; ?></p>
                        </div>
                    </div>
                </div>
                <?php } ?>
            </div>
        </section>
        
        <section id="contact" class="py-5 bg-light">
            <div class="container">
                <div class="header-section text-center">
                    <h2 class="section-title">Contact Us</h2>
                </div>
                
                <div class="row justify-content-center">
                    <div class="col-md-10">
                        <div class="card shadow-sm border-0 p-4">
                            <form action="" method="POST">
                                <?php echo $message_status;?>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="name" class="small font-weight-bold">Full Name</label>
                                            <input type="text" name="name" class="form-control" placeholder="Enter your name" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="email" class="small font-weight-bold">Email Address</label>
                                            <input type="email" name="email" class="form-control" placeholder="Enter your email" required>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="semester" class="small font-weight-bold">Semester</label>
                                            <input type="text" name="semester" class="form-control" placeholder="Enter your Semester" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="degree" class="small font-weight-bold">Degree</label>
                                            <input type="text" name="degree" class="form-control" placeholder="Enter your degree" required>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="rollno" class="small font-weight-bold">Ag-No</label>
                                            <input type="text" name="rollno" class="form-control" placeholder="Enter your registration number" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                              <label for="section" class="small font-weight-bold">Section</label>
                                              <input type="text" name="section" class="form-control" placeholder="Enter your section" required>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="message" class="small font-weight-bold">Message</label>
                                    <textarea name="message" class="form-control" rows="4" placeholder="How can we help you?"></textarea>
                                </div>

                                <div class="text-center mt-4">
                                    <button type="submit" class="btn btn-primary px-5 shadow-sm font-weight-bold" name="submit">Send Message</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <footer class="bg-dark text-white py-4 mt-5">
        <div class="container text-center">
            <p class="mb-0 small">&copy; 2026 Pars Campus - Department of Computer Science. All Rights Reserved.</p>
        </div>
    </footer>
    
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>