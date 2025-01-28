<?php
// Database connection (Inline for simplicity)
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "tour_management";

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $tour_id = $_POST['tour_id'];
    $activity_name = $_POST['activity_name'];
    $activity_date = $_POST['activity_date'];
    $activity_time = $_POST['activity_time'];
    $description = $_POST['description'];

    $sql = "INSERT INTO activities (tour_id, activity_name, activity_date, activity_time, description)
            VALUES (?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("issss", $tour_id, $activity_name, $activity_date, $activity_time, $description);

    if ($stmt->execute()) {
        echo "<script>
                alert('Activity added successfully!');
                window.location.href = ''; // Reload the current page or redirect to another page
              </script>";
    } else {
        echo "<script>
                alert('Error: " . addslashes($stmt->error) . "');
              </script>";
    }

    $stmt->close();
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Activity</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background-color: #f9f9f9;
        }
        .form-container {
            max-width: 600px;
            width: 100%;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 10px;
            background-color: #fff;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        h1 {
            text-align: center;
            color: #333;
        }
        form {
            display: flex;
            flex-direction: column;
        }
        label {
            margin-top: 10px;
            font-weight: bold;
        }
        input, textarea, button {
            padding: 10px;
            margin-top: 5px;
            border-radius: 5px;
            border: 1px solid #ccc;
        }
        button {
            margin-top: 20px;
            background-color: #28a745;
            color: white;
            border: none;
            cursor: pointer;
        }
        button:hover {
            background-color: #218838;
        }
    </style>
</head>
<body>
    <div class="form-container">
        <h1>Add Activity</h1>
        <form action="" method="POST">
            <label for="tour_id">Tour ID:</label>
            <input type="number" id="tour_id" name="tour_id" required>

            <label for="activity_name">Activity Name:</label>
            <input type="text" id="activity_name" name="activity_name" required>

            <label for="activity_date">Activity Date:</label>
            <input type="date" id="activity_date" name="activity_date" required>

            <label for="activity_time">Activity Time:</label>
            <input type="time" id="activity_time" name="activity_time" required>

            <label for="description">Description:</label>
            <textarea id="description" name="description" rows="5"></textarea>

            <button type="submit">Save Activity</button>
        </form>
    </div>
</body>
</html>