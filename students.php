<?php
include 'config.php';
include 'header.php';

$err = [];
$suc = '';
$editStudent = null;

if (isset($_GET['edit'])) {
    $editId = (int)$_GET['edit'];
    $res = $conn->query("SELECT * FROM students WHERE id = $editId");
    if ($res && $res->num_rows > 0) {
        $editStudent = $res->fetch_assoc();
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add'])) {
        $r = trim($_POST['roll']);
        $n = trim($_POST['name']);
        $g = $_POST['gender'];
        $e = trim($_POST['email']);
        $p = trim($_POST['phone']);
        $c = $_POST['class'] ?: NULL;
        $addr = trim($_POST['address']);
        $par = trim($_POST['parents']);

        if (!$r || !$n || !$e) {
            $err[] = "Roll, name & email are required.";
        } else {
            $stmt = $conn->prepare("INSERT INTO students (roll_number, name, gender, email, phone, class_id, address, parents_name) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            if (!$stmt) die("Prepare failed: " . $conn->error);
            $stmt->bind_param("sssssiss", $r, $n, $g, $e, $p, $c, $addr, $par);
            if ($stmt->execute()) $suc = "Student added.";
            else $err[] = "DB error: " . $stmt->error;
            $stmt->close();
        }
    } elseif (isset($_POST['update'])) {
        $id = (int)$_POST['id'];
        $r = trim($_POST['roll']);
        $n = trim($_POST['name']);
        $g = $_POST['gender'];
        $e = trim($_POST['email']);
        $p = trim($_POST['phone']);
        $c = $_POST['class'] ?: NULL;
        $addr = trim($_POST['address']);
        $par = trim($_POST['parents']);

        if (!$r || !$n || !$e) {
            $err[] = "Roll, name & email are required.";
        } else {
            $stmt = $conn->prepare("UPDATE students SET roll_number=?, name=?, gender=?, email=?, phone=?, class_id=?, address=?, parents_name=? WHERE id=?");
            if (!$stmt) die("Prepare failed: " . $conn->error);
            $stmt->bind_param("sssssissi", $r, $n, $g, $e, $p, $c, $addr, $par, $id);
            if ($stmt->execute()) {
                $suc = "Student updated.";
                header("Location: students.php");
                exit;
            } else $err[] = "DB error: " . $stmt->error;
            $stmt->close();
        }
    } elseif (isset($_POST['delete'])) {
        $id = (int)$_POST['id'];
        $conn->query("DELETE FROM students WHERE id = $id");
        header("Location: students.php");
        exit;
    }
}

$students = $conn->query("
    SELECT s.*, c.class_name FROM students s
    LEFT JOIN classes c ON s.class_id = c.id
    ORDER BY id DESC
");
?>

<!-- FontAwesome for icons -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />

<style>
  /* Form styles */
  form label {
    font-weight: 600;
    margin-bottom: 0.3rem;
    display: block;
    color: #222;
  }
  form input.form-control,
  form select.form-select {
    border-radius: 8px;
    border: 1.8px solid #ccc;
    padding: 0.45rem 0.75rem;
    font-size: 0.95rem;
    transition: border-color 0.3s ease;
    width: 100%;
    box-sizing: border-box;
  }
  form input.form-control:focus,
  form select.form-select:focus {
    border-color: #5a67d8;
    box-shadow: 0 0 8px rgba(90, 103, 216, 0.3);
    outline: none;
  }

  /* Buttons */
  .btn-brand {
    background: linear-gradient(45deg, #6a82fb, #fc5c7d);
    border: none;
    color: white;
    padding: 0.55rem 1.3rem;
    border-radius: 10px;
    font-weight: 600;
    cursor: pointer;
    transition: background 0.4s ease;
  }
  .btn-brand:hover {
    background: linear-gradient(45deg, #5a6eea, #e04a6e);
  }
  .btn-secondary {
    background-color: #6c757d;
    border: none;
    color: white;
    padding: 0.55rem 1.3rem;
    border-radius: 10px;
    font-weight: 600;
    cursor: pointer;
    margin-left: 0.5rem;
  }
  .btn-secondary:hover {
    background-color: #565e64;
  }

  /* Table */
  .table-premium {
    overflow-x: auto;
  }
  table.table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0 10px;
    font-size: 0.95rem;
  }
  thead tr {
    background-color: #f8f9fa;
    color: #495057;
    font-weight: 700;
    text-transform: uppercase;
  }
  thead th {
    padding: 12px 15px;
    text-align: left;
  }
  tbody tr {
    background-color: white;
    box-shadow: 0 1px 6px rgba(0,0,0,0.08);
    transition: background-color 0.3s ease;
  }
  tbody tr:hover {
    background-color: #f1f3f5;
  }
  tbody td {
    padding: 12px 15px;
    vertical-align: middle;
    color: #343a40;
  }

  /* Action buttons */
  .btn-sm {
    font-size: 0.85rem;
    padding: 5px 10px;
    border-radius: 6px;
    border: none;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 6px;
  }
  .btn-warning {
    background-color: #f0ad4e;
    color: #212529;
  }
  .btn-warning:hover {
    background-color: #d9973f;
  }
  .btn-danger {
    background-color: #dc3545;
    color: white;
  }
  .btn-danger:hover {
    background-color: #bb2d3b;
  }

  /* Alerts */
  .alert {
    padding: 10px 15px;
    margin-bottom: 20px;
    border-radius: 6px;
    font-weight: 600;
  }
  .alert-danger {
    background-color: #f8d7da;
    color: #842029;
  }
  .alert-success {
    background-color: #d1e7dd;
    color: #0f5132;
  }

  /* Margin utilities */
  .mt-5 { margin-top: 3rem; }
  .mb-3 { margin-bottom: 1rem; }
</style>

<?php if ($err): ?>
    <div class="alert alert-danger"><?= implode("<br>", $err) ?></div>
<?php endif; ?>
<?php if ($suc): ?>
    <div class="alert alert-success"><?= $suc ?></div>
<?php endif; ?>

<h4><?= $editStudent ? "Edit Student" : "Add Student" ?></h4>
<form method="post" class="row gy-3">
    <?php if ($editStudent): ?>
        <input type="hidden" name="id" value="<?= $editStudent['id'] ?>">
    <?php endif; ?>

    <div class="col-md-3">
        <label for="roll">Roll Number*</label>
        <input id="roll" name="roll" class="form-control" required value="<?= htmlspecialchars($editStudent['roll_number'] ?? '') ?>">
    </div>

    <div class="col-md-3">
        <label for="name">Name*</label>
        <input id="name" name="name" class="form-control" required value="<?= htmlspecialchars($editStudent['name'] ?? '') ?>">
    </div>

    <div class="col-md-2">
        <label for="gender">Gender</label>
        <select id="gender" name="gender" class="form-select">
            <?php
            $genders = ['Male', 'Female', 'Other'];
            $selGender = $editStudent['gender'] ?? 'Male';
            foreach ($genders as $gender) {
                echo '<option ' . ($selGender == $gender ? 'selected' : '') . '>' . $gender . '</option>';
            }
            ?>
        </select>
    </div>

    <div class="col-md-4">
        <label for="email">Email*</label>
        <input id="email" name="email" type="email" class="form-control" required value="<?= htmlspecialchars($editStudent['email'] ?? '') ?>">
    </div>

    <div class="col-md-3">
        <label for="phone">Phone</label>
        <input id="phone" name="phone" class="form-control" value="<?= htmlspecialchars($editStudent['phone'] ?? '') ?>">
    </div>

    <div class="col-md-3">
        <label for="class">Class</label>
        <select id="class" name="class" class="form-select">
            <option value="">-- Select Class --</option>
            <?php
            $classes = $conn->query("SELECT id, class_name FROM classes ORDER BY class_name");
            while ($cl = $classes->fetch_assoc()) {
                $sel = (isset($editStudent['class_id']) && $editStudent['class_id'] == $cl['id']) ? 'selected' : '';
                echo "<option value='{$cl['id']}' $sel>{$cl['class_name']}</option>";
            }
            ?>
        </select>
    </div>

    <div class="col-md-6">
        <label for="address">Address</label>
        <input id="address" name="address" class="form-control" value="<?= htmlspecialchars($editStudent['address'] ?? '') ?>">
    </div>

    <div class="col-md-4">
        <label for="parents">Parents Name</label>
        <input id="parents" name="parents" class="form-control" value="<?= htmlspecialchars($editStudent['parents_name'] ?? '') ?>">
    </div>

    <div class="col-md-12">
        <?php if ($editStudent): ?>
            <button type="submit" name="update" class="btn btn-brand">Update Student</button>
            <a href="students.php" class="btn btn-secondary">Cancel</a>
        <?php else: ?>
            <button type="submit" name="add" class="btn btn-brand">Add Student</button>
        <?php endif; ?>
    </div>
</form>

<h4 class="mt-5 mb-3">Student List</h4>
<div class="table-premium">
    <table class="table mb-0">
        <thead>
            <tr>
                <th>ID</th>
                <th>Roll</th>
                <th>Name</th>
                <th>Gender</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Class</th>
                <th>Parents</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($students && $students->num_rows > 0): while ($row = $students->fetch_assoc()): ?>
            <tr>
                <td><?= $row['id'] ?></td>
                <td><?= htmlspecialchars($row['roll_number']) ?></td>
                <td><?= htmlspecialchars($row['name']) ?></td>
                <td><?= htmlspecialchars($row['gender']) ?></td>
                <td><?= htmlspecialchars($row['email']) ?></td>
                <td><?= htmlspecialchars($row['phone']) ?></td>
                <td><?= htmlspecialchars($row['class_name'] ?? '-') ?></td>
                <td><?= htmlspecialchars($row['parents_name']) ?></td>
                <td>
                    <a href="?edit=<?= $row['id'] ?>" class="btn btn-sm btn-warning" title="Edit Student">
                        <i class="fa-solid fa-pen-to-square"></i> Edit
                    </a>
                    <form method="post" style="display:inline" onsubmit="return confirm('Delete this student?');">
                        <input type="hidden" name="id" value="<?= $row['id'] ?>">
                        <button type="submit" name="delete" class="btn btn-sm btn-danger" title="Delete Student">
                            <i class="fa-solid fa-trash"></i> Delete
                        </button>
                    </form>
                </td>
            </tr>
            <?php endwhile; else: ?>
            <tr><td colspan="9" class="text-center py-4">No students found</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include 'footer.php'; ?>
