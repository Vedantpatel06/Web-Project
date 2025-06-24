<?php 
include 'config.php'; 
include 'header.php';

$sc = $conn->query("SELECT COUNT(*) c FROM students")->fetch_assoc()['c'];
$tc = $conn->query("SELECT COUNT(*) c FROM teachers")->fetch_assoc()['c'];
$cc = $conn->query("SELECT COUNT(*) c FROM classes")->fetch_assoc()['c'];
$ac = $conn->query("SELECT COUNT(*) c FROM attendance")->fetch_assoc()['c'];
$st = $conn->query("
  SELECT s.*, c.class_name, t.name AS teacher_name
  FROM students s
  LEFT JOIN classes c ON s.class_id=c.id
  LEFT JOIN teachers t ON c.teacher_id=t.id
  ORDER BY s.id DESC LIMIT 10
")->fetch_all(MYSQLI_ASSOC);
?>

<style>
  /* Card styling - simple soft color and subtle shadow */
  .card-premium {
    background-color: #f8f9fa; /* light gray */
    color: #343a40; /* dark text */
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    transition: transform 0.25s ease, box-shadow 0.25s ease;
  }
  .card-premium:hover {
    transform: translateY(-6px);
    box-shadow: 0 6px 20px rgba(0,0,0,0.15);
  }
  .card-icon {
    font-size: 2.8rem;
    color: #6c757d; /* medium gray */
    margin-bottom: 0.6rem;
  }
  a.text-decoration-none:hover {
    text-decoration: none;
  }

  /* Table styling */
  .table-premium table {
    border-radius: 10px;
    overflow: hidden;
    box-shadow: 0 4px 12px rgba(0,0,0,0.05);
  }
  .table-premium thead {
    background-color: #e9ecef; /* very light gray */
    color: #495057;
    font-weight: 600;
  }
  .table-premium tbody tr:hover {
    background-color: #f1f3f5;
    transition: background-color 0.3s ease;
  }
  .table-premium tbody td, .table-premium thead th {
    vertical-align: middle;
  }

  /* Heading style */
  h4.mb-4 {
    color: #495057;
    font-weight: 600;
    letter-spacing: 0.05em;
    margin-bottom: 1.5rem !important;
  }
</style>

<div class="row gy-4 mb-5 justify-content-center text-center">
  <?php 
  $iconMap = [
    'students' => 'person-circle',  // changed icon here
    'teachers' => 'person-badge',
    'classes' => 'book-half',
    'attendance' => 'clipboard-check'
  ];
  foreach(['Students'=>$sc,'Teachers'=>$tc,'Classes'=>$cc,'Attendance'=>$ac] as $name=>$count): 
    $iconName = $iconMap[strtolower($name)] ?? 'people-fill';
  ?>
  <div class="col-12 col-sm-6 col-md-3">
    <a href="<?= strtolower($name) ?>.php" class="text-center text-decoration-none">
      <div class="card-premium p-4">
        <i class="bi bi-<?= $iconName ?> card-icon"></i>
        <h5 class="mt-2"><?= $name ?></h5>
        <p class="display-5 fw-semibold mb-0"><?= $count ?></p>
      </div>
    </a>
  </div>
  <?php endforeach; ?>
</div>

<h4 class="mb-4">Recent Students</h4>
<div class="table-premium mb-5">
  <table class="table mb-0">
    <thead>
      <tr><th>ID</th><th>Roll</th><th>Name</th><th>Gender</th><th>Class</th><th>Teacher</th><th>Parent</th></tr>
    </thead>
    <tbody>
      <?php if ($st): foreach($st as $r): ?>
      <tr>
        <td><?= $r['id'] ?></td>
        <td><?= htmlspecialchars($r['roll_number']) ?></td>
        <td><?= htmlspecialchars($r['name']) ?></td>
        <td><?= htmlspecialchars($r['gender']) ?></td>
        <td><?= htmlspecialchars($r['class_name'] ?? '-') ?></td>
        <td><?= htmlspecialchars($r['teacher_name'] ?? '-') ?></td>
        <td><?= htmlspecialchars($r['parents_name']) ?></td>
      </tr>
      <?php endforeach; else: ?>
      <tr><td colspan="7" class="text-center py-4 text-muted fst-italic">No student records found</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<?php include 'footer.php'; ?>
