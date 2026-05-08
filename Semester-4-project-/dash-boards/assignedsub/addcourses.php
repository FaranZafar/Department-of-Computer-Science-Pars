<?php
include_once("../../dbconnection.php");

// 1. HANDLE MESSAGES
$message = $_GET['msg'] ?? null;

// 2. HANDLE DELETE
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $stmt = $con->prepare("DELETE FROM courses WHERE course_id = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        header("Location: " . $_SERVER['PHP_SELF'] . "?msg=" . urlencode("Course deleted successfully!"));
        exit();
    } else {
        $message = "Error deleting: " . $con->error;
    }
}

// 3. HANDLE ADD or UPDATE
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $code     = $_POST['course_code'];
    $title    = $_POST['course_title'];
    $cr_hours = $_POST['credit_hours'];
    $sem_id   = $_POST['semester_id'];
    $id       = $_POST['course_id'] ?? null; 

    if (!empty($code) && !empty($title) && !empty($sem_id)) {
        if ($id) {
            // UPDATE EXISTING
            $sql = "UPDATE courses SET course_code = ?, course_title = ?, credit_hours = ?, semester_id = ? WHERE course_id = ?";
            $stmt = $con->prepare($sql);
            $stmt->bind_param("sssii", $code, $title, $cr_hours, $sem_id, $id);
            $action = "updated";
        } else {
            // INSERT NEW
            $sql = "INSERT INTO courses (course_code, course_title, credit_hours, semester_id) VALUES (?, ?, ?, ?)";
            $stmt = $con->prepare($sql);
            $stmt->bind_param("sssi", $code, $title, $cr_hours, $sem_id);
            $action = "added";
        }

        if ($stmt->execute()) {
            header("Location: " . $_SERVER['PHP_SELF'] . "?msg=" . urlencode("Course $action successfully!"));
            exit();
        } else {
            $message = "Database Error: " . $con->error;
        }
    }
}

// 4. FETCH ALL COURSES (JOINING SEMESTER TABLE)
$query = "SELECT courses.*, semester.semester_name 
          FROM courses 
          INNER JOIN semester ON courses.semester_id = semester.semester_id 
          ORDER BY courses.course_id DESC";
$result = $con->query($query);
$courses = $result->fetch_all(MYSQLI_ASSOC);

// 5. FETCH ALL SEMESTERS FOR THE DROPDOWN
$sem_res = $con->query("SELECT * FROM semester ORDER BY semester_name ASC");
$all_semesters = $sem_res->fetch_all(MYSQLI_ASSOC);

// 6. FETCH DATA FOR EDIT MODE
$editData = null;
if (isset($_GET['edit'])) {
    $editId = $_GET['edit'];
    $stmt = $con->prepare("SELECT * FROM courses WHERE course_id = ?");
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
    <title>Course Management</title>
    <link rel="icon" type="image/png" href="../../images/pars.png">
    <style>
        :root { --primary: #4a90e2; --success: #2ecc71; --danger: #e74c3c; --dark: #2c3e50; }
        body { font-family: 'Segoe UI', sans-serif; background: #f4f7f6; padding: 40px; color: #333; }
        .container { max-width: 1100px; margin: auto; background: white; padding: 30px; border-radius: 12px; box-shadow: 0 10px 25px rgba(0,0,0,0.1); }
        h2 { border-bottom: 2px solid #eee; padding-bottom: 10px; color: var(--dark); margin-top: 0; }
        .form-flex { display: flex; gap: 15px; margin-bottom: 30px; align-items: flex-end; flex-wrap: wrap; }
        .form-group { flex: 1; min-width: 180px; }
        label { display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 5px; color: #666; }
        input, select { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 6px; box-sizing: border-box; }
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

<div class="container">
    <h2><?php echo $editData ? 'Edit Course' : 'Course Management'; ?></h2>
    
    <?php if($message): ?>
        <div class='msg'><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>

    <form method="POST" action="?">
        <?php if ($editData): ?>
            <input type="hidden" name="course_id" value="<?php echo $editData['course_id']; ?>">
        <?php endif; ?>

        <div class="form-flex">
            <div class="form-group">
                <label>Course Code</label>
                <input type="text" name="course_code" required value="<?php echo $editData['course_code'] ?? ''; ?>" placeholder="e.g. CS-410">
            </div>

            <div class="form-group">
                <label>Course Title</label>
                <input type="text" name="course_title" required value="<?php echo $editData['course_title'] ?? ''; ?>" placeholder="e.g. Database Systems">
            </div>

            <div class="form-group">
                <label>Credit Hours</label>
                <input type="text" name="credit_hours" required value="<?php echo $editData['credit_hours'] ?? ''; ?>" placeholder="e.g. 3(3-0)">
            </div>
            
            <div class="form-group">
                <label>Semester</label>
                <select name="semester_id" required>
                    <option value="">-- Assign Semester --</option>
                    <?php foreach ($all_semesters as $s): ?>
                        <option value="<?php echo $s['semester_id']; ?>" 
                            <?php echo (isset($editData) && $editData['semester_id'] == $s['semester_id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($s['semester_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <button type="submit" class="btn <?php echo $editData ? 'btn-update' : 'btn-add'; ?>">
                    <?php echo $editData ? 'Update Course' : 'Save Course'; ?>
                </button>
            </div>
        </div>
    </form>

    <h3>Course List</h3>
    <table>
        <thead>
            <tr>
                <th>Code</th>
                <th>Course Title</th>
                <th>Credit Hours</th>
                <th>Semester</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($courses)): ?>
                <tr><td colspan="5" style="text-align:center; padding:20px; color:#999;">No courses found.</td></tr>
            <?php else: ?>
                <?php foreach ($courses as $c): ?>
                <tr>
                    <td><code><?php echo htmlspecialchars($c['course_code']); ?></code></td>
                    <td><strong><?php echo htmlspecialchars($c['course_title']); ?></strong></td>
                    <td><?php echo htmlspecialchars($c['credit_hours']); ?></td>
                    <td><span style="color: var(--primary); font-weight:600;"><?php echo htmlspecialchars($c['semester_name']); ?></span></td>
                    <td>
                        <a href="?edit=<?php echo $c['course_id']; ?>" class="badge edit">Edit</a>
                        <a href="?delete=<?php echo $c['course_id']; ?>" class="badge delete" onclick="return confirm('Delete this course?')">Delete</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

</body>
</html>