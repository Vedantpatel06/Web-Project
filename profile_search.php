<?php include 'config.php'; ?>
<?php include 'header.php'; ?>

<h4 class="mb-3">Search Profile</h4>

<form method="POST" class="row gy-3">
    <div class="col-md-4">
        <label>Email:</label>
        <input type="email" name="email" class="form-control" placeholder="Enter Email" required>
    </div>
    <div class="col-md-4">
        <label>Mobile Number:</label>
        <input type="text" name="mobile" class="form-control" placeholder="Enter Mobile Number" required>
    </div>
    <div class="col-md-2">
        <button type="submit" class="btn btn-brand">Search</button>
    </div>
</form>

<?php
// Variables to store form input
$email = $_POST['email'] ?? '';
$mobile = $_POST['mobile'] ?? '';

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Prepare SQL query to search for matching profile
    $stmt = $conn->prepare("SELECT id, name, email, mobile, profile_picture FROM users WHERE email = ? AND mobile = ?");
    $stmt->bind_param("ss", $email, $mobile);
    $stmt->execute();
    $result = $stmt->get_result();
    
    // Check if a matching profile is found
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        ?>
        
        <h4 class="mt-4">Profile Found</h4>
        <div class="card-premium">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <!-- Profile picture (use default if not found) -->
                        <img src="<?= $user['profile_picture'] ?: 'default-profile.jpg' ?>" alt="Profile Picture" class="img-fluid rounded-circle">
                    </div>
                    <div class="col-md-8">
                        <h5><?= htmlspecialchars($user['name']) ?></h5>
                        <p>Email: <?= htmlspecialchars($user['email']) ?></p>
                        <p>Mobile: <?= htmlspecialchars($user['mobile']) ?></p>
                    </div>
                </div>
            </div>
        </div>
        
        <?php
    } else {
        echo '<div class="alert alert-danger">No matching profile found. Please check your email and mobile number.</div>';
    }
    
    $stmt->close();
}
?>

<?php include 'footer.php'; ?>
