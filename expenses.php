<?php
session_start();
include 'db.php'; // Include database connection file

// Generate Unique Tour ID
function generateTourId() {
    return 'TOUR-' . strtoupper(substr(md5(uniqid()), 0, 8));
}

// Handle Expense Submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_expense'])) {
    $tour_id = generateTourId(); 
    $accommodation_cost = $_POST['accommodation_cost'];
    $transportation_cost = $_POST['transportation_cost'];
    $other_expenses = $_POST['other_expenses'];

    // Ensure all fields are filled
    if (empty($accommodation_cost) || empty($transportation_cost) || empty($other_expenses) || empty($_FILES['receipt']['name'])) {
        echo "<script>alert('All fields are required!'); window.history.back();</script>";
        exit;
    }

    $total_cost = $accommodation_cost + $transportation_cost + $other_expenses;
    
    // File upload validation
    $upload_dir = 'uploads/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    $receipt_path = '';
    if (!empty($_FILES['receipt']['name'])) {
        $file_name = $_FILES['receipt']['name'];
        $file_tmp = $_FILES['receipt']['tmp_name'];
        $file_size = $_FILES['receipt']['size'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        $allowed_exts = ['jpg', 'jpeg', 'png'];

        if (!in_array($file_ext, $allowed_exts)) {
            echo "<script>alert('Only JPG, JPEG, and PNG files are allowed!'); window.history.back();</script>";
            exit;
        }

        if ($file_size > 1048576) { // 1MB = 1048576 bytes
            echo "<script>alert('File size must be 1MB or less!'); window.history.back();</script>";
            exit;
        }

        $receipt_path = $upload_dir . uniqid() . '.' . $file_ext;
        move_uploaded_file($file_tmp, $receipt_path);
    }

    $sql = "INSERT INTO expenses (tour_id, accommodation_cost, transportation_cost, other_expenses, total_cost, receipt_path) 
            VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('sdddds', $tour_id, $accommodation_cost, $transportation_cost, $other_expenses, $total_cost, $receipt_path);

    if ($stmt->execute()) {
        echo "<script>alert('Expense submitted successfully!'); window.location.href = 'index.php';</script>";
    } else {
        echo "<script>alert('Error submitting expense!');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Expense Submission</title>
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
                <h3>Compensation Master</h3>
            </div>
            <div class="card-body">
                <form method="POST" enctype="multipart/form-data" id="expenseForm">
                    <div class="mb-3">
                        <label class="form-label">Tour ID</label>
                        <input type="text" name="tour_id" class="form-control" value="<?= generateTourId(); ?>" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Transportation costs</label>
                        <input type="number" name="transportation_cost" class="form-control" placeholder="Enter Transportation Cost" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Accommodation expenses </label>
                        <input type="number" name="accommodation_cost" class="form-control" placeholder="Enter Accommodation Cost" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Other expenses</label>
                        <input type="number" name="other_expenses" class="form-control" placeholder="Enter Other Expenses" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Upload the receipt</label>
                        <input type="file" name="receipt" id="receipt" class="form-control" accept=".jpg,.jpeg,.png" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Total cost </label>
                        <input type="text" id="total_cost" class="form-control" readonly>
                    </div>
                    <div class="d-flex justify-content-between">
                        <button type="submit" name="submit_expense" class="btn btn-success">Submit</button>
                        <a href="index.php" class="btn btn-primary">Back</a>
                    </div>
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

        document.getElementById('expenseForm').addEventListener('submit', function (e) {
            let receipt = document.getElementById('receipt').files[0];
            if (!receipt) {
                alert('Receipt is required!');
                e.preventDefault();
                return;
            }

            let allowedExtensions = ['jpg', 'jpeg', 'png'];
            let fileExtension = receipt.name.split('.').pop().toLowerCase();
            if (!allowedExtensions.includes(fileExtension)) {
                alert('Only JPG, JPEG, and PNG files are allowed!');
                e.preventDefault();
                return;
            }

            if (receipt.size > 1048576) {
                alert('File size must be 1MB or less!');
                e.preventDefault();
                return;
            }
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
