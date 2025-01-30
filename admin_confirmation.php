<?php
session_start();
include 'db.php';

// Fetch all pending expenses
$sql = "SELECT * FROM expenses WHERE status = 'Pending'";
$result = $conn->query($sql);

// Handle Approval or Rejection
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $expense_id = $_POST['expense_id'];
    $status = $_POST['status'];
    $admin_comment = ($status === 'Rejected') ? $_POST['admin_comment'] : 'Approved without comment';

    // Update status in DB
    $update_sql = "UPDATE expenses SET status = ?, admin_comment = ? WHERE id = ?";
    $stmt = $conn->prepare($update_sql);
    $stmt->bind_param('ssi', $status, $admin_comment, $expense_id);
    
    if ($stmt->execute()) {
        echo "<script>
                alert('Expense status updated!');
                window.location.href='admin_reviewed_expenses.php'; // Redirect to reviewed expenses page
              </script>";
        exit;
    } else {
        echo "<script>alert('Failed to update expense status.');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Confirmation</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
         body{
        /* background-image: url('https://www.shutterstock.com/image-photo/lush-rice-paddy-field-neat-260nw-2499404003.jpg'); */
        background-size: cover;
        background-position: center;
        background-repeat: no-repeat;
        background-attachment: fixed;
    }

    .card {
        border-radius: 10px;
        border: none;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }

    .table th, .table td {
        vertical-align: middle;
    }

    .table-hover tbody tr:hover {
        background-color: #f1f1f1;
    }

    .table-dark th {
        background: linear-gradient(90deg, rgb(79, 176, 0), rgb(68, 219, 83));
        color: #fff;
    }

    .btn-sm {
        padding: 6px 12px;
    }

    .action-btns a {
        margin: 0 5px;
    }

    h2 {
        font-size: 2.5rem;
        font-weight: bold;
        color: #007bff;
    }

    .card-body {
        padding: 30px;
    }

    /* Apply the linear gradient to navbar */
    .navbar {
        background: linear-gradient(90deg, rgb(79, 176, 0), rgb(68, 219, 83));
    }

    .navbar-nav .nav-link {
        color: white !important;
    }

    .navbar-toggler-icon {
        background-color: white !important;
    }
    .navbar .navbar-brand {
            font-weight: bold;
        }
    </style>
    <script>
        function validateForm() {
            let status = document.getElementById("status").value;
            let reasonField = document.getElementById("reason_field");
            if (status === "Rejected" && reasonField.value.trim() === "") {
                alert("Please provide a reason for rejection.");
                return false;
            }
            return true;
        }

        function setModalValues(expenseId, tourId, totalCost) {
            document.getElementById("modalExpenseId").value = expenseId;
            document.getElementById("modalTourId").innerText = tourId;
            document.getElementById("modalTotalCost").innerText = "₹" + totalCost;
        }
    </script>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">Admin Panel</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">

                    <li class="nav-item">
                        <a class="nav-link" href="#">Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-5">
        <h2 class="mb-4 text-center text-primary">Admin Expense Review</h2>

        <div class="card shadow-lg">
            <div class="card-body">
                <table class="table table-hover text-center">
                    <thead class="table-dark">
                        <tr>
                            <th>Tour ID</th>
                            <th>Total Cost</th>
                            <th>Receipt</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result->fetch_assoc()) { ?>
                            <tr>
                                <td><?= htmlspecialchars($row['tour_id']); ?></td>
                                <td>₹<?= number_format($row['total_cost'], 2); ?></td>
                                <td>
                                    <?php if (!empty($row['receipt_path'])) { ?>
                                        <a href="<?= htmlspecialchars($row['receipt_path']); ?>" target="_blank" class="btn btn-primary btn-sm">View Receipt</a>
                                    <?php } ?>
                                </td>
                                <td>
                                    <button class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#reviewModal" onclick="setModalValues('<?= $row['id']; ?>', '<?= $row['tour_id']; ?>', '<?= number_format($row['total_cost'], 2); ?>')">Review</button>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Review Modal -->
    <div class="modal fade" id="reviewModal" tabindex="-1" aria-labelledby="reviewModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-dark text-white">
                    <h5 class="modal-title" id="reviewModalLabel">Expense Review</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p><strong>Tour ID:</strong> <span id="modalTourId"></span></p>
                    <p><strong>Total Cost:</strong> <span id="modalTotalCost"></span></p>
                    <form method="POST" onsubmit="return validateForm();">
                        <input type="hidden" name="expense_id" id="modalExpenseId">
                        <div class="mb-3">
                            <label class="form-label">Action</label>
                            <select id="status" name="status" class="form-select">
                                <option value="Approved">Approve</option>
                                <option value="Rejected">Reject</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Reason (If rejecting)</label>
                            <textarea id="reason_field" name="admin_comment" class="form-control" placeholder="Enter reason"></textarea>
                        </div>
                        <div class="modal-footer">
                            <button type="submit" class="btn btn-primary">Submit</button>
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
