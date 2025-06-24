<?php
session_start();
include 'config.php';  // Database connection

// Check if the form was submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];  // Email entered by the user
    $phone = $_POST['phone'];  // Phone number entered by the user

    // Query to check if the user exists with both email and phone
    $stmt = $conn->prepare("SELECT * FROM students WHERE email = ? AND phone = ?");
    $stmt->bind_param("ss", $email, $phone);  // Bind email and phone
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();

        // Start the session and store user data
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        header("Location: profile.php");  // Redirect to profile page
        exit;
    } else {
        $error = "No user found with this email and phone combination.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .login-form {
            background-color: #ffffff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin-top: 50px;
            max-width: 400px;
            margin: auto;
        }
        .login-form h2 {
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
        .error {
            color: red;
            text-align: center;
        }
    </style>
</head>
<body>

    <div class="container">
        <div class="login-form">
            <h2>Login</h2>

            <!-- Display error if there is one -->
            <?php if (isset($error)) { ?>
                <div class="error"><?= $error; ?></div>
            <?php } ?>

            <!-- Login Form -->
            <form method="POST">
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control" id="email" name="email" placeholder="Enter your email" required>
                </div>
                <div class="mb-3">
                    <label for="phone" class="form-label">Phone</label>
                    <input type="text" class="form-control" id="phone" name="phone" placeholder="Enter your phone number" required>
                </div>
                <button type="submit" class="btn btn-primary">Login</button>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
