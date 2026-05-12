<?php
include_once("../dbconnection.php");

// 1. AUTO-UPDATE GRADUATION STATUS
// Logic: If a student has no semester_id or section_id, and is NOT already marked 'Graduated',
// update them to 'Graduated'.
$con->query("UPDATE students 
             SET status = 'Graduated' 
             WHERE (semester_id IS NULL OR semester_id = 0 OR section_id IS NULL OR section_id = 0) 
             AND status != 'Graduated'");

// 2. GET FILTER PARAMETERS FROM URL
$filter_degree   = $_GET['degree_id'] ?? null;
$filter_semester = $_GET['semester_id'] ?? null;
$filter_section  = $_GET['section_id'] ?? null;
$filter_status   = $_GET['status'] ?? null; 
$search          = $_GET['search'] ?? '';

$message = $_GET['msg'] ?? null;

// 3. HANDLE DELETE
if (isset($_GET['delete'])) {
    $stmt = $con->prepare("DELETE FROM students WHERE student_id = ?");
    $stmt->bind_param("i", $_GET['delete']);
    $stmt->execute();
    header("Location: view_students.php?msg=Deleted"); 
    exit();
}

// 4. STATISTICS QUERIES
// Overall Totals
$statsRes = $con->query("SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN status = 'Active' THEN 1 ELSE 0 END) as active,
    SUM(CASE WHEN status = 'Graduated' THEN 1 ELSE 0 END) as graduated,
    SUM(CASE WHEN status = 'Freezed' THEN 1 ELSE 0 END) as freezed
    FROM students");
$stats = $statsRes->fetch_assoc();

// Degree Stats (Counts Active/Freezed as "Enrolled", Graduated separately if needed)
$degreeStats = $con->query("SELECT d.*, COUNT(s.student_id) as count 
    FROM degree d 
    LEFT JOIN students s ON d.degree_id = s.degree_id AND s.status IN ('Active', 'Freezed')
    GROUP BY d.degree_id")->fetch_all(MYSQLI_ASSOC);

// Semester Stats
$semesterStats = [];
if ($filter_degree) {
    $stmt = $con->prepare("SELECT sem.*, COUNT(s.student_id) as count 
        FROM semester sem 
        LEFT JOIN students s ON sem.semester_id = s.semester_id AND s.status IN ('Active', 'Freezed')
        WHERE sem.degree_id = ? GROUP BY sem.semester_id");
    $stmt->bind_param("i", $filter_degree);
    $stmt->execute();
    $semesterStats = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

// Section Stats
$sectionStats = [];
if ($filter_semester) {
    $stmt = $con->prepare("SELECT sec.*, COUNT(s.student_id) as count 
        FROM sections sec 
        LEFT JOIN students s ON sec.section_id = s.section_id AND s.status IN ('Active', 'Freezed')
        WHERE sec.semester_id = ? GROUP BY sec.section_id");
    $stmt->bind_param("i", $filter_semester);
    $stmt->execute();
    $sectionStats = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

// 5. MAIN STUDENT QUERY
$sql = "SELECT s.*, d.degree_name, sem.semester_name, sec.section_name 
        FROM students s
        LEFT JOIN degree d ON s.degree_id = d.degree_id
        LEFT JOIN semester sem ON s.semester_id = sem.semester_id
        LEFT JOIN sections sec ON s.section_id = sec.section_id
        WHERE 1=1";

$params = []; $types = "";

if ($search) { 
    $sql .= " AND (s.full_name LIKE ? OR s.ag_no LIKE ?)"; 
    $st = "%$search%"; $params[] = $st; $params[] = $st; $types .= "ss"; 
}
if ($filter_degree) { $sql .= " AND s.degree_id = ?"; $params[] = $filter_degree; $types .= "i"; }
if ($filter_semester) { $sql .= " AND s.semester_id = ?"; $params[] = $filter_semester; $types .= "i"; }
if ($filter_section) { $sql .= " AND s.section_id = ?"; $params[] = $filter_section; $types .= "i"; }
if ($filter_status) { $sql .= " AND s.status = ?"; $params[] = $filter_status; $types .= "s"; }

$sql .= " ORDER BY s.student_id DESC";
$stmt = $con->prepare($sql);
if ($types) $stmt->bind_param($types, ...$params);
$stmt->execute();
$students = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Student Records | Pars Campus</title>
    <link rel="icon" type="image/png" href="../images/pars.png">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        body { background-color: #f0f2f5; font-family: 'Segoe UI', sans-serif; }
        .stat-card { border: none; border-radius: 15px; color: white; transition: 0.3s; cursor: pointer; }
        .stat-card:hover { transform: translateY(-3px); box-shadow: 0 10px 20px rgba(0,0,0,0.1); }
        .filter-btn { border-radius: 20px; font-weight: 600; font-size: 0.8rem; margin: 2px; transition: 0.2s; }
        .main-card { border: none; border-radius: 15px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
        .table thead th { border: none; background: #f8f9fc; text-transform: uppercase; font-size: 0.7rem; color: #4e73df; letter-spacing: 1px; }
        .badge-status { padding: 6px 12px; border-radius: 20px; font-size: 0.75rem; font-weight: 700; }
        .text-graduated { color: #28a745; font-weight: bold; font-size: 0.85rem; }
        .text-null { color: #adb5bd; font-style: italic; font-size: 0.8rem; }
    </style>
</head>
<body>

<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-xl-11">

            <!-- Statistics Section -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card stat-card bg-primary shadow-sm" onclick="location.href='view_students.php'">
                        <div class="card-body py-3">
                            <small class="text-uppercase font-weight-bold" style="opacity: 0.8">Total Records</small>
                            <h2 class="mb-0 font-weight-bold"><?= $stats['total'] ?></h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card stat-card bg-success shadow-sm" onclick="location.href='?status=Active'">
                        <div class="card-body py-3">
                            <small class="text-uppercase font-weight-bold" style="opacity: 0.8">Active Students</small>
                            <h2 class="mb-0 font-weight-bold"><?= $stats['active'] ?></h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card stat-card bg-dark shadow-sm" onclick="location.href='?status=Graduated'">
                        <div class="card-body py-3">
                            <small class="text-uppercase font-weight-bold" style="opacity: 0.8">Graduated (Completed)</small>
                            <h2 class="mb-0 font-weight-bold"><?= $stats['graduated'] ?></h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card stat-card bg-info shadow-sm" onclick="location.href='?status=Freezed'">
                        <div class="card-body py-3">
                            <small class="text-uppercase font-weight-bold" style="opacity: 0.8">Freezed</small>
                            <h2 class="mb-0 font-weight-bold"><?= $stats['freezed'] ?></h2>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Hierarchical Filters -->
            <div class="card main-card mb-4">
                <div class="card-body">
                    <div class="mb-2">
                        <label class="small font-weight-bold text-muted mb-1">FILTER BY DEGREE</label><br>
                        <?php foreach($degreeStats as $stat): ?>
                            <a href="?degree_id=<?= $stat['degree_id'] ?>&search=<?= $search ?>" 
                               class="btn filter-btn <?= ($filter_degree == $stat['degree_id']) ? 'btn-primary' : 'btn-outline-primary' ?>">
                                <?= $stat['degree_name'] ?> (<?= $stat['count'] ?> Enrolled)
                            </a>
                        <?php endforeach; ?>
                    </div>

                    <?php if($filter_degree): ?>
                    <hr class="my-2">
                    <div class="mb-2">
                        <label class="small font-weight-bold text-muted mb-1">SELECT SEMESTER</label><br>
                        <?php foreach($semesterStats as $stat): ?>
                            <a href="?degree_id=<?= $filter_degree ?>&semester_id=<?= $stat['semester_id'] ?>&search=<?= $search ?>" 
                               class="btn filter-btn <?= ($filter_semester == $stat['semester_id']) ? 'btn-info' : 'btn-outline-info' ?>">
                                <?= $stat['semester_name'] ?> (<?= $stat['count'] ?>)
                            </a>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>

                    <?php if($filter_semester): ?>
                    <hr class="my-2">
                    <div>
                        <label class="small font-weight-bold text-muted mb-1">SELECT SECTION</label><br>
                        <?php foreach($sectionStats as $stat): ?>
                            <a href="?degree_id=<?= $filter_degree ?>&semester_id=<?= $filter_semester ?>&section_id=<?= $stat['section_id'] ?>&search=<?= $search ?>" 
                               class="btn filter-btn <?= ($filter_section == $stat['section_id']) ? 'btn-dark' : 'btn-outline-dark' ?>">
                                Section <?= $stat['section_name'] ?> (<?= $stat['count'] ?>)
                            </a>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Table Card -->
            <div class="card main-card">
                <div class="card-header bg-white py-3">
                    <div class="row align-items-center">
                        <div class="col-md-4"><h5 class="mb-0 font-weight-bold text-primary">Student Database</h5></div>
                        <div class="col-md-5">
                            <form method="GET" class="input-group input-group-sm">
                                <input type="text" name="search" class="form-control" placeholder="Search AG No or Name..." value="<?= htmlspecialchars($search) ?>">
                                <div class="input-group-append">
                                    <button class="btn btn-primary" type="submit"><i class="fas fa-search"></i></button>
                                </div>
                            </form>
                        </div>
                        <div class="col-md-3 text-right">
                            <a href="enroll_student.php" class="btn btn-sm btn-success font-weight-bold shadow-sm"><i class="fas fa-plus mr-1"></i> Enroll Student</a>
                        </div>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th class="pl-4">AG Number</th>
                                <th>Full Name</th>
                                <th>Degree</th>
                                <th>Sem / Sec</th>
                                <th>Status</th>
                                <th class="text-right pr-4">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(empty($students)): ?>
                                <tr><td colspan="6" class="text-center py-5 text-muted">No student records found matching your filters.</td></tr>
                            <?php else: ?>
                                <?php foreach($students as $row): ?>
                                <tr>
                                    <td class="pl-4 align-middle font-weight-bold"><?= $row['ag_no'] ?></td>
                                    <td class="align-middle"><?= $row['full_name'] ?></td>
                                    <td class="align-middle small"><?= $row['degree_name'] ?></td>
                                    <td class="align-middle">
                                        <?php if($row['status'] == 'Graduated'): ?>
                                            <span class="text-graduated"><i class="fas fa-graduation-cap mr-1"></i> Degree Completed</span>
                                        <?php else: ?>
                                            <div class="font-weight-bold small">
                                                <?= (!empty($row['semester_name'])) ? $row['semester_name'] : '<span class="text-null">No Semester</span>'; ?>
                                            </div>
                                            <div class="text-muted small">
                                                <?= (!empty($row['section_name'])) ? 'Section '.$row['section_name'] : '<span class="text-null">No Section</span>'; ?>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="align-middle">
                                        <?php 
                                            $c = 'badge-secondary';
                                            if($row['status'] == 'Active') $c = 'badge-success';
                                            if($row['status'] == 'Graduated') $c = 'badge-dark';
                                            if($row['status'] == 'Freezed') $c = 'badge-info';
                                        ?>
                                        <span class="badge badge-status <?= $c ?>"><?= $row['status'] ?></span>
                                    </td>
                                    <td class="text-right pr-4 align-middle">
                                        <a href="student_profile.php?id=<?= $row['student_id'] ?>" class="text-info mr-2" title="View Profile"><i class="fas fa-eye"></i></a>
                                        <a href="editstudent.php?id=<?= $row['student_id'] ?>" class="btn btn-sm btn-outline-warning py-0 mr-1">Edit</a>
                                        <a href="?delete=<?= $row['student_id'] ?>" class="btn btn-sm btn-outline-danger py-0" onclick="return confirm('Delete record permanently?')">Delete</a>
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

<script src="https://cdn.jsdelivr.net/npm/jquery@3.5.1/dist/jquery.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>