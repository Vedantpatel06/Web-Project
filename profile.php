<?php
session_start();
include 'config.php';  // Database connection

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");  // Redirect to login page if not logged in
    exit;
}

$user_id = $_SESSION['user_id'];  // Get the logged-in user's ID

// Fetch user data from the database
$stmt = $conn->prepare("SELECT * FROM students WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .profile-container {
            background-color: #ffffff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin-top: 50px;
            max-width: 800px;
            margin: auto;
        }
        .profile-container h2 {
            text-align: center;
            color: #4e73df;
        }
        .btn-primary {
            background-color: #4e73df;
            border: none;
            width: 100%;
            padding: 10px;
            border-radius: 5px;
            font-weight: bold;
        }
        .btn-primary:hover {
            background-color: #2e59d9;
        }
    </style>
</head>
<body>

    <div class="container">
        <div class="profile-container">
            <h2>Welcome, <?= htmlspecialchars($user['name']) ?></h2>

            <div class="mb-3">
                <p><strong>Email:</strong> <?= htmlspecialchars($user['email']) ?></p>
                <p><strong>Phone:</strong> <?= htmlspecialchars($user['phone']) ?></p>
                <p><strong>Address:</strong> <?= htmlspecialchars($user['address']) ?></p>
                <p><strong>Parents:</strong> <?= htmlspecialchars($user['parents_name']) ?></p>
            </div>

            <!-- Logout Button -->
            <a href="logout.php" class="btn btn-primary">Logout</a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
