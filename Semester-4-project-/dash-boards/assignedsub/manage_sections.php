<?php
include_once("../../dbconnection.php");

$message = $_GET['msg'] ?? null;
$error = null;

// --- 1. HANDLE FORM SUBMISSIONS (CREATE & UPDATE) ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $section_name = $_POST['section_name'];
    $semester_id  = $_POST['semester_id'];
    
    if (isset($_POST['save_section'])) {
        // CREATE Logic
        $stmt = $con->prepare("INSERT INTO sections (section_name, semester_id) VALUES (?, ?)");
        $stmt->bind_param("si", $section_name, $semester_id);
        if ($stmt->execute()) {
            header("Location: manage_sections.php?msg=added"); exit();
        } else { $error = "Error adding record: " . $con->error; }
    } 
    elseif (isset($_POST['update_section'])) {
        // UPDATE Logic
        $section_id = $_POST['section_id'];
        $stmt = $con->prepare("UPDATE sections SET section_name = ?, semester_id = ? WHERE section_id = ?");
        $stmt->bind_param("sii", $section_name, $semester_id, $section_id);
        if ($stmt->execute()) {
            header("Location: manage_sections.php?msg=updated"); exit();
        } else { $error = "Error updating record: " . $con->error; }
    }
}

// --- 2. HANDLE DELETE ---
if (isset($_GET['delete'])) {
    $stmt = $con->prepare("DELETE FROM sections WHERE section_id = ?");
    $stmt->bind_param("i", $_GET['delete']);
    $stmt->execute();
    header("Location: manage_sections.php?msg=deleted"); exit();
}

// --- 3. FETCH DATA FOR VIEWING ---
// Get all sections with their semester names
$sections = $con->query("SELECT s.*, sem.semester_name 
                        FROM sections s 
                        JOIN semester sem ON s.semester_id = sem.semester_id 
                        ORDER BY s.section_id DESC")->fetch_all(MYSQLI_ASSOC);

// Get semesters for the dropdown
$semesters = $con->query("SELECT * FROM semester ORDER BY semester_name ASC")->fetch_all(MYSQLI_ASSOC);

// --- 4. PREPARE EDIT MODE ---
$edit_mode = false;
$edit_data = ['section_id' => '', 'section_name' => '', 'semester_id' => ''];
if (isset($_GET['edit'])) {
    $edit_mode = true;
    $edit_id = $_GET['edit'];
    $res = $con->query("SELECT * FROM sections WHERE section_id = $edit_id");
    if ($res->num_rows > 0) { $edit_data = $res->fetch_assoc(); }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Section Management</title>
    <link rel="icon" type="image/png" href="../../images/pars.png">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        body { background-color: #f4f7f6; padding-top: 30px; }
        .card { border: none; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
        .thead-light th { background-color: #f8f9fa; border-top: none; }
    </style>
</head>
<body>

<div class="container">
    <div class="row">
        <!-- FORM COLUMN (Create/Edit) -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header <?php echo $edit_mode ? 'bg-warning' : 'bg-primary'; ?> text-white">
                    <h5 class="mb-0">
                        <i class="fas <?php echo $edit_mode ? 'fa-edit' : 'fa-plus-circle'; ?> mr-2"></i>
                        <?php echo $edit_mode ? 'Edit Section' : 'Add New Section'; ?>
                    </h5>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <?php if($edit_mode): ?>
                            <input type="hidden" name="section_id" value="<?php echo $edit_data['section_id']; ?>">
                        <?php endif; ?>

                        <div class="form-group">
                            <label class="font-weight-bold">Section Name</label>
                            <input type="text" name="section_name" class="form-control" 
                                   placeholder="e.g. 4th A" required 
                                   value="<?php echo htmlspecialchars($edit_data['section_name']); ?>">
                        </div>

                        <div class="form-group">
                            <label class="font-weight-bold">Assign to Semester</label>
                            <select name="semester_id" class="form-control" required>
                                <option value="">-- Select Semester --</option>
                                <?php foreach($semesters as $sem): ?>
                                    <option value="<?php echo $sem['semester_id']; ?>" 
                                        <?php echo ($sem['semester_id'] == $edit_data['semester_id']) ? 'selected' : ''; ?>>
                                        <?php echo $sem['semester_name']; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <button type="submit" name="<?php echo $edit_mode ? 'update_section' : 'save_section'; ?>" 
                                class="btn <?php echo $edit_mode ? 'btn-warning' : 'btn-primary'; ?> btn-block shadow-sm">
                            <?php echo $edit_mode ? 'Update Changes' : 'Save Section'; ?>
                        </button>
                        
                        <?php if($edit_mode): ?>
                            <!-- <a href="../coordinator.php" class="btn btn-primary btn-block border mt-2">Cancel Edit</a> -->
                        <?php endif; ?>
                    </form>
                </div>
            </div>
        </div>

        <!-- LIST COLUMN (View) -->
        <div class="col-md-8">
            <?php if($message): ?>
                <div class="alert alert-success alert-dismissible fade show shadow-sm">
                    <strong>Success!</strong> Record successfully <?php echo $message; ?>.
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                </div>
            <?php endif; ?>

            <div class="card">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 text-dark font-weight-bold">Existing Sections</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th class="pl-4">ID</th>
                                    <th>Section Name</th>
                                    <th>Semester</th>
                                    <th class="text-right pr-4">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(empty($sections)): ?>
                                    <tr><td colspan="4" class="text-center py-4">No sections found.</td></tr>
                                <?php else: ?>
                                    <?php foreach($sections as $row): ?>
                                    <tr>
                                        <td class="pl-4 text-muted"><?php echo $row['section_id']; ?></td>
                                        <td class="font-weight-bold"><?php echo $row['section_name']; ?></td>
                                        <td><span class="badge badge-info px-3 py-2"><?php echo $row['semester_name']; ?></span></td>
                                        <td class="text-right pr-4">
                                            <a href="?edit=<?php echo $row['section_id']; ?>" class="btn btn-sm btn-outline-warning mr-1">
                                                <!-- <i class="fas fa-edit"></i> -->Edit
                                            </a>
                                            <a href="?delete=<?php echo $row['section_id']; ?>" 
                                               class="btn btn-sm btn-outline-danger" 
                                               onclick="return confirm('you don't delete section sure?')">
                                                <!-- <i class="fas fa-trash"></i> --> Delete
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/jquery@3.5.1/dist/jquery.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>