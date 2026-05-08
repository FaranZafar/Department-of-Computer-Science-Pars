<?php
include_once("../../dbconnection.php");

// Fetch messages from the database (newest first)
// Note: Ensure your table name is 'messages' as used in your previous code
$query = "SELECT * FROM message ORDER BY message_id DESC";
$result = mysqli_query($con, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inbound Messages | Pars Campus Admin</title>
    <link rel="icon" type="image/png" href="../../images/pars.png">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    
    <style>
        body { font-family: 'Inter', sans-serif;
         background-color: #f4f7f6; 
         color: #333;
         }
        .table-container { 
            background: #fff; 
            padding: 30px; 
            border-radius: 12px; 
            box-shadow: 0 8px 20px rgba(0,0,0,0.08); 
            margin-top: 40px; 
            border: 1px solid #e0e0e0; 
        }
        .msg-text { 
            max-width: 300px; 
            font-size: 0.85rem; 
            color: #555; 
            white-space: normal; 
            word-wrap: break-word; 
        }
        .thead-custom { 
            /* background-color: #0056b3;  */
            color: white; 
        }
        .badge-academic { background-color: #e3f2fd; color: #0056b3; border: 1px solid #bbdefb; }
    </style>
</head>
<body>

    <div class="container-fluid px-lg-5">
        <div class="row justify-content-center">
            <div class="col-12">
                <div class="table-container">
                    <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
                        <div>
                            <h2 class="font-weight-bold text-primary mb-0">Inbound Messages</h2>
                            <p class="text-muted small">Manage student inquiries and feedback</p>
                        </div>
                        <a href="../coordinator.php" class="btn btn-outline-primary btn-sm px-4 shadow-sm">Back to Dashboard</a>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="thead-custom bg-primary">
                                <tr>
                                    <th>ID</th>
                                    <th>Student Info</th>
                                    <th>Academic Details</th>
                                    <th>Message</th>
                                    <th>Received Date</th>
                                    
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                if(mysqli_num_rows($result) > 0) {
                                    while($row = mysqli_fetch_assoc($result)) { 
                                ?>
                                <tr>
                                    <td class="font-weight-bold">#<?php echo $row['message_id']; ?></td>
                                    <td>
                                        <div class="font-weight-bold text-dark"><?php echo htmlspecialchars($row['name']); ?></div>
                                        <div class="small text-muted"><?php echo htmlspecialchars($row['email']); ?></div>
                                    </td>
                                    <td>
                                        <div class="badge badge-academic px-2 py-1 mb-1"><?php echo htmlspecialchars($row['degree']); ?></div>
                                        <div class="small">
                                            <span class="text-muted">Roll:</span> <?php echo htmlspecialchars($row['ag_no']); ?><br>
                                            <span class="text-muted">Sem/Sec:</span> <?php echo htmlspecialchars($row['semester']); ?> (<?php echo htmlspecialchars($row['section']); ?>)
                                        </div>
                                    </td>
                                    <td class="msg-text">
                                        <div class="border-left pl-2" style="border-width: 3px !important; border-color: #007bff !important;">
                                            <?php echo nl2br(htmlspecialchars($row['message'])); ?>
                                        </div>
                                    </td>
                                    <td>
                                        <small class="text-dark font-weight-bold">
                                            <?php echo date('d M, Y', strtotime($row['Date'])); ?>
                                        </small><br>
                                        <small class="text-muted"><?php echo date('h:i A', strtotime($row['Date'])); ?></small>
                                    </td>
                                   
                                </tr>
                                <?php 
                                    }
                                } else {
                                    echo "<tr><td colspan='6' class='text-center py-5 text-muted'>No messages found in the database.</td></tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function confirmDelete(id) {
            if(confirm("Warning: This will permanently delete the message. Continue?")) {
                window.location.href = "delete_message.php?id=" + id;
            }
        }
    </script>

    <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>