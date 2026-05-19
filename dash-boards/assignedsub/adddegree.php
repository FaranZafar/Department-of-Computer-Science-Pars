<?php
include_once("../../dbconnection.php");

// 1. HANDLE MESSAGES
$message = $_GET['msg'] ?? null;

// 2. HANDLE DELETE
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $stmt = $con->prepare("DELETE FROM degree WHERE degree_id = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        header("Location: " . $_SERVER['PHP_SELF'] . "?msg=" . urlencode("Degree deleted successfully!"));
        exit();
    } else {
        $message = "Error deleting: " . $con->error;
    }
}

// 3. HANDLE ADD or UPDATE
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['degree_name'];
    $duration = $_POST['duration'];
    $id = $_POST['degree_id'] ?? null; 

    if (!empty($name) && !empty($duration)) {
        if ($id) {
            // UPDATE EXISTING
            $sql = "UPDATE degree SET degree_name = ?, duration = ? WHERE degree_id = ?";
            $stmt = $con->prepare($sql);
            $stmt->bind_param("ssi", $name, $duration, $id);
            $action = "updated";
        } else {
            // INSERT NEW
            $sql = "INSERT INTO degree (degree_name, duration) VALUES (?, ?)";
            $stmt = $con->prepare($sql);
            $stmt->bind_param("ss", $name, $duration);
            $action = "added";
        }

        if ($stmt->execute()) {
            header("Location: " . $_SERVER['PHP_SELF'] . "?msg=" . urlencode("Degree $action successfully!"));
            exit();
        } else {
            $message = "Database Error: " . $con->error;
        }
    }
}

// 4. FETCH ALL DEGREES FOR THE TABLE
$result = $con->query("SELECT * FROM degree ORDER BY degree_id DESC");
$degrees = $result->fetch_all(MYSQLI_ASSOC);

// 5. FETCH DATA FOR EDIT MODE
$editData = null;
if (isset($_GET['edit'])) {
    $editId = $_GET['edit'];
    $stmt = $con->prepare("SELECT * FROM degree WHERE degree_id = ?");
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
    <title>Degree Management</title>
    <link rel="icon" type="image/png" href="../../images/pars.png">
     <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
     
    <style>
        :root { --primary: #4a90e2; --success: #2ecc71; --danger: #e74c3c; --dark: #2c3e50; }
        body { font-family: 'Segoe UI', sans-serif; background: #f4f7f6; padding: 40px; }
        .container { max-width: 900px; margin: auto; background: white; padding: 30px; border-radius: 12px; box-shadow: 0 10px 25px rgba(0,0,0,0.1); }
        h2 { border-bottom: 2px solid #eee; padding-bottom: 10px; color: var(--dark); margin-top: 0; }
        .form-flex { display: flex; gap: 15px; margin-bottom: 30px; align-items: flex-end; flex-wrap: wrap; }
        .form-group { flex: 1; min-width: 200px; }
        label { display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 5px; color: #666; }
        input { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 6px; box-sizing: border-box; }
        .btn { padding: 11px 20px; border: none; border-radius: 6px; cursor: pointer; font-weight: 600; color: white; transition: 0.3s; }
        .btn-add { background: var(--primary); }
        .btn-update { background: var(--success); }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { text-align: left; padding: 12px; border-bottom: 1px solid #eee; }
        th { background: #f8f9fa; color: #555; }
        .badge { padding: 6px 12px; border-radius: 4px; text-decoration: none; font-size: 0.8rem; font-weight: bold; }
        .edit { background: #fff3cd; color: #856404; }
        .delete { background: #f8d7da; color: #721c24; }
        .msg { padding: 15px; background: #d4edda; color: #155724; border-radius: 6px; margin-bottom: 20px; border-left: 5px solid var(--success); }
    </style>
</head>
<body>
 <a href="../coordinator.php" class="badge badge-primary edit">Back</a>
 
<div class="container">

    <h2><?php echo $editData ? 'Edit Degree' : 'Degree Management'; ?></h2>
    
    <?php if($message): ?>
        <div class='msg'><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>

    <form method="POST" action="?">

        <?php if ($editData): ?>
            <input type="hidden" name="degree_id" value="<?php echo $editData['degree_id']; ?>">
        <?php endif; ?>
        
        <div class="form-flex">
            <div class="form-group">
                <label>Degree Name</label>
                <input type="text" name="degree_name" required value="<?php echo $editData['degree_name'] ?? ''; ?>" placeholder="e.g. BS Computer Science">
            </div>
            
            <div class="form-group">
                <label>Duration</label>
                <input type="text" name="duration" required value="<?php echo $editData['duration'] ?? ''; ?>" placeholder="e.g. 4 Years / 8 Semesters">
            </div>

            <div>
                <button type="submit" class="btn <?php echo $editData ? 'btn-update' : 'btn-add'; ?>">
                    <?php echo $editData ? 'Update Degree' : 'Save Degree'; ?>
                </button>
                <?php if($editData): ?>
                    <a href="?" style="margin-left:10px; color:#999; text-decoration:none;">Cancel</a>
                <?php endif; ?>
            </div>
        </div>
    </form>

    <h3>Degree List</h3>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Degree Name</th>
                <th>Duration</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($degrees)): ?>
                <tr><td colspan="4" style="text-align:center; padding:20px; color:#999;">No degrees found.</td></tr>
            <?php else: ?>
                <?php foreach ($degrees as $d): ?>
                <tr>
                    <td>#<?php echo $d['degree_id']; ?></td>
                    <td><strong><?php echo htmlspecialchars($d['degree_name']); ?></strong></td>
                    <td><?php echo htmlspecialchars($d['duration']); ?></td>
                    <td>
                        <a href="?edit=<?php echo $d['degree_id']; ?>" class="badge edit">Edit</a>
                        <a href="?delete=<?php echo $d['degree_id']; ?>" class="badge delete" onclick="return confirm('Delete this degree?')">Delete</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

</body>
</html>