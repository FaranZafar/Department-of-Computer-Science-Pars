<?php
include_once("../../dbconnection.php");

// 1. Validate ID exists in the URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: manage_sections.php?msg=InvalidID");
    exit();
}

$id = $_GET['id'];

// HANDLE UPDATE
if (isset($_POST['update_section'])) {
    $name = $_POST['section_name'];
    $sem_id = $_POST['semester_id'];
    
    // Using prepared statements for security
    $stmt = $con->prepare("UPDATE sections SET section_name = ?, semester_id = ? WHERE section_id = ?");
    $stmt->bind_param("sii", $name, $sem_id, $id);
    
    if($stmt->execute()) {
        $stmt->close();
        header("Location: manage_sections.php?msg=Updated"); 
        exit();
    } else {
        $error = "Update failed: " . $con->error;
    }
}

// FETCH CURRENT DATA (Securely)
$stmt_fetch = $con->prepare("SELECT * FROM sections WHERE section_id = ?");
$stmt_fetch->bind_param("i", $id);
$stmt_fetch->execute();
$result = $stmt_fetch->get_result();
$section = $result->fetch_assoc();

// If no section found with that ID, redirect back
if (!$section) {
    header("Location: manage_sections.php?msg=NotFound");
    exit();
}

// FETCH SEMESTERS for dropdown
$semesters_query = $con->query("SELECT * FROM semester"); // Double-check if table is 'semester' or 'semesters'
$semesters = $semesters_query->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Section</title>
    <link rel="icon" type="image/png" href="../../images/pars.png">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
</head>
<body class="bg-light p-5">

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card shadow border-0">
                <div class="card-header bg-warning font-weight-bold text-dark">Edit Section</div>
                <div class="card-body">
                    <?php if(isset($error)): ?>
                        <div class="alert alert-danger"><?= $error ?></div>
                    <?php endif; ?>

                    <form method="POST">
                        <div class="form-group">
                            <label class="font-weight-bold">Section Name</label>
                            <input type="text" name="section_name" class="form-control" 
                                   value="<?= htmlspecialchars($section['section_name']) ?>" required>
                        </div>
                        <div class="form-group">
                            <label class="font-weight-bold">Assign to Semester</label>
                            <select name="semester_id" class="form-control" required>
                                <option value="">-- Select Semester --</option>
                                <?php foreach($semesters as $sem): ?>
                                    <option value="<?= $sem['semester_id'] ?>" 
                                        <?= ($sem['semester_id'] == $section['semester_id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($sem['semester_name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <hr>
                        <button type="submit" name="update_section" class="btn btn-primary btn-block">Update Changes</button>
                        <a href="manage_sections.php" class="btn btn-outline-secondary btn-block">Cancel</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>