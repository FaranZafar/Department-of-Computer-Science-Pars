<?php
session_start();
include_once("../../dbconnection.php");

// 1. HANDLE MESSAGES
$message = $_GET['msg'] ?? null;

// Capture current search parameters to persist them in URLs
$search_degree = $_GET['f_degree'] ?? '';
$search_semester = $_GET['f_semester'] ?? '';
$search_query_string = "&f_degree=" . urlencode($search_degree) . "&f_semester=" . urlencode($search_semester);

// 2. HANDLE DELETE
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $stmt = $con->prepare("DELETE FROM courses WHERE course_id = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        // Redirect keeping the search filters active
        header("Location: " . $_SERVER['PHP_SELF'] . "?msg=" . urlencode("Course deleted successfully!") . $search_query_string);
        exit();
    } else {
        $message = "Error deleting: " . $con->error;
    }
}

// 3. HANDLE ADD or UPDATE
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $code      = $_POST['course_code'];
    $title     = $_POST['course_title'];
    $cr_hours  = $_POST['credit_hours'];
    $deg_id    = $_POST['degree_id'];
    $sem_id    = $_POST['semester_id'];
    $id        = $_POST['course_id'] ?? null; 

    if (!empty($code) && !empty($title) && !empty($deg_id) && !empty($sem_id)) {
        if ($id) {
            $sql = "UPDATE courses SET course_code = ?, course_title = ?, credit_hours = ?, semester_id = ?, degree_id = ? WHERE course_id = ?";
            $stmt = $con->prepare($sql);
            $stmt->bind_param("sssiii", $code, $title, $cr_hours, $sem_id, $deg_id, $id);
            $action = "updated";
        } else {
            $sql = "INSERT INTO courses (course_code, course_title, credit_hours, semester_id, degree_id) VALUES (?, ?, ?, ?, ?)";
            $stmt = $con->prepare($sql);
            $stmt->bind_param("sssii", $code, $title, $cr_hours, $sem_id, $deg_id);
            $action = "added";
        }

        if ($stmt->execute()) {
            // Redirect keeping the search filters active
            header("Location: " . $_SERVER['PHP_SELF'] . "?msg=" . urlencode("Course $action successfully!") . $search_query_string);
            exit();
        } else {
            $message = "Database Error: " . $con->error;
        }
    }
}

// 4. SEARCH & FILTER LOGIC
$query = "SELECT courses.*, semester.semester_name, degree.degree_name 
          FROM courses 
          LEFT JOIN semester ON courses.semester_id = semester.semester_id 
          LEFT JOIN degree ON courses.degree_id = degree.degree_id 
          WHERE 1=1";

if (!empty($search_degree)) {
    $query .= " AND courses.degree_id = '" . $con->real_escape_string($search_degree) . "'";
}
if (!empty($search_semester)) {
    $query .= " AND courses.semester_id = '" . $con->real_escape_string($search_semester) . "'";
}

$query .= " ORDER BY courses.course_id DESC";
$result = $con->query($query);
$courses = ($result) ? $result->fetch_all(MYSQLI_ASSOC) : [];

// 5. FETCH ALL SEMESTERS & DEGREES FOR DROPDOWNS
$sem_res = $con->query("SELECT * FROM semester ORDER BY semester_name ASC");
$all_semesters = ($sem_res) ? $sem_res->fetch_all(MYSQLI_ASSOC) : [];

$degree_res = $con->query("SELECT * FROM degree ORDER BY degree_name ASC");
$all_degrees = ($degree_res) ? $degree_res->fetch_all(MYSQLI_ASSOC) : [];

// 6. FETCH DATA FOR EDIT MODE
$editData = null;
if (isset($_GET['edit'])) {
    $editId = $_GET['edit'];
    $stmt = $con->prepare("SELECT * FROM courses WHERE course_id = ?");
    $stmt->bind_param("i", $editId);
    $stmt->execute();
    $editData = $stmt->get_result()->fetch_assoc();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Course Management | Pars Campus</title>
    <link rel="icon" type="image/png" href="../../images/pars.png">
    <style>
        :root { --primary: #4a90e2; --success: #2ecc71; --danger: #e74c3c; --dark: #2c3e50; }
        body { font-family: 'Segoe UI', sans-serif; background: #f4f7f6; padding: 40px; color: #333; }
        .container { max-width: 1200px; margin: auto; background: white; padding: 30px; border-radius: 12px; box-shadow: 0 10px 25px rgba(0,0,0,0.1); }
        h2 { border-bottom: 2px solid #eee; padding-bottom: 10px; color: var(--dark); margin-top: 0; }
        .form-flex { display: flex; gap: 15px; margin-bottom: 30px; align-items: flex-end; flex-wrap: wrap; }
        .form-group { flex: 1; min-width: 180px; }
        label { display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 5px; color: #666; }
        input, select { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 6px; box-sizing: border-box; }
        .btn { padding: 11px 20px; border: none; border-radius: 6px; cursor: pointer; font-weight: 600; color: white; transition: 0.3s; }
        .btn-add { background: var(--primary); }
        .btn-update { background: var(--success); }
        .btn-search { background: var(--dark); }
        .btn-reset { background: #95a5a6; text-decoration: none; display: inline-block; padding: 11px 20px; border-radius: 6px; color: white; }
        .search-box { background: #f9f9f9; padding: 20px; border-radius: 8px; border: 1px solid #eee; margin-bottom: 25px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { text-align: left; padding: 12px; border-bottom: 1px solid #eee; }
        th { background: #f8f9fa; color: #555; }
        .badge { padding: 6px 12px; border-radius: 4px; text-decoration: none; font-size: 0.8rem; font-weight: bold; }
        .edit { background: #fff3cd; color: #856404; }
        .delete { background: #f8d7da; color: #721c24; }
        .msg { padding: 15px; background: #d4edda; color: #155724; border-radius: 6px; margin-bottom: 20px; border-left: 5px solid var(--success); }
        code { background: #f1f1f1; padding: 2px 6px; border-radius: 4px; color: var(--danger); }
    </style>
</head>
<body>

<div class="container">
    <h2><?php echo $editData ? 'Edit Course' : 'Course Management'; ?></h2>
    
    <?php if($message): ?>
        <div class='msg'><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>

    <!-- ADD/EDIT FORM -->
    <form method="POST" action="?<?php echo ltrim($search_query_string, '&'); ?>">
        <?php if ($editData): ?>
            <input type="hidden" name="course_id" value="<?php echo $editData['course_id']; ?>">
        <?php endif; ?>

        <div class="form-flex">
            <div class="form-group">
                <label>Course Code</label>
                <input type="text" name="course_code" required value="<?php echo $editData['course_code'] ?? ''; ?>">
            </div>
            <div class="form-group">
                <label>Course Title</label>
                <input type="text" name="course_title" required value="<?php echo $editData['course_title'] ?? ''; ?>">
            </div>
            <div class="form-group">
                <label>Credit Hours</label>
                <input type="text" name="credit_hours" required value="<?php echo $editData['credit_hours'] ?? ''; ?>">
            </div>
            <div class="form-group">
                <label>Degree</label>
                <select name="degree_id" id="degree_select" required onchange="filterOptions('degree_select', 'semester_select')">
                    <option value="">-- Select Degree --</option>
                    <?php foreach ($all_degrees as $d): ?>
                        <option value="<?php echo $d['degree_id']; ?>" <?php echo (isset($editData) && $editData['degree_id'] == $d['degree_id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($d['degree_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Semester</label>
                <select name="semester_id" id="semester_select" required>
                    <option value="">-- Choose Semester --</option>
                    <?php foreach ($all_semesters as $s): ?>
                        <option value="<?php echo $s['semester_id']; ?>" data-degree="<?php echo $s['degree_id']; ?>" <?php echo (isset($editData) && $editData['semester_id'] == $s['semester_id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($s['semester_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="btn <?php echo $editData ? 'btn-update' : 'btn-add'; ?>">
                <?php echo $editData ? 'Update' : 'Save'; ?>
            </button>
        </div>
    </form>

    <!-- SEARCH BOX -->
    <div class="search-box">
        <form method="GET" action="?">
            <div class="form-flex" style="margin-bottom:0">
                <div class="form-group">
                    <label>Filter Degree</label>
                    <select name="f_degree" id="search_degree_select" onchange="filterOptions('search_degree_select', 'search_semester_select')">
                        <option value="">All Degrees</option>
                        <?php foreach ($all_degrees as $d): ?>
                            <option value="<?php echo $d['degree_id']; ?>" <?php echo ($search_degree == $d['degree_id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($d['degree_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Filter Semester</label>
                    <select name="f_semester" id="search_semester_select">
                        <option value="">All Semesters</option>
                        <?php foreach ($all_semesters as $s): ?>
                            <option value="<?php echo $s['semester_id']; ?>" data-degree="<?php echo $s['degree_id']; ?>" <?php echo ($search_semester == $s['semester_id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($s['semester_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div style="display:flex; gap:10px;">
                    <button type="submit" class="btn btn-search">Search</button>
                    <a href="?" class="btn-reset">Reset</a>
                </div>
            </div>
        </form>
    </div>

   <!-- TABLE -->
    <table>
        <thead>
            <tr>
                <th>Code</th>
                <th>Title</th>
                <th>Cr. Hours</th> <!-- Added Header -->
                <th>Degree</th>
                <th>Semester</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($courses)): ?>
                <tr><td colspan="6" style="text-align:center; padding:20px; color:#999;">No matching courses found.</td></tr>
            <?php else: ?>
                <?php foreach ($courses as $c): ?>
                <tr>
                    <td><code><?php echo htmlspecialchars($c['course_code']); ?></code></td>
                    <td><?php echo htmlspecialchars($c['course_title']); ?></td>
                    <td><?php echo htmlspecialchars($c['credit_hours']); ?></td> <!-- Added Data Cell -->
                    <td><?php echo htmlspecialchars($c['degree_name']); ?></td>
                    <td><?php echo htmlspecialchars($c['semester_name']); ?></td>
                    <td>
                        <a href="?edit=<?php echo $c['course_id'] . $search_query_string; ?>" class="badge edit">Edit</a>
                        <a href="?delete=<?php echo $c['course_id'] . $search_query_string; ?>" class="badge delete" onclick="return confirm('Delete?')">Delete</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<script>
function filterOptions(degreeSelectId, semesterSelectId) {
    const degreeId = document.getElementById(degreeSelectId).value;
    const semesterSelect = document.getElementById(semesterSelectId);
    const options = semesterSelect.querySelectorAll('option');

    options.forEach(option => {
        const optionDegreeId = option.getAttribute('data-degree');
        if (option.value === "" || degreeId === "" || optionDegreeId === degreeId) {
            option.style.display = "block";
        } else {
            option.style.display = "none";
        }
    });
}
window.onload = function() {
    filterOptions('degree_select', 'semester_select');
    filterOptions('search_degree_select', 'search_semester_select');
};
</script>
</body>
</html>