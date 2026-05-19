<?php
include_once("../../../dbconnection.php");

$message = '';

// 1. LOGIC: Handle the Form Submission
if (isset($_POST['submit_attendance'])) {
    $attendance_data = $_POST['attendance'] ?? []; 
    $current_date = date('Y-m-d');
    $success_count = 0;

    foreach ($attendance_data as $staffID => $attendance_value) {
        
        // Check if attendance for this staff on this date already exists
        $checkStmt = $con->prepare("SELECT attId FROM staff_attendance WHERE StaffID = ? AND date = ?");
        $checkStmt->bind_param("ss", $staffID, $current_date);
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result();

        if ($checkResult->num_rows > 0) {
            // UPDATE: If record exists, update the 'value' column
            $updateStmt = $con->prepare("UPDATE staff_attendance SET value = ? WHERE StaffID = ? AND date = ?");
            $updateStmt->bind_param("sss", $attendance_value, $staffID, $current_date);
            $updateStmt->execute();
        } else {
            // INSERT: If no record, create new one using your fields: StaffID, date, value
            $insertStmt = $con->prepare("INSERT INTO staff_attendance (StaffID, date, value) VALUES (?, ?, ?)");
            $insertStmt->bind_param("sss", $staffID, $current_date, $attendance_value);
            $insertStmt->execute();
        }
        $success_count++;
    }
    $message = "<div class='alert alert-success shadow-sm'>Successfully saved attendance for $success_count records!</div>";
}

// 2. QUERY: Fetch teachers for the display table
$query = "SELECT StaffID, FirstName, LastName, Department,Qualification ,joiningDate FROM staff WHERE UserRole='Teacher' AND EmploymentType='Visiting'";
$result = $con->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teacher Attendance </title>
    <link rel="icon" type="image/png" href="../../../images/pars.png">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
    <style>
        body { 
        font-family: 'Inter', sans-serif; 
        background-color: #f0f2f5; 
        }
        .card { 
        border: none; 
        border-radius: 15px; 
        box-shadow: 0 8px 20px rgba(0,0,0,0.06); 
        }
        .thead-custom { 
        background-color: #4e73df; 
        color: white; 
        }
        .radio-box { 
        cursor: pointer; 
        transition: 0.2s; 
        }
        .btn-save { 
        border-radius: 8px; 
        font-weight: 600; 
        padding: 10px 25px; 
        }
    </style>
</head>
<body>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-xl-1z">
            <?php echo $message; ?>
            
            <div class="card bg-white p-4">
                <form action="" method="POST">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div>
                            <h3 class="font-weight-bold text-gray-800">Visiting Staff Attendance</h3>
                            <p class="text-muted">Today's Date: <strong><?php echo date('M d, Y'); ?></strong></p>
                        </div>
                        <button type="submit" name="submit_attendance" class="btn btn-primary btn-save shadow">
                            Save Records
                        </button>
                    </div>

                    <div class="table-responsive">
                         <a href="../teacher-record.php" class="btn btn-outline-primary">Back</a>
                        <br><br>
                        <table class="table table-hover border-bottom">
                            <thead class="thead-custom bg-primary">
                                <tr>
                                    <th>ID</th>
                                    <th>Teacher Name</th>
                                    <th>Department</th>
                                    <th>Qualification</th>
                                    <th class="text-center">Mark Status</th>
                                    <th class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if($result && $result->num_rows > 0): ?>
                                    <?php while($row = $result->fetch_assoc()): ?>
                                        <tr>
                                            <td class="align-middle"><strong><?php echo $row['StaffID']; ?></strong></td>
                                            <td class="align-middle"><?php echo htmlspecialchars($row['FirstName'] . " " . $row['LastName']); ?></td>
                                            <td class="align-middle text-muted"><?php echo htmlspecialchars($row['Department']); ?></td>
                                            <td class="align-middle text-muted"><?php echo htmlspecialchars($row['Qualification']); ?></td>
                                            <td class="text-center">
                                                <div class="btn-group btn-group-toggle" data-toggle="buttons">
                                                    <label class="btn btn-outline-success btn-sm px-3">
                                                        <input type="radio" name="attendance[<?php echo $row['StaffID']; ?>]" value="Present" required> Present
                                                    </label>
                                                    <label class="btn btn-outline-danger btn-sm px-3">
                                                        <input type="radio" name="attendance[<?php echo $row['StaffID']; ?>]" value="Absent" required> Absent
                                                    </label>
                                                </div>
                                            </td>
                                            <td>
                                                <a href="teacher_details.php?id=<?php echo $row['StaffID']; ?>" class="btn btn-sm btn-dark mr-1 shadow-sm">
                                                 <i class="fas fa-user-tie"></i> Profile Detail
                                              </a> 
                                              <a href="view_attendance.php?id=<?php echo $row['StaffID']; ?>" class="btn btn-sm btn-info shadow-sm">View Attendance</a>
                                              <a href="update.php?id=<?php echo $row['StaffID']; ?>" class="btn btn-success">Update</a>
                                              <a href="delete.php?id=<?php echo $row['StaffID']; ?>" class="btn btn-danger">Delete</a>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr><td colspan="4" class="text-center py-5">No teachers found.</td></tr>
                                <?php endif; ?>
                                
                                   
                                
                            </tbody>
                        </table>
                    </div>
                    <a href="add-teacher.php" class="btn btn-primary">Add Teacher</a>   
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>