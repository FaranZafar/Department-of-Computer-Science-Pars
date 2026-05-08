<?php
include_once("../../dbconnection.php");

$message = $_GET['msg'] ?? null;
$error = null;

// --- 1. HANDLE FORM SUBMISSIONS (CREATE & UPDATE) ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $room_name = $_POST['room_name'];
    
    if (isset($_POST['save_room'])) {
        // CREATE Logic
        $stmt = $con->prepare("INSERT INTO room (room_name) VALUES (?)");
        $stmt->bind_param("s", $room_name);
        if ($stmt->execute()) {
            header("Location: addroom.php?msg=added"); exit();
        } else { $error = "Error adding record: " . $con->error; }
    } 
    elseif (isset($_POST['update_room'])) {
        // UPDATE Logic
        $room_id = $_POST['room_id'];
        $stmt = $con->prepare("UPDATE room SET room_name = ? WHERE room_id = ?");
        $stmt->bind_param("si", $room_name, $room_id);
        if ($stmt->execute()) {
            header("Location: addroom.php?msg=updated"); exit();
        } else { $error = "Error updating record: " . $con->error; }
    }
}

// --- 2. HANDLE DELETE ---
if (isset($_GET['delete'])) {
    $stmt = $con->prepare("DELETE FROM room WHERE room_id = ?");
    $stmt->bind_param("i", $_GET['delete']);
    $stmt->execute();
    header("Location: addroom.php?msg=deleted"); exit();
}

// --- 3. FETCH DATA FOR VIEWING ---
$rooms = $con->query("SELECT * FROM room ORDER BY room_id DESC")->fetch_all(MYSQLI_ASSOC);

// --- 4. PREPARE EDIT MODE ---
$edit_mode = false;
$edit_data = ['room_id' => '', 'room_name' => ''];
if (isset($_GET['edit'])) {
    $edit_mode = true;
    $edit_id = $_GET['edit'];
    $res = $con->query("SELECT * FROM room WHERE room_id = $edit_id");
    if ($res->num_rows > 0) { $edit_data = $res->fetch_assoc(); }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Room Management</title>
    <link rel="icon" type="image/png" href="../../images/pars.png">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        body { background-color: #f4f7f6; padding-top: 30px; }
        .card { border: none; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
        .thead-light th { background-color: #f8f9fa; border-top: none; text-transform: uppercase; font-size: 0.8rem; letter-spacing: 1px; }
    </style>
</head>
<body>

<div class="container">
    <div class="row">
        <!-- FORM COLUMN (Create/Edit) -->
        <div class="col-md-4">
            <div class="card shadow-sm">
                <div class="card-header <?php echo $edit_mode ? 'bg-warning' : 'bg-primary'; ?> text-white">
                    <h5 class="mb-0">
                        <i class="fas <?php echo $edit_mode ? 'fa-edit' : 'fa-door-open'; ?> mr-2"></i>
                        <?php echo $edit_mode ? 'Edit Room' : 'Add New Room'; ?>
                    </h5>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <?php if($edit_mode): ?>
                            <input type="hidden" name="room_id" value="<?php echo $edit_data['room_id']; ?>">
                        <?php endif; ?>

                        <div class="form-group">
                            <label class="font-weight-bold">Room Name / Number</label>
                            <input type="text" name="room_name" class="form-control" 
                                   placeholder="e.g. Room 101 or Lab A" required 
                                   value="<?php echo htmlspecialchars($edit_data['room_name']); ?>">
                        </div>

                        <button type="submit" name="<?php echo $edit_mode ? 'update_room' : 'save_room'; ?>" 
                                class="btn <?php echo $edit_mode ? 'btn-warning' : 'btn-primary'; ?> btn-block font-weight-bold shadow-sm">
                            <?php echo $edit_mode ? 'Update Room' : 'Save Room'; ?>
                        </button>
                        
                        <?php if($edit_mode): ?>
                            <a href="addroom.php" class="btn btn-light btn-block border mt-2">Cancel</a>
                        <?php endif; ?>
                    </form>
                </div>
            </div>
        </div>

        <!-- LIST COLUMN (View) -->
        <div class="col-md-8">
            <?php if($message): ?>
                <div class="alert alert-success alert-dismissible fade show shadow-sm border-0">
                    <strong>Success!</strong> Room has been <?php echo htmlspecialchars($message); ?>.
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                </div>
            <?php endif; ?>

            <div class="card shadow-sm">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 text-dark font-weight-bold">Registered Rooms</h5>
                    <span class="badge badge-primary badge-pill px-3 py-2"><?php echo count($rooms); ?> Total</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th class="pl-4">ID</th>
                                    <th>Room Name</th>
                                    <th class="text-right pr-4">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(empty($rooms)): ?>
                                    <tr><td colspan="3" class="text-center py-5 text-muted">No rooms found.</td></tr>
                                <?php else: ?>
                                    <?php foreach($rooms as $row): ?>
                                    <tr>
                                        <td class="pl-4 text-muted small"><?php echo $row['room_id']; ?></td>
                                        <td class="font-weight-bold text-dark"><?php echo $row['room_name']; ?></td>
                                        <td class="text-right pr-4">
                                            <a href="?edit=<?php echo $row['room_id']; ?>" class="btn btn-sm btn-outline-warning mr-1 shadow-sm">
                                                <i class="fas fa-edit"></i> Edit
                                            </a>
                                            <a href="?delete=<?php echo $row['room_id']; ?>" 
                                               class="btn btn-sm btn-outline-danger shadow-sm" 
                                               onclick="return confirm('Delete this room?')">
                                                <i class="fas fa-trash"></i> Delete
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