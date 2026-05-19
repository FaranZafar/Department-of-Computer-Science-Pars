<?php
// Include your connection file
include_once("../../dbconnection.php");

// 1. HANDLE MESSAGES
$message = $_GET['msg'] ?? null;

// 2. HANDLE DELETE
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $stmt = $con->prepare("DELETE FROM semester WHERE semester_id = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        header("Location: " . $_SERVER['PHP_SELF'] . "?msg=" . urlencode("Semester deleted successfully!"));
        exit();
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
            $sql = "UPDATE semester SET semester_name = ?, degree_id = ?, year = ? WHERE semester_id = ?";
            $stmt = $con->prepare($sql);
            $stmt->bind_param("sisi", $name, $deg_id, $year, $id);
            $action = "updated";
        } else {
            $sql = "INSERT INTO semester (semester_name, degree_id, year) VALUES (?, ?, ?)";
            $stmt = $con->prepare($sql);
            $stmt->bind_param("sis", $name, $deg_id, $year);
            $action = "added";
        }

        if ($stmt->execute()) {
            header("Location: " . $_SERVER['PHP_SELF'] . "?msg=" . urlencode("Semester $action successfully!"));
            exit();
        }
    }
}

// 4. SEARCH & FETCH LOGIC
$search_name = $_GET['search_name'] ?? '';
$search_degree = $_GET['search_degree'] ?? '';

$query = "SELECT semester.*, degree.degree_name 
          FROM semester 
          INNER JOIN degree ON semester.degree_id = degree.degree_id WHERE 1=1";

if (!empty($search_name)) {
    $query .= " AND semester.semester_name LIKE '%" . $con->real_escape_string($search_name) . "%'";
}
if (!empty($search_degree)) {
    $query .= " AND semester.degree_id = '" . $con->real_escape_string($search_degree) . "'";
}

$query .= " ORDER BY semester.semester_id DESC";
$result = $con->query($query);

// Ensure $semesters is always an array, even if empty
$semesters = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];

// 5. FETCH ALL DEGREES
$deg_res = $con->query("SELECT * FROM degree ORDER BY degree_name ASC");
$all_degrees = $deg_res ? $deg_res->fetch_all(MYSQLI_ASSOC) : [];

// 6. FETCH DATA FOR EDIT MODE
$editData = null;
if (isset($_GET['edit'])) {
    $editId = $_GET['edit'];
    $stmt = $con->prepare("SELECT * FROM semester WHERE semester_id = ?");
    $stmt->bind_param("i", $editId);
    $stmt->execute();
    $editData = $stmt->get_result()->fetch_assoc();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Semester Management | Dashboard</title>
    <link rel="icon" type="image/png" href="../../images/pars.png">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --primary: #4361ee;
            --primary-light: #4895ef;
            --success: #4cc9f0;
            --danger: #f72585;
            --dark: #2b2d42;
            --bg: #f8f9fc;
            --card-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.1);
        }

        body { font-family: 'Inter', sans-serif; background-color: var(--bg); color: var(--dark); margin: 0; padding: 20px; }
        .dashboard-container { max-width: 1200px; margin: auto; }
        .page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
        .stats-row { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px; }
        .stat-card { background: white; padding: 20px; border-radius: 12px; box-shadow: var(--card-shadow); border-left: 4px solid var(--primary); display: flex; align-items: center; }
        .stat-card i { font-size: 2rem; margin-right: 15px; opacity: 0.3; }
        .card { background: white; border-radius: 12px; box-shadow: var(--card-shadow); padding: 25px; margin-bottom: 30px; border: none; }
        .form-flex { display: flex; gap: 20px; flex-wrap: wrap; align-items: flex-end; }
        .form-group { flex: 1; min-width: 200px; }
        label { display: block; font-size: 0.75rem; font-weight: 700; margin-bottom: 8px; color: #555; text-transform: uppercase; }
        input, select { width: 100%; padding: 12px 15px; border: 1px solid #e3e6f0; border-radius: 8px; background-color: #fcfcfd; }
        .btn { padding: 12px 25px; border-radius: 8px; font-weight: 600; border: none; cursor: pointer; display: inline-flex; align-items: center; gap: 8px; }
        .btn-primary { background: var(--primary); color: white; }
        .btn-success { background: var(--success); color: white; }
        .btn-dark { background: var(--dark); color: white; }
        .btn-outline { background: #eaecf4; color: #5a5c69; text-decoration: none; }
        .table-container { background: white; border-radius: 12px; box-shadow: var(--card-shadow); overflow: hidden; }
        table { width: 100%; border-collapse: collapse; }
        th { background: #f8f9fc; padding: 15px 20px; text-align: left; font-size: 0.75rem; text-transform: uppercase; color: #858796; border-bottom: 1px solid #e3e6f0; }
        td { padding: 18px 20px; border-bottom: 1px solid #f1f1f1; }
        .badge-id { background: #f1f3f9; color: #5a5c69; padding: 4px 8px; border-radius: 4px; font-weight: 600; }
        .badge-action { padding: 6px 12px; border-radius: 6px; font-size: 0.75rem; text-decoration: none; }
        .alert { padding: 15px 20px; border-radius: 10px; margin-bottom: 25px; display: flex; align-items: center; gap: 12px; background: #d1e7dd; color: #0f5132; border-left: 5px solid #198754; }
    </style>
</head>
<body>

<div class="dashboard-container">
    
    <div class="page-header">
        <h2>Semester Management</h2>
        <div><?php echo date('D, M d, Y'); ?></div>
    </div>
     <a href="../coordinator.php" class="btn btn-outline-primary ">Back</a>
    
    <!-- Stats Row -->
    <div class="stats-row">
        <div class="stat-card">
            <!-- <i class="fa-solid fa-list-check"></i> -->
            <div>
                <span>Total Records</span>
                <!-- FIXED: Changed $semester to $semesters -->
                <b><?php echo count($semesters); ?></b>
            </div>
        </div>
        <div class="stat-card" style="border-left-color: var(--success);">
            <!-- <i class="fa-solid fa-building-columns"></i> -->
            <div>
                <span>Active Degrees</span>
                <b><?php echo count($all_degrees); ?></b>
            </div>
        </div>
    </div>

    <?php if($message): ?>
        <div class="alert">
            <i class="fa-solid fa-circle-check"></i>
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>

    <!-- Management Card -->
    <div class="card">
        <form method="POST" action="?">
            <?php if ($editData): ?>
                <input type="hidden" name="semester_id" value="<?php echo $editData['semester_id']; ?>">
            <?php endif; ?>

            <div class="form-flex">
                <div class="form-group">
                    <label>Semester Name</label>
                    <input type="text" name="semester_name" required value="<?php echo $editData['semester_name'] ?? ''; ?>" placeholder="e.g. 1st Semester">
                </div>
                <hr>
                <div class="form-group">
                    <label>Degree</label>
                    <select name="degree_id" required>
                        <option value="">-- Choose Degree --</option>
                        <?php foreach ($all_degrees as $d): ?>
                            <option value="<?php echo $d['degree_id']; ?>" <?php echo (isset($editData) && $editData['degree_id'] == $d['degree_id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($d['degree_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Year</label>
                    <input type="text" name="academic_year" required value="<?php echo $editData['year'] ?? ''; ?>" placeholder="2025-2026">
                </div>
                <hr>
                <button type="submit" class="btn btn-primary">
                    <i class="fa-solid fa-save"></i> <?php echo $editData ? 'Update' : 'Save'; ?>
                </button>
            </div>
        </form>
    </div>

    <!-- Search Card -->
    <div class="card" style="background: #f1f4f9;">
        <form method="GET" action="?" class="form-flex">
            <div class="form-group">
                <label>Keyword</label>
                <input type="text" name="search_name" value="<?php echo htmlspecialchars($search_name); ?>" placeholder="Search name...">
            </div>
            <hr>
            <div class="form-group">
                <label>Filter Degree</label>
                <select name="search_degree">
                    <option value="">All Degrees</option>
                    <?php foreach ($all_degrees as $d): ?>
                        <option value="<?php echo $d['degree_id']; ?>" <?php echo ($search_degree == $d['degree_id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($d['degree_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div style="display:flex; gap:10px;">
                <button type="submit" class="btn btn-dark">Filter</button>
                <a href="?" class="btn btn-outline">Reset</a>
            </div>
        </form>
    </div>

    <!-- Table -->
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Semester</th>
                    <th>Degree</th>
                    <th>Year</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($semesters as $s): ?>
                <tr>
                    <td><span class="badge-id">#<?php echo $s['semester_id']; ?></span></td>
                    <td><b><?php echo htmlspecialchars($s['semester_name']); ?></b></td>
                    <td><?php echo htmlspecialchars($s['degree_name']); ?></td>
                    <td><?php echo htmlspecialchars($s['year']); ?></td>
                    <td>
                        <a href="?edit=<?php echo $s['semester_id']; ?>" style="color:var(--primary); margin-right:10px;"><i class="fa-solid fa-edit"></i></a>
                        <a href="?delete=<?php echo $s['semester_id']; ?>" style="color:var(--danger);" onclick="return confirm('Delete this?')"><i class="fa-solid fa-trash"></i></a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>