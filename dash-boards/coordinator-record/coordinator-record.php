<?php if(isset($_GET['msg']) && $_GET['msg'] == 'updated'): ?>
    <div class="alert alert-info">Record updated successfully!</div>
<?php endif; ?>

<?php
include_once("../../dbconnection.php");

$query = "SELECT * FROM staff 
          WHERE UserRole = 'Coordinator' OR UserRole = 'Admin' 
          ORDER BY StaffID ASC";
$result = $con->query($query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Coordinator-record</title>
    <link rel="icon" type="image/png" href="../../images/pars.png">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f8f9fa; }
        .table-container { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
    </style>
</head>
<body>
    <div class="container-fluid mt-5">
        <div class="row justify-content-center">
            <div class="col-lg-11 table-container">
                <h2 class="mb-4 text-center">Admin/Coordinator Records</h2>
                <a href="../coordinator.php" class="btn btn-outline-primary">Back</a>
                <br><br>
                <table class="table table-bordered table-striped">
                    <thead class="thead-custom text-white bg-primary">
                        <tr>
                            <th>Id</th>
                            <th>FirstName</th>
                            <th>LastName</th>
                            <th>Department</th>
                            <th>Designation</th>
                            <th>Qualification</th>
                            <th>Joining Date</th>
                            <th colspan="3" class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if ($result && $result->num_rows > 0) {
                            while($row = $result->fetch_assoc()) {
                                echo "<tr>";
                                echo "<td>" . htmlspecialchars($row['StaffID']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['FirstName']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['LastName']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['Department']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['Designation']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['Qualification']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['joiningDate']) . "</td>";
                                // Fixed: Wrapped the buttons inside echo and used single quotes for the classes
                               echo "<td><a href='update.php?id=" . $row['StaffID'] . "' class='btn btn-sm btn-info'>Update</a></td>";
                               echo "<td><a href='delete.php?id=" . $row['StaffID'] . "' class='btn btn-sm btn-danger'>Delete</a></td>";
                               echo "<td><a href='coordinator_details.php?id=" . $row['StaffID'] . "' class='btn btn-sm btn-dark'>Profile</a></td>";
                            }
                        } else {
                            echo "<tr><td colspan='9' class='text-center'>No records found</td></tr>";
                        }
                        ?>
                    </tbody>
                    
                </table>
                <a href="addcoordinator.php" class="btn btn-primary">Add Coordinator</a>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>