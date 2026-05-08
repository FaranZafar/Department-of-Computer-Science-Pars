<?php
// Include your connection file ($con variable should be inside here)
include_once("../../dbconnection.php");

// 1. HANDLE MESSAGES
$message = $_GET['msg'] ?? null;

// 2. HANDLE DELETE
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    
    // Prepare and Bind
    $stmt = $con->prepare("DELETE FROM semester WHERE semester_id = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        header("Location: " . $_SERVER['PHP_SELF'] . "?msg=" . urlencode("Semester deleted successfully!"));
        exit();
    } else {
        $message = "Error deleting: " . $con->error;
    }
}

// 3. HANDLE ADD or UPDATE
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['semester_name'];
    $deg_id = $_POST['degree_id']; 
    $year = $_POST['academic_year'];
    $id   = $_POST['semester_id'] ?? null; 

    if (!empty($name) && !empty($deg_id)) {
        if ($id) {
            // UPDATE EXISTING
            $sql = "UPDATE semester SET semester_name = ?, degree_id = ?, year = ? WHERE semester_id = ?";
            $stmt = $con->prepare($sql);
            $stmt->bind_param("sisi", $name, $deg_id, $year, $id);
            $action = "updated";
        } else {
            // INSERT NEW
            $sql = "INSERT INTO semester (semester_name, degree_id, year) VALUES (?, ?, ?)";
            $stmt = $con->prepare($sql);
            $stmt->bind_param("sis", $name, $deg_id, $year);
            $action = "added";
        }

        if ($stmt->execute()) {
            header("Location: " . $_SERVER['PHP_SELF'] . "?msg=" . urlencode("Semester $action successfully!"));
            exit();
        } else {
            $message = "Database Error: " . $con->error;
        }
    }
}

// 4. FETCH ALL SEMESTERS (JOINING DEGREE TABLE)
// This ensures we see the degree name in the table instead of just an ID number
$query = "SELECT semester.*, degree.degree_name 
          FROM semester 
          INNER JOIN degree ON semester.degree_id = degree.degree_id 
          ORDER BY semester.semester_id DESC";
$result = $con->query($query);
$semesters = $result->fetch_all(MYSQLI_ASSOC);

// 5. FETCH ALL DEGREES FOR THE DROPDOWN MENU
$deg_res = $con->query("SELECT * FROM degree ORDER BY degree_name ASC");
$all_degrees = $deg_res->fetch_all(MYSQLI_ASSOC);

// 6. FETCH DATA FOR EDIT MODE (Pre-filling the form)
$editData = null;
if (isset($_GET['edit'])) {
    $editId = $_GET['edit'];
    $stmt = $con->prepare("SELECT * FROM semester WHERE semester_id = ?");
    $stmt->bind_param("i", $editId);
    $stmt->execute();
    $res = $stmt->get_result(); 
    $editData = $res->fetch_assoc();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Semester Management</title>
    <link rel="icon" type="image/png" href="../../images/pars.png">

    <style>
        :root { --primary: #4a90e2; --success: #2ecc71; --danger: #e74c3c; --dark: #2c3e50; }
        body { font-family: 'Segoe UI', sans-serif; background: #f4f7f6; color: #333; margin: 0; padding: 40px; }
        .container { max-width: 1100px; margin: auto; background: white; padding: 30px; border-radius: 12px; box-shadow: 0 10px 25px rgba(0,0,0,0.1); }
        
        h2 { border-bottom: 2px solid #eee; padding-bottom: 10px; color: var(--dark); margin-top: 0; }
        
        /* Form Styling */
        .form-flex { display: flex; gap: 15px; margin-bottom: 30px; align-items: flex-end; flex-wrap: wrap; }
        .form-group { flex: 1; min-width: 220px; }
        label { display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 5px; color: #666; }
        input, select { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 6px; box-sizing: border-box; outline: none; }
        input:focus, select:focus { border-color: var(--primary); }
        
        .btn { padding: 11px 20px; border: none; border-radius: 6px; cursor: pointer; font-weight: 600; color: white; transition: 0.3s; }
        .btn-add { background: var(--primary); }
        .btn-update { background: var(--success); }
        .btn:hover { opacity: 0.9; transform: translateY(-1px); }

        /* Table Styling */
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th { background: #f8f9fa; text-align: left; padding: 12px; border-bottom: 2px solid #eee; color: #555; }
        td { padding: 12px; border-bottom: 1px solid #eee; }
        tr:hover { background: #fafafa; }

        .badge { padding: 6px 12px; border-radius: 4px; text-decoration: none; font-size: 0.8rem; font-weight: bold; display: inline-block; }
        .edit { background: #fff3cd; color: #856404; margin-right: 5px; }
        .delete { background: #f8d7da; color: #721c24; }
        .msg { padding: 15px; background: #d4edda; color: #155724; border-radius: 6px; margin-bottom: 20px; border-left: 5px solid var(--success); }
    </style>
</head>
<body>

<div class="container">
    <h2><?php echo $editData ? 'Edit Semester' : 'Semester Management'; ?></h2>
    
    <!-- Success/Error Messages -->
    <?php if($message): ?>
        <div class='msg'><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>

    <!-- ADD / EDIT FORM -->
    <form method="POST" action="?">
        <?php if ($editData): ?>
            <input type="hidden" name="semester_id" value="<?php echo $editData['semester_id']; ?>">
        <?php endif; ?>

        <div class="form-flex">
            <div class="form-group">
                <label>Semester Name</label>
                <input type="text" name="semester_name" required value="<?php echo $editData['semester_name'] ?? ''; ?>" placeholder="e.g. 1st Semester">
            </div>
            
            <div class="form-group">
                <label>Select Degree Program</label>
                <select name="degree_id" required>
                    <option value="">-- Choose Degree --</option>
                    <?php foreach ($all_degrees as $d): ?>
                        <option value="<?php echo $d['degree_id']; ?>" 
                            <?php echo (isset($editData) && $editData['degree_id'] == $d['degree_id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($d['degree_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label>Academic Year</label>
                <input type="text" name="academic_year" required value="<?php echo $editData['year'] ?? ''; ?>" placeholder="e.g. 2025-2026">
            </div>

            <div style="padding-bottom: 2px;">
                <button type="submit" class="btn <?php echo $editData ? 'btn-update' : 'btn-add'; ?>">
                    <?php echo $editData ? 'Update Record' : 'Save Semester'; ?>
                </button>
                <?php if($editData): ?>
                    <a href="?" style="margin-left:10px; color:#999; text-decoration:none; font-size:0.9rem;">Cancel</a>
                <?php endif; ?>
            </div>
        </div>
    </form>

    <!-- SEMESTERS LIST -->
    <h3>Current Semester Records</h3>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Semester</th>
                <th>Degree Program</th>
                <th>Year</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($semesters)): ?>
                <tr><td colspan="5" style="text-align:center; padding:30px; color:#999;">No semesters added yet.</td></tr>
            <?php else: ?>
                <?php foreach ($semesters as $s): ?>
                <tr>
                    <td>#<?php echo $s['semester_id']; ?></td>
                    <td><strong><?php echo htmlspecialchars($s['semester_name']); ?></strong></td>
                    <td><?php echo htmlspecialchars($s['degree_name']); ?></td>
                    <td><?php echo htmlspecialchars($s['year']); ?></td>
                    <td>
                        <a href="?edit=<?php echo $s['semester_id']; ?>" class="badge edit">Edit</a>
                        <a href="?delete=<?php echo $s['semester_id']; ?>" class="badge delete" onclick="return confirm('Are you sure you want to delete this semester?')">Delete</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

</body>
</html>