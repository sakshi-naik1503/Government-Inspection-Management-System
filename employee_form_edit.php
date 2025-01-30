<?php
session_start();
include 'db.php';

// Check if expense_id is set
if (!isset($_GET['expense_id'])) {
    echo "<script>alert('Invalid request!'); window.location.href = 'employee_status_review.php';</script>";
    exit;
}

$expense_id = $_GET['expense_id'];

// Fetch existing expense details
$sql = "SELECT * FROM expenses WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $expense_id);
$stmt->execute();
$result = $stmt->get_result();
$expense = $result->fetch_assoc();

if (!$expense) {
    echo "<script>alert('Expense not found!'); window.location.href = 'employee_status_review.php';</script>";
    exit;
}

// Handle form submission for updates
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_expense'])) {
    $accommodation_cost = $_POST['accommodation_cost'];
    $transportation_cost = $_POST['transportation_cost'];
    $other_expenses = $_POST['other_expenses'];
    $total_cost = $accommodation_cost + $transportation_cost + $other_expenses;

    $receipt_path = $expense['receipt_path']; // Keep old receipt if not updated

    // Handle receipt upload
    if (!empty($_FILES['receipt']['name'])) {
        $upload_dir = 'uploads/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        $file_name = $_FILES['receipt']['name'];
        $file_tmp = $_FILES['receipt']['tmp_name'];
        $file_size = $_FILES['receipt']['size'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        $allowed_exts = ['jpg', 'jpeg', 'png'];

        if (!in_array($file_ext, $allowed_exts)) {
            echo "<script>alert('Only JPG, JPEG, and PNG files are allowed!');</script>";
        } elseif ($file_size > 1048576) {
            echo "<script>alert('File size must be 1MB or less!');</script>";
        } else {
            $receipt_path = $upload_dir . uniqid() . '.' . $file_ext;
            move_uploaded_file($file_tmp, $receipt_path);
        }
    }

    // Update expense record
    $sql = "UPDATE expenses SET accommodation_cost = ?, transportation_cost = ?, other_expenses = ?, total_cost = ?, receipt_path = ?, status = 'Pending' WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ddddsi', $accommodation_cost, $transportation_cost, $other_expenses, $total_cost, $receipt_path, $expense_id);

    if ($stmt->execute()) {
        echo "<script>alert('Expense updated successfully!'); window.location.href = 'expenses.php?tour_id=" . $expense['tour_id'] . "';</script>";
    } else {
        echo "<script>alert('Error updating expense!');</script>";
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
    <style>
         body {
            background-color: #f8f9fa;
            /* background-image: url('https://www.shutterstock.com/image-photo/lush-rice-paddy-field-neat-260nw-2499404003.jpg'); */
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            background-attachment: fixed;
        }
        .container {
            max-width: 600px;
        }
        .card {
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            background: rgba(255, 255, 255, 0.9);
        }
        .card-header {
            background: linear-gradient(90deg, rgb(79, 176, 0), rgb(68, 219, 83));
            color: white;
            font-size: 1.5rem;
            font-weight: bold;
            border-radius: 10px 10px 0 0;
        }
        .form-label {
            font-weight: 600;
        }
        .btn {
            font-size: 1.1rem;
            
            background: linear-gradient(90deg, rgb(79, 176, 0), rgb(68, 219, 83));
            border: none;
            color: white;
        }
        .btn:hover {
            opacity: 0.9;
        }
        .card-body {
            padding: 2rem;
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <div class="card">
            <div class="card-header text-center">
                <h3>Edit Expense</h3>
            </div>
            <div class="card-body">
                <form method="POST" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label class="form-label">Tour ID</label>
                        <input type="text" class="form-control" value="<?= htmlspecialchars($expense['tour_id']); ?>" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Transportation Cost</label>
                        <input type="number" name="transportation_cost" class="form-control" value="<?= htmlspecialchars($expense['transportation_cost']); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Accommodation Cost</label>
                        <input type="number" name="accommodation_cost" class="form-control" value="<?= htmlspecialchars($expense['accommodation_cost']); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Other Expenses</label>
                        <input type="number" name="other_expenses" class="form-control" value="<?= htmlspecialchars($expense['other_expenses']); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Upload New Receipt (Optional)</label>
                        <input type="file" name="receipt" class="form-control" accept=".jpg,.jpeg,.png">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Total Cost</label>
                        <input type="text" id="total_cost" class="form-control" value="<?= htmlspecialchars($expense['total_cost']); ?>" readonly>
                    </div>
                    <button type="submit" name="update_expense" class="btn btn-success">Update </button>
                    <a href="employee_status_review.php" class="btn btn-secondary">Cancel</a>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('input', function () {
            let accommodation = parseFloat(document.querySelector('[name="accommodation_cost"]').value) || 0;
            let transportation = parseFloat(document.querySelector('[name="transportation_cost"]').value) || 0;
            let other = parseFloat(document.querySelector('[name="other_expenses"]').value) || 0;
            document.getElementById('total_cost').value = accommodation + transportation + other;
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
