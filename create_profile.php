<?php include 'config.php'; ?>
<?php include 'header.php'; ?>

<h4 class="mb-3">Create or Update Profile</h4>

<form method="POST" enctype="multipart/form-data">
    <div class="col-md-6">
        <label>Name:</label>
        <input type="text" name="name" class="form-control" placeholder="Enter your full name" required>
    </div>
    <div class="col-md-6">
        <label>Email:</label>
        <input type="email" name="email" class="form-control" placeholder="Enter your email" required>
    </div>
    <div class="col-md-6">
        <label>Mobile Number:</label>
        <input type="text" name="mobile" class="form-control" placeholder="Enter your mobile number" required>
    </div>
    <div class="col-md-6">
        <label>Profile Picture:</label>
        <input type="file" name="profile_picture" class="form-control">
    </div>
    <div class="col-md-12 mt-3">
        <button type="submit" name="save_profile" class="btn btn-brand">Save Profile</button>
    </div>
</form>

<?php
if (isset($_POST['save_profile'])) {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $mobile = $_POST['mobile'];
    $profile_picture = null;

    // Handle profile picture upload
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] == 0) {
        $profile_picture = 'uploads/' . basename($_FILES['profile_picture']['name']);
        move_uploaded_file($_FILES['profile_picture']['tmp_name'], $profile_picture);
    }

    // Insert or update profile in the database
    $stmt = $conn->prepare("INSERT INTO users (name, email, mobile, profile_picture) 
        VALUES (?, ?, ?, ?) ON DUPLICATE KEY UPDATE name=?, mobile=?, profile_picture=?");
    $stmt->bind_param("sssssss", $name, $email, $mobile, $profile_picture, $name, $mobile, $profile_picture);
    if ($stmt->execute()) {
        echo '<div class="alert alert-success">Profile saved successfully!</div>';
    } else {
        echo '<div class="alert alert-danger">Failed to save profile.</div>';
    }
    $stmt->close();
}
?>

<?php include 'footer.php'; ?>
