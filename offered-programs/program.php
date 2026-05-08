<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Offered Programs | Pars Campus</title>
    <link rel="icon" type="image/png" href="../images/pars.png">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body { 
            font-family: 'Inter', sans-serif;
             background-color: #f4f7f9;
             }
        .navbar { 
            border-bottom: 3px solid #0056b3;
         }
        .bg-image {
            background-image: linear-gradient(rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0.6)), url("../images/back.png");
            background-position: center; background-repeat: no-repeat; background-size: cover;
            min-height: calc(100vh - 70px); display: flex; align-items: center; justify-content: center; color: white;
        }
        .section-title { 
            position: relative;
             margin-bottom: 40px; 
             font-weight: 700; 
             color: #003366; 
             text-center; }
        .section-title::after { 
            content: ''; 
            width: 60px;
             height: 4px;
             background: #0056b3; 
            position: absolute; 
            bottom: -10px; 
            left: 50%;
             transform: translateX(-50%); 
        }
        .program-card { 
            border: none; 
            border-radius: 12px; 
            transition: all 0.3s ease; 
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        }
        .program-card:hover { 
            transform: translateY(-10px); 
            box-shadow: 0 10px 30px rgba(0,0,0,0.1) !important; }
        .badge-duration { 
            background-color: #fff3e0; 
            color: #d35400; 
            font-weight: 600; 
            font-size: 0.8rem; 
            padding: 5px 12px; 
            border-radius: 20px; 
            width: fit-content; 
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-white sticky-top shadow-sm">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="#">
                <img src="../images/pars.png" width="60" alt="LOGO" class="mr-3">
                <div class="d-flex flex-column">
                    <span style="font-size: 0.9rem; color:#0056b3; text-transform: uppercase; font-weight:bold;">Dept. of Computer Science</span>
                    <span style="font-size: 1.4rem; line-height: 1; font-weight:bold; color:grey;">Pars Campus</span>
                </div>
            </a>
             <!-- togglar button -->
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#collapNavbar">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="collapNavbar">
                <ul class="navbar-nav ml-auto font-weight-bold">
                    <!-- navbar items -->
                    <li class="nav-item px-2"><a class="nav-link text-primary" href="../homepage/home.php">Home</a></li>
                    <li class="nav-item px-2"><a class="nav-link" href="../homepage/home.php#about">About</a></li>
                    <li class="nav-item px-2"><a class="nav-link" href="#programs">Programs</a></li>
                    <li class="nav-item px-2"><a class="nav-link" href="../homepage/home.php#contact">Contact</a></li>
                    <li class="nav-item ml-lg-3">
                        <a href="../authurization/login.php" class="btn btn-outline-primary px-4 shadow-sm">Portal Login</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="jumbotron text-center bg-image">
        <div class="container">
            <h1 class="display-3 font-weight-bold">Our Academic Programs</h1>
            <p class="lead">Empowering the next generation of technologists.</p>
        </div>
    </div>

    <section id="programs" class="py-5 bg-light">
        <div class="container">
            <h2 class="text-center section-title mb-5">Our Academic Programs</h2>
            <div class="row">
                <?php
                $file = 'data.json';
                if (file_exists($file)) {
                    $programs = json_decode(file_get_contents($file), true);
                    foreach ($programs as $p) {
                        echo "
                        <div class='col-md-4 mb-4 d-flex align-items-stretch'>
                            <div class='card program-card h-100 w-100'>
                                <div class='card-body d-flex flex-column'>
                                    <span class='badge badge-duration mb-2'>{$p['duration']}</span>
                                    <h4 class='card-title font-weight-bold'>{$p['title']}</h4>
                                    <p class='card-text text-muted'>{$p['description']}</p>
                                    <button class='btn btn-outline-primary btn-block mt-auto' style='border-radius: 25px;'>View Curriculum</button>
                                </div>
                            </div>
                        </div>";
                    }
                } else {
                    echo "<p class='col-12 text-center text-muted'>No programs added yet.</p>";
                }
                ?>
            </div>
        </div>
    </section>

    <footer class="bg-dark text-white py-4 mt-5">
        <div class="container text-center small">&copy; 2026 Pars Campus. All Rights Reserved.</div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/jquery@3.5.1/dist/jquery.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.min.js"></script>
</body>
</html>