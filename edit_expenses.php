<?php
session_start();
include 'db.php';

// Check if an ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['error'] = "Invalid expense ID!";
    header("Location: admin_reviewed_expenses.php");
    exit;
}

$id = intval($_GET['id']);

// Fetch the record based on ID
$sql = "SELECT * FROM expenses WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    $_SESSION['error'] = "Expense not found!";
    header("Location: admin_reviewed_expenses.php");
    exit;
}

$row = $result->fetch_assoc();

// Update record when form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $status = trim($_POST['status']);
    $admin_comment = trim($_POST['admin_comment']);

    if (empty($status) || empty($admin_comment)) {
        $_SESSION['error'] = "All fields are required!";
    } else {
        $update_sql = "UPDATE expenses SET status = ?, admin_comment = ? WHERE id = ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("ssi", $status, $admin_comment, $id);

        if ($update_stmt->execute()) {
            $_SESSION['success'] = "Expense updated successfully!";
            echo "<script>
                    alert('Expense updated successfully!');
                    window.location.href='admin_reviewed_expenses.php';
                  </script>";
            exit;
        } else {
            $_SESSION['error'] = "Failed to update the record!";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Expense</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">Admin Panel</a>
        </div>
    </nav>

    <div class="container mt-5">
        <h2 class="text-center mb-4">Edit Expense</h2>

        <!-- Display messages -->
        <?php if (isset($_SESSION['error'])): ?>
            <script>alert("<?= $_SESSION['error']; ?>");</script>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <form action="" method="POST">
            <div class="mb-3">
                <label for="tour_id" class="form-label">Tour ID</label>
                <input type="text" class="form-control" id="tour_id" name="tour_id" value="<?= htmlspecialchars($row['tour_id']); ?>" readonly>
            </div>
            <div class="mb-3">
                <label for="total_cost" class="form-label">Total Cost</label>
                <input type="number" class="form-control" id="total_cost" name="total_cost" value="<?= htmlspecialchars($row['total_cost']); ?>" readonly step="0.01">
            </div>
            <div class="mb-3">
                <label for="status" class="form-label">Status</label>
                <select class="form-select" id="status" name="status" required>
                    <option value="Approved" <?= ($row['status'] == 'Approved') ? 'selected' : ''; ?>>Approved</option>
                    <option value="Rejected" <?= ($row['status'] == 'Rejected') ? 'selected' : ''; ?>>Rejected</option>
                    
                </select>
            </div>
            <div class="mb-3">
                <label for="admin_comment" class="form-label">Admin Comment</label>
                <textarea class="form-control" id="admin_comment" name="admin_comment" rows="3" required><?= htmlspecialchars($row['admin_comment']); ?></textarea>
            </div>
            <button type="submit" class="btn btn-primary">Save Changes</button>
            <a href="admin_reviewed_expenses.php" class="btn btn-secondary">Cancel</a>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
