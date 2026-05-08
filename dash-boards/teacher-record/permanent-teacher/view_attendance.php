<?php
include_once("../../../dbconnection.php");

// Get the specific StaffID from the URL
$staffID = isset($_GET['id']) ? $_GET['id'] : null;

if (!$staffID) {
    die("Error: No Teacher ID provided.");
}

// Fetch Teacher Details (Names)
$nameQuery = $con->prepare("SELECT FirstName, LastName, Department FROM staff WHERE StaffID = ?");
$nameQuery->bind_param("s", $staffID);
$nameQuery->execute();
$teacher = $nameQuery->get_result()->fetch_assoc();

// Fetch Attendance History for this ID only
$historyQuery = $con->prepare("SELECT date, value FROM staff_attendance WHERE StaffID = ? ORDER BY date DESC");
$historyQuery->bind_param("s", $staffID);
$historyQuery->execute();
$history = $historyQuery->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Teacher History</title>
    <link rel="icon" type="image/png" href="../../../images/pars.png">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">
    <div class="container py-5">
        <div class="card shadow border-0">
            <div class="card-header bg-primary text-white p-4">
                <h3 class="mb-0">History: <?php echo htmlspecialchars($teacher['FirstName'] . " " . $teacher['LastName']); ?></h3>
                <small><?php echo htmlspecialchars($teacher['Department']); ?> Department | ID: #<?php echo htmlspecialchars($staffID); ?></small>
            </div>
            <div class="card-body">
                <table class="table table-hover">
                    <thead class="thead-light">
                        <tr>
                            <th>Date</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if($history->num_rows > 0): ?>
                            <?php while($row = $history->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo date("M d, Y", strtotime($row['date'])); ?></td>
                                    <td>
                                        <span class="badge badge-pill <?php echo ($row['value'] == 'Present') ? 'badge-success' : 'badge-danger'; ?> px-3 py-2">
                                            <?php echo $row['value']; ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="2" class="text-center text-muted py-4">No records found for this teacher.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
                <a href="javascript:history.back()" class="btn btn-secondary mt-3">Go Back</a>
            </div>
        </div>
    </div>
</body>
</html>