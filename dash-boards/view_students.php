<?php
include_once("../dbconnection.php");

$message = $_GET['msg'] ?? null;

// HANDLE DELETE
if (isset($_GET['delete'])) {
    $stmt = $con->prepare("DELETE FROM students WHERE student_id = ?");
    $stmt->bind_param("i", $_GET['delete']);
    $stmt->execute();
    header("Location: view_students.php?msg=Deleted"); exit();
}

// --- NEW: STATISTICS QUERIES ---
// 1. Total Enrolled Students
$totalRes = $con->query("SELECT COUNT(*) as total FROM students");
$totalCount = $totalRes->fetch_assoc()['total'];

// 2. Students per Degree
$degreeStatsQuery = "SELECT d.degree_name, COUNT(s.student_id) as count 
                     FROM degree d 
                     LEFT JOIN students s ON d.degree_id = s.degree_id 
                     GROUP BY d.degree_id";
$degreeStats = $con->query($degreeStatsQuery)->fetch_all(MYSQLI_ASSOC);
// -------------------------------

// HANDLE SEARCH
$search = $_GET['search'] ?? '';
$searchTerm = "%$search%";

$query = "SELECT s.*, d.degree_name, sem.semester_name, sec.section_name 
          FROM students s
          LEFT JOIN degree d ON s.degree_id = d.degree_id
          LEFT JOIN semester sem ON s.semester_id = sem.semester_id
          LEFT JOIN sections sec ON s.section_id = sec.section_id
          WHERE (s.full_name LIKE ? 
             OR s.ag_no LIKE ? 
             OR d.degree_name LIKE ? 
             OR sem.semester_name LIKE ?)
          ORDER BY s.student_id DESC";

$stmt = $con->prepare($query);
$stmt->bind_param("ssss", $searchTerm, $searchTerm, $searchTerm, $searchTerm);
$stmt->execute();
$students = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student List</title>
    <link rel="icon" type="image/png" href="../images/pars.png">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    
    <style>
        body { background-color: #f4f7f6; }
        .main-card { border: none; border-radius: 12px; box-shadow: 0 5px 15px rgba(0,0,0,0.05); }
        .stat-card { border: none; border-radius: 10px; transition: transform 0.2s; }
        .stat-card:hover { transform: translateY(-5px); }
        .thead-light th { background-color: #f8f9fa; border-top: none; text-transform: uppercase; font-size: 0.85rem; letter-spacing: 1px; }
        .table td { vertical-align: middle; }
    </style>
</head>
<body>

<div class="container-fluid mt-4">
    <div class="row justify-content-center">
        <div class="col-md-11">
            
            <!-- Statistics Section -->
            <div class="row mb-4">
                <!-- Total Students Card -->
                <div class="col-md-3">
                    <div class="card stat-card bg-primary text-white shadow-sm">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="text-uppercase mb-1" style="opacity: 0.8;">Total Enrolled</h6>
                                    <h2 class="mb-0 font-weight-bold"><?php echo $totalCount; ?></h2>
                                </div>
                                <i class="fas fa-users fa-3x" style="opacity: 0.3;"></i>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Degree Breakdown -->
                <div class="col-md-9">
                    <div class="card stat-card bg-white shadow-sm">
                        <div class="card-body py-3">
                            <h6 class="text-muted text-uppercase mb-3 font-weight-bold" style="font-size: 0.75rem;">Enrollment by Degree</h6>
                            <div class="d-flex flex-wrap">
                                <?php foreach($degreeStats as $stat): ?>
                                    <div class="mr-4 mb-2">
                                        <span class="text-secondary small d-block"><?php echo $stat['degree_name']; ?></span>
                                        <span class="badge badge-pill badge-light border font-weight-bold" style="font-size: 1rem;">
                                            <?php echo $stat['count']; ?> <small class="text-muted">Students</small>
                                        </span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Success Message Alert -->
            <?php if($message): ?>
            <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
                <strong>Success!</strong> Student record has been <?php echo htmlspecialchars($message); ?>.
                <button type="button" class="close" data-dismiss="alert">&times;</button>
            </div>
            <?php endif; ?>

            <div class="card main-card">
                <div class="card-header bg-white py-3">
                    <div class="row align-items-center">
                        <div class="col-md-4">
                            <h3 class="mb-0 text-primary font-weight-bold">Student Records</h3>
                        </div>
                        
                        <div class="col-md-5">
                            <form method="GET" class="input-group">
                               <input type="text" name="search" class="form-control" 
                                placeholder="Search Name, AG No, Degree, or Semester..." 
                                value="<?php echo htmlspecialchars($search); ?>">
                               <div class="input-group-append">
                                    <button class="btn btn-primary" type="submit"><i class="fas fa-search"></i></button>
                               </div>
                            </form>
                        </div>

                        <div class="col-md-3 text-right">
                            <a href="enroll_student.php" class="btn btn-success font-weight-bold shadow-sm">
                                <i class="fas fa-plus-circle"></i> Add New Student
                            </a>
                        </div>
                    </div>
                </div>

                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th class="pl-4">AG No</th>
                                    <th>Full Name</th>
                                    <th>Program / Degree</th>
                                    <th>Section</th>
                                    <th>Status</th>
                                    <th class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(empty($students)): ?>
                                    <tr>
                                        <td colspan="6" class="text-center py-5 text-muted">
                                            <i class="fas fa-user-slash fa-3x mb-3"></i><br>
                                            No students found matching your search.
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach($students as $row): ?>
                                    <tr>
                                        <td class="pl-4 font-weight-bold text-dark"><?php echo $row['ag_no']; ?></td>
                                        <td><?php echo $row['full_name']; ?></td>
                                        <td>
                                            <div class="font-weight-bold text-secondary"><?php echo $row['degree_name']; ?></div>
                                            <span class="badge badge-info shadow-sm"><?php echo $row['semester_name']; ?></span>
                                        </td>
                                        <td><span class="text-uppercase font-weight-bold"><?php echo $row['section_name']; ?></span></td>
                                        <td>
                                            <?php $statusClass = ($row['status'] == 'Active') ? 'badge-success' : 'badge-secondary'; ?>
                                            <span class="badge <?php echo $statusClass; ?> px-3 py-2"><?php echo $row['status']; ?></span>
                                        </td>
                                        <td class="text-center">
    <div class="d-flex justify-content-center">
        <!-- New Profile Button -->
        <a href="student_profile.php?id=<?php echo $row['student_id']; ?>" class="btn btn-sm btn-info mr-2 shadow-sm">
            <i class="fas fa-eye"></i> View
        </a>
        
        <a href="editstudent.php?id=<?php echo $row['student_id']; ?>" class="btn btn-sm btn-warning text-white mr-2 shadow-sm">
            <i class="fas fa-edit"></i> Edit
        </a>
        
        <a href="?delete=<?php echo $row['student_id']; ?>" class="btn btn-sm btn-danger shadow-sm" onclick="return confirm('Are you sure?')">
            <i class="fas fa-trash-alt"></i> Delete
        </a>
    </div>
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