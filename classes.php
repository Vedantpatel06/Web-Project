<?php
include 'config.php';
include 'header.php';

$err = [];
$suc = '';
$editClass = null;

if (isset($_GET['edit'])) {
    $editId = (int)$_GET['edit'];
    $res = $conn->query("SELECT * FROM classes WHERE id = $editId");
    if ($res && $res->num_rows > 0) {
        $editClass = $res->fetch_assoc();
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add'])) {
        $cn = trim($_POST['class_name']);
        $tid = $_POST['teacher'] ?: NULL;
        $desc = trim($_POST['description']);
        if (!$cn) {
            $err[] = "Class name required.";
        } else {
            $stmt = $conn->prepare("INSERT INTO classes (class_name, teacher_id, description) VALUES (?, ?, ?)");
            if (!$stmt) die("Prepare failed: " . $conn->error);
            $stmt->bind_param("sis", $cn, $tid, $desc);
            if ($stmt->execute()) {
                $suc = "Class added.";
            } else {
                $err[] = "DB error: " . $stmt->error;
            }
            $stmt->close();
        }
    } elseif (isset($_POST['update'])) {
        $id = (int)$_POST['id'];
        $cn = trim($_POST['class_name']);
        $tid = $_POST['teacher'] ?: NULL;
        $desc = trim($_POST['description']);
        if (!$cn) {
            $err[] = "Class name required.";
        } else {
            $stmt = $conn->prepare("UPDATE classes SET class_name=?, teacher_id=?, description=? WHERE id=?");
            if (!$stmt) die("Prepare failed: " . $conn->error);
            $stmt->bind_param("sisi", $cn, $tid, $desc, $id);
            if ($stmt->execute()) {
                $suc = "Class updated.";
                header("Location: classes.php");
                exit;
            } else {
                $err[] = "DB error: " . $stmt->error;
            }
            $stmt->close();
        }
    } elseif (isset($_POST['delete'])) {
        $id = (int)$_POST['id'];
        $conn->query("DELETE FROM classes WHERE id = $id");
        header("Location: classes.php");
        exit;
    }
}

$classes = $conn->query("
    SELECT c.*, t.name AS teacher_name FROM classes c
    LEFT JOIN teachers t ON c.teacher_id = t.id
    ORDER BY c.id DESC
");

$teachers = $conn->query("SELECT id, name FROM teachers ORDER BY name");
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

<h4><?= $editClass ? "Edit Class" : "Add Class" ?></h4>
<form method="post" class="row gy-3">
    <?php if ($editClass): ?>
        <input type="hidden" name="id" value="<?= $editClass['id'] ?>">
    <?php endif; ?>
    <div class="col-md-4">
        <label for="class_name">Class Name*</label>
        <input id="class_name" name="class_name" class="form-control" required value="<?= htmlspecialchars($editClass['class_name'] ?? '') ?>">
    </div>
    <div class="col-md-4">
        <label for="teacher">Class Teacher</label>
        <select id="teacher" name="teacher" class="form-select">
            <option value="">-- Select Teacher --</option>
            <?php
            while ($t = $teachers->fetch_assoc()) {
                $sel = (isset($editClass['teacher_id']) && $editClass['teacher_id'] == $t['id']) ? 'selected' : '';
                echo "<option value='{$t['id']}' $sel>" . htmlspecialchars($t['name']) . "</option>";
            }
            ?>
        </select>
    </div>
    <div class="col-md-8">
        <label for="description">Description</label>
        <input id="description" name="description" class="form-control" value="<?= htmlspecialchars($editClass['description'] ?? '') ?>">
    </div>
    <div class="col-md-12">
        <?php if ($editClass): ?>
            <button type="submit" name="update" class="btn btn-brand">Update Class</button>
            <a href="classes.php" class="btn btn-secondary">Cancel</a>
        <?php else: ?>
            <button type="submit" name="add" class="btn btn-brand">Add Class</button>
        <?php endif; ?>
    </div>
</form>

<h4 class="mt-5 mb-3">Class List</h4>
<div class="table-premium">
    <table class="table mb-0">
        <thead>
            <tr>
                <th>ID</th><th>Class Name</th><th>Teacher</th><th>Description</th><th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($classes && $classes->num_rows > 0): while ($row = $classes->fetch_assoc()): ?>
            <tr>
                <td><?= $row['id'] ?></td>
                <td><?= htmlspecialchars($row['class_name']) ?></td>
                <td><?= htmlspecialchars($row['teacher_name'] ?? '-') ?></td>
                <td><?= htmlspecialchars($row['description']) ?></td>
                <td>
                    <a href="?edit=<?= $row['id'] ?>" class="btn btn-sm btn-warning" title="Edit Class">
                      <i class="fa fa-pen"></i> Edit
                    </a>
                    <form method="post" style="display:inline" onsubmit="return confirm('Delete this class?');">
                        <input type="hidden" name="id" value="<?= $row['id'] ?>">
                        <button type="submit" name="delete" class="btn btn-sm btn-danger" title="Delete Class">
                          <i class="fa fa-trash"></i> Delete
                        </button>
                    </form>
                </td>
            </tr>
            <?php endwhile; else: ?>
            <tr><td colspan="5" class="text-center py-4">No classes found</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include 'footer.php'; ?>
