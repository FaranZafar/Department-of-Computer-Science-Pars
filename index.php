<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pars Campus</title>
    <link rel="icon" type="image/png" href="./images/pars.png">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    
    <style>
        body, html {
            margin: 0;
            padding: 0;
            height: 100%;
            overflow: hidden; /* Prevents scrolling during load */
        }

        .loader-container {
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            background-color: #ffffff; /* Change to your brand color */
        }

        /* Optional: Add a simple fade-in animation for the logo */
        .loader-container img {
            width: 200px; /* Control the size here */
            height: auto;
            image-rendering: -webkit-optimize-contrast;
             animation: pulse 1.5s infinite;
        }

        @keyframes pulse {
            0% { transform: scale(1); opacity: 1; }
            50% { transform: scale(1.05); opacity: 0.8; }
            100% { transform: scale(1); opacity: 1; }
        }
    </style> 
</head>
<body>

    <div class="loader-container">
        <img src="./images/pars.png" alt="logo">
    </div>

    <script>
        // Set the delay in milliseconds (e.g., 3000ms = 3 seconds)
        const delay = 8000; 
        const nextPage = "./homepage/home.php"; // Replace with your actual next page file

        setTimeout(() => {
            window.location.href = nextPage;
        }, delay);
    </script>

    <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>