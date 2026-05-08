<?php
// 1. Connection to the database
include_once("../../../dbconnection.php");

// 2. Check if the ID is actually set in the URL
if (isset($_GET['id'])) {
    $id = mysqli_real_escape_string($con, $_GET['id']);

    // 3. Prepare the Delete Query
    // Make sure 'StaffID' matches the column name in your database table
    $query = "DELETE FROM staff WHERE StaffID = '$id'";

    if ($con->query($query)) {
        // 4. Success: Redirect back to the records page with a success message
        header("Location: pteacher-record.php?msg=deleted");
        exit();
    } else {
        // 5. Error: Show what went wrong
        echo "Error deleting record: " . $con->error;
    }
} else {
    // If someone tries to access delete.php directly without an ID
    header("Location: pteacher-record.php");
    exit();
}
?>