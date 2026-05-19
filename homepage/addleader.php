<?php 
//  dbconnection 
include_once("../dbconnection.php"); 
// --- 1. HANDLE DELETE ACTION ---
if(isset($_GET['delete'])) {
    $id = intval($_GET['delete']); // Security: Ensure ID is an integer
    
    // Get the filename first to delete it from the folder
    $res = $con->query("SELECT staff_img FROM leadership_staff WHERE id = $id");
    if($res && $row = $res->fetch_assoc()) {
        if($row['staff_img'] != 'default.jpg' && file_exists("uploads/" . $row['staff_img'])) {
            @unlink("uploads/" . $row['staff_img']); 
        }
    }
    
    $con->query("DELETE FROM leadership_staff WHERE id = $id");
    header("Location: addleader.php?msg=Deleted");
    exit();
}

// --- 2. HANDLE EDIT FETCH ---
$edit_data = null;
if(isset($_GET['edit'])) {
    $id = intval($_GET['edit']);
    // Fixed: changed $conn to $con
    $res = $con->query("SELECT * FROM leadership_staff WHERE id = $id");
    if($res) {
        $edit_data = $res->fetch_assoc();
    }
}

// --- 3. HANDLE INSERT / UPDATE ---
if(isset($_POST['submit'])) {
    $fname = mysqli_real_escape_string($con, $_POST['fname']);
    $lname = mysqli_real_escape_string($con, $_POST['lname']);
    $role  = mysqli_real_escape_string($con, $_POST['role']);
    $id    = $_POST['staff_id'];
    
    $img = $_FILES['image']['name'];
    
    if(!empty($img)) {
        // Create folder if it doesn't exist
        if (!is_dir('uploads')) { mkdir('uploads', 0777, true); }
        
        move_uploaded_file($_FILES['image']['tmp_name'], "uploads/".$img);
        $img_sql = ", staff_img = '$img'";
    } else {
        $img_sql = "";
    }

    if(!empty($id)) {
        // UPDATE EXISTING
        $sql = "UPDATE leadership_staff SET first_name='$fname', last_name='$lname', user_role='$role' $img_sql WHERE id=$id";
    } else {
        // INSERT NEW
        $final_img = !empty($img) ? $img : 'default.jpg';
        $sql = "INSERT INTO leadership_staff (first_name, last_name, user_role, staff_img) VALUES ('$fname', '$lname', '$role', '$final_img')";
    }

    if($con->query($sql)) {
        header("Location: addleader.php?msg=Success");
        exit();
    } else {
        die("Query Error: " . $con->error);
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin - Manage Leadership</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="icon" type="image/png" href="../images/pars.png">
    <style>
        .thumb-img { width: 50px; height: 50px; object-fit: cover; border-radius: 5px; background: #eee; }
    </style>
</head>
<body class="bg-light">

<div class="container mt-5">
    <?php if(isset($_GET['msg'])): ?>
        <div class="alert alert-success alert-dismissible fade show">
            Action Performed: <?php echo htmlspecialchars($_GET['msg']); ?>
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
    <?php endif; ?>

    <div class="row">
        <div class="col-md-4">
            <div class="card p-4 shadow-sm border-0">
                <h4 class="mb-4"><?php echo $edit_data ? 'Edit' : 'Add'; ?> Leadership</h4>
                
                <form action="addleader.php" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="staff_id" value="<?php echo $edit_data['id'] ?? ''; ?>">
                    
                    <div class="form-group">
                        <label>First Name</label>
                        <input type="text" name="fname" class="form-control" value="<?php echo $edit_data['first_name'] ?? ''; ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Last Name</label>
                        <input type="text" name="lname" class="form-control" value="<?php echo $edit_data['last_name'] ?? ''; ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Role</label>
                        <input type="text" name="role" class="form-control" value="<?php echo $edit_data['user_role'] ?? ''; ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Image</label>
                        <input type="file" name="image" class="form-control-file">
                        <?php if($edit_data): ?>
                            <small class="text-muted">Leave blank to keep current photo</small>
                        <?php endif; ?>
                    </div>
                    <button type="submit" name="submit" class="btn btn-primary btn-block shadow-sm">
                        <?php echo $edit_data ? 'Update Member' : 'Add Member'; ?>
                    </button>
                    <?php if($edit_data): ?>
                        <a href="addleader.php" class="btn btn-link btn-block text-muted">Cancel Edit</a>
                    <?php endif; ?>
                </form>
            </div>
            <a href="home.php#about" class="btn btn-outline-info btn-block mt-3">View Public Page</a>
        </div>

        <div class="col-md-8">
            <div class="card p-4 shadow-sm border-0">
                <h4 class="mb-4">Current Staff List</h4>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Img</th>
                                <th>Name</th>
                                <th>Role</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $list = $con->query("SELECT * FROM leadership_staff ORDER BY id DESC");
                            if($list && $list->num_rows > 0):
                                while($row = $list->fetch_assoc()):
                            ?>
                            <tr>
                                <td>
                                    <?php 
                                        $img_path = "uploads/" . $row['staff_img'];
                                        $display_img = (!empty($row['staff_img']) && file_exists($img_path)) ? $img_path : 'https://via.placeholder.com/50';
                                    ?>
                                    <img src="<?php echo $display_img; ?>" class="thumb-img">
                                </td>
                                <td class="align-middle"><?php echo htmlspecialchars($row['first_name'] . " " . $row['last_name']); ?></td>
                                <td class="align-middle"><span class="badge badge-primary"><?php echo htmlspecialchars($row['user_role']); ?></span></td>
                                <td class="align-middle">
                                    <a href="addleader.php?edit=<?php echo $row['id']; ?>" class="btn btn-sm btn-info">Edit</a>
                                    <a href="addleader.php?delete=<?php echo $row['id']; ?>" 
                                       class="btn btn-sm btn-danger" 
                                       onclick="return confirm('Delete this record?')">Delete</a>
                                </td>
                            </tr>
                            <?php 
                                endwhile; 
                            else:
                                echo "<tr><td colspan='4' class='text-center'>No records found.</td></tr>";
                            endif; 
                            ?>
                        </tbody>
                    </table>
                <a href="../dash-boards/coordinator.php" class="btn btn-outline-primary btn-block mt-4" style="border-radius:20px;">Back</a>

                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/jquery@3.5.1/dist/jquery.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>