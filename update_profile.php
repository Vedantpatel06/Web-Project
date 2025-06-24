<?php
session_start();
include 'config.php';

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $address = $_POST['address'];

    // Handling file upload for profile picture
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] == 0) {
        $profile_picture = 'uploads/' . basename($_FILES['profile_picture']['name']);
        move_uploaded_file($_FILES['profile_picture']['tmp_name'], $profile_picture);
    } else {
        $profile_picture = null;  // Keep existing picture
    }

    // Update the user data in the database
    $stmt = $conn->prepare("UPDATE students SET email = ?, phone = ?, address = ?, profile_picture = ? WHERE id = ?");
    $stmt->bind_param("ssssi", $email, $phone, $address, $profile_picture, $user_id);
    $stmt->execute();
    $stmt->close();

    // Redirect back to profile
    header("Location: profile.php");
    exit;
}

// Fetch user data to pre-fill the form
$stmt = $conn->prepare("SELECT * FROM students WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
?>

<!-- Profile Update Form -->
<h2>Update Profile</h2>
<form method="POST" enctype="multipart/form-data">
    <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
    <input type="text" name="phone" value="<?= htmlspecialchars($user['phone']) ?>" required>
    <textarea name="address"><?= htmlspecialchars($user['address']) ?></textarea>
    <input type="file" name="profile_picture">
    <button type="submit">Update</button>
</form>
