<?php
include_once("../../dbconnection.php");

$message = $_GET['msg'] ?? null;
$error = null;

// --- 1. HANDLE FORM SUBMISSIONS ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $section_name = mysqli_real_escape_string($con, $_POST['section_name']);
    $semester_id  = $_POST['semester_id'];
    $degree_id    = $_POST['degree_id'];
    
    if (isset($_POST['save_section'])) {
        $stmt = $con->prepare("INSERT INTO sections (section_name, semester_id, degree_id) VALUES (?, ?, ?)");
        $stmt->bind_param("sii", $section_name, $semester_id, $degree_id);
        if ($stmt->execute()) { header("Location: manage_sections.php?msg=added"); exit(); }
    } 
    elseif (isset($_POST['update_section'])) {
        $section_id = $_POST['section_id'];
        $stmt = $con->prepare("UPDATE sections SET section_name = ?, semester_id = ?, degree_id = ? WHERE section_id = ?");
        $stmt->bind_param("siii", $section_name, $semester_id, $degree_id, $section_id);
        if ($stmt->execute()) { header("Location: manage_sections.php?msg=updated"); exit(); }
    }
}

// --- 2. HANDLE DELETE ---
if (isset($_GET['delete'])) {
    $stmt = $con->prepare("DELETE FROM sections WHERE section_id = ?");
    $stmt->bind_param("i", $_GET['delete']);
    $stmt->execute();
    header("Location: manage_sections.php?msg=deleted"); exit();
}

// --- 3. FETCH DATA ---
$sections = $con->query("SELECT s.*, sem.semester_name, d.degree_name 
                        FROM sections s 
                        LEFT JOIN semester sem ON s.semester_id = sem.semester_id 
                        LEFT JOIN degree d ON s.degree_id = d.degree_id
                        ORDER BY s.section_id DESC")->fetch_all(MYSQLI_ASSOC);

$semesters_raw = $con->query("SELECT * FROM semester ORDER BY semester_name ASC")->fetch_all(MYSQLI_ASSOC);
$degrees = $con->query("SELECT * FROM degree ORDER BY degree_name ASC")->fetch_all(MYSQLI_ASSOC);

// --- 4. EDIT MODE ---
$edit_mode = false;
$edit_data = ['section_id' => '', 'section_name' => '', 'semester_id' => '', 'degree_id' => ''];
if (isset($_GET['edit'])) {
    $edit_mode = true;
    $res = $con->query("SELECT * FROM sections WHERE section_id = " . (int)$_GET['edit']);
    if ($res->num_rows > 0) { $edit_data = $res->fetch_assoc(); }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Section Management | Pars Campus</title>
    <link rel="icon" type="image/png" href="../../images/pars.png">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        body { 
            background-color: #f0f2f5; 
            font-family: 'Segoe UI', sans-serif;
         }
        .container { 
            margin-top: 40px; 
        }
        .card { 
            border: none; 
            border-radius: 15px; 
            box-shadow: 0 8px 24px rgba(0,0,0,0.05); 
        }
        .form-control, .custom-select { 
            border-radius: 10px; 
            height: 45px; 
        }
        .btn-primary { 
            background: #4e73df; 
            border: none; 
            border-radius: 10px; 
            font-weight: 600; 
            padding: 12px; 
        }
        .badge-semester { 
            background: #eef2ff; 
            color: #6366f1; 
            padding: 6px 12px; 
            border-radius: 8px; 
            font-weight: 600; 
        }
        .table thead th { 
            border-top: none; 
            background: #f8f9fc; 
            text-transform: uppercase; 
            font-size: 11px; 
            color: #4e73df; 
        }
    </style>
</head>
<body>

<div class="container">
    <div class="row">
        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-header <?php echo $edit_mode ? 'bg-warning' : 'bg-primary text-white'; ?> py-3">
                    <h5 class="mb-0"><i class="fas <?php echo $edit_mode ? 'fa-edit' : 'fa-plus'; ?> mr-2"></i><?php echo $edit_mode ? 'Edit Section' : 'Create Section'; ?></h5>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <?php if($edit_mode): ?>
                            <input type="hidden" name="section_id" value="<?php echo $edit_data['section_id']; ?>">
                        <?php endif; ?>

                        <div class="form-group">
                            <label class="small font-weight-bold">Section Name</label>
                            <input type="text" name="section_name" class="form-control" placeholder="e.g. BS-SE-4A" required 
                                   value="<?php echo htmlspecialchars($edit_data['section_name']); ?>">
                        </div>

                        <div class="form-group">
                            <label class="small font-weight-bold">1. Select Degree</label>
                            <select name="degree_id" id="degree_select" class="custom-select" required onchange="updateSemesterDropdown()">
                                <option value="">-- Select Degree First --</option>
                                <?php foreach($degrees as $deg): ?>
                                    <option value="<?php echo $deg['degree_id']; ?>" 
                                        <?php echo ($deg['degree_id'] == $edit_data['degree_id']) ? 'selected' : ''; ?>>
                                        <?php echo $deg['degree_name']; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="small font-weight-bold">2. Assign Semester</label>
                            <select name="semester_id" id="semester_select" class="custom-select" required disabled>
                                <option value="">-- Waiting for Degree --</option>
                            </select>
                        </div>

                        <button type="submit" name="<?php echo $edit_mode ? 'update_section' : 'save_section'; ?>" 
                                class="btn btn-primary btn-block shadow-sm mt-3">
                            <?php echo $edit_mode ? 'Apply Changes' : 'Save Section'; ?>
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 font-weight-bold text-dark">Existing Sections</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th class="pl-4">Degree</th>
                                     <th>Semester</th>
                                    <th>Section Name</th>
                                    <th class="text-center pr-4">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($sections as $row): ?>
                                <tr>
                                    <td class="pl-4 align-middle small text-muted"><?php echo $row['degree_name']; ?></td>
                                    <td class="align-middle"><span class="badge-semester"><?php echo $row['semester_name']; ?></span></td>
                                    <td class="align-middle font-weight-bold"><?php echo $row['section_name']; ?></td>
                                    
                                    <td class="text-right pr-4 align-middle">
                                        <a href="?edit=<?php echo $row['section_id']; ?>" class="text-warning mr-3">Edit</a>
                                        <a href="?delete=<?php echo $row['section_id']; ?>" class="text-danger" onclick="return confirm('Delete?')">Delete</a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// JSON encoded data from PHP
const allSemesters = <?php echo json_encode($semesters_raw); ?>;
const savedSemesterId = "<?php echo $edit_data['semester_id']; ?>";

function updateSemesterDropdown() {
    const degreeId = document.getElementById('degree_select').value;
    const semesterSelect = document.getElementById('semester_select');
    
    // Reset Dropdown
    semesterSelect.innerHTML = '';

    if (degreeId === "") {
        semesterSelect.disabled = true;
        const opt = document.createElement('option');
        opt.value = "";
        opt.innerHTML = "-- Waiting for Degree --";
        semesterSelect.appendChild(opt);
    } else {
        semesterSelect.disabled = false;
        
        // Default Option
        const defaultOpt = document.createElement('option');
        defaultOpt.value = "";
        defaultOpt.innerHTML = "-- Select Semester --";
        semesterSelect.appendChild(defaultOpt);

        // Filter Logic
        const filtered = allSemesters.filter(s => s.degree_id == degreeId);
        
        filtered.forEach(s => {
            const opt = document.createElement('option');
            opt.value = s.semester_id;
            opt.innerHTML = s.semester_name;
            
            // Re-select value if in edit mode
            if(s.semester_id == savedSemesterId) {
                opt.selected = true;
            }
            
            semesterSelect.appendChild(opt);
        });
    }
}

// Call on load for Edit Mode support
window.onload = updateSemesterDropdown;
</script>

</body>
</html>