<?php
$file = 'data.json';
$programs = file_exists($file) ? json_decode(file_get_contents($file), true) : [];

if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    if (isset($programs[$id])) {
        unset($programs[$id]);
        file_put_contents($file, json_encode(array_values($programs)));
    }
    header("Location: addprogram.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $new_program = [
        "title" => htmlspecialchars($_POST['title']),
        "duration" => htmlspecialchars($_POST['duration']),
        "description" => htmlspecialchars($_POST['description'])
    ];

    if (isset($_POST['id']) && $_POST['id'] !== "") {
        $programs[$_POST['id']] = $new_program;
    } else {
        $programs[] = $new_program;
    }

    file_put_contents($file, json_encode(array_values($programs)));
    header("Location: addprogram.php");
    exit();
}

$edit_id = isset($_GET['edit']) ? $_GET['edit'] : null;
$edit_data = ($edit_id !== null) ? $programs[$edit_id] : null;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin - Pars Campus</title>
    <link rel="icon" type="image/png" href="../images/pars.png">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <style>
        body { background-color: #f4f7f9; padding: 40px 0; font-family: 'Inter', sans-serif; }
        .card { border: none; border-radius: 15px; box-shadow: 0 4px 10px rgba(0,0,0,0.05); }
        .btn-success { border-radius: 20px; background-color: #28a745; border: none; }
        .list-group-item { border: none; margin-bottom: 8px; border-radius: 10px !important; box-shadow: 0 2px 5px rgba(0,0,0,0.03); }
    </style>
</head>
<body>
<div class="container">
    <div class="row">
        <div class="col-md-5 mb-4">
            <div class="card p-4">
                <h4 class="font-weight-bold"><?php echo $edit_data ? "Edit Program" : "Add New Program"; ?></h4>
                <hr>
                <form method="POST">
                    <input type="hidden" name="id" value="<?php echo $edit_id; ?>">
                    <div class="form-group"><label>Duration</label>
                        <input type="text" name="duration" class="form-control" value="<?php echo $edit_data['duration'] ?? ''; ?>" placeholder="4 Years | 8 Semesters" required>
                    </div>
                    <div class="form-group"><label>Program Name</label>
                        <input type="text" name="title" class="form-control" value="<?php echo $edit_data['title'] ?? ''; ?>" placeholder="BS Computer Science" required>
                    </div>
                    <div class="form-group"><label>Description</label>
                        <textarea name="description" class="form-control" rows="4" required><?php echo $edit_data['description'] ?? ''; ?></textarea>
                    </div>
                    <button type="submit" class="btn btn-success btn-block shadow-sm">Save Program</button>
                    <?php if($edit_data): ?>
                        <a href="addprogram.php" class="btn btn-link btn-block text-danger">Cancel Edit</a>
                    <?php endif; ?>
                </form>
            </div>
        </div>
        <div class="col-md-7">
            <div class="card p-4">
                <h4 class="font-weight-bold">Existing Programs</h4>
                <hr>
                <div class="list-group">
                    <?php foreach ($programs as $id => $p): ?>
                    <div class="list-group-item d-flex justify-content-between align-items-center">
                        <span class="font-weight-bold"><?php echo $p['title']; ?></span>
                        <div>
                            <a href="addprogram.php?edit=<?php echo $id; ?>" class="btn btn-sm btn-outline-primary">Edit</a>
                            <a href="addprogram.php?delete=<?php echo $id; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this?')">Delete</a>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <a href="program.php" class="btn btn-primary btn-block mt-4" style="border-radius:20px;">View Live Website</a>
            </div>
        </div>
    </div>
</div>
</body>
</html>